<?php
namespace App\Services;

use App\Enums\ProjectFormStatus;
use App\Exceptions\ProjectManagementException;
use App\Exceptions\PermissionDeniedException;
use App\Models\ProjectForm;
use App\Repositories\FormSubmissionPeriodRepository;
use App\Repositories\GroupMemberRepository;
use App\Repositories\ProjectFormRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Response;

class ProjectFormService
{
    public function __construct(
        protected ProjectFormRepository $repository,
        protected GroupMemberRepository $groupRepo,
        protected FcmNotificationDispatcherService $dispatcherService,
        protected FormSubmissionPeriodRepository $periodRepo,
    ) {}

    public function store(array $data): void
    {
        $user = Auth::user();

        $groupMember = $user->groupMember;
        if (!$groupMember) {
            throw new PermissionDeniedException('! خطأ', 'المستخدم غير موجود بأي مجموعة.');
        }

        $groupId = $groupMember->group_id;

        if (!$this->groupRepo->isLeader($groupId, $user->id)) {
            throw new PermissionDeniedException('! صلاحيات', 'فقط قائد المجموعة يستطيع تعبئة الاستمارة.');
        }

        // منع التكرار
        if ($this->repository->existsForGroupByLeader($groupId, $user->id)) {
            throw new PermissionDeniedException('! عملية مكررة', 'لا يمكنك تعبئة الاستمارة أكثر من مرة لنفس المجموعة.');
        }

        if(count($groupMember) < 2)
        {
            throw new PermissionDeniedException('! عملية خاطئة', 'لا يمكنك التقدم لهذه الاستمارة لان عدد اعضاء الغروب لا يحقق شرط التقدم' , 422);
        }

        $this->ensureFormPeriodIsActive("form1");

        $form = $this->repository->create([
            'group_id'          => $groupId,
            'user_id'           => $data['user_id'],
            'arabic_title'      => $data['arabic_title'],
            'english_title'     => $data['english_title'],
            'description'       => $data['description'],
            'project_scope'     => $data['project_scope'],
            'targeted_sector'   => $data['targeted_sector'],
            'sector_classification' => $data['sector_classification'],
            'stakeholders'      => $data['stakeholders'],
            'status'            => ProjectFormStatus::Draft,
        ]);

        $this->regeneratePdf($form);
    }

    public function sign(ProjectForm $form): void
    {
        $user = Auth::user();

        if ($form->status !== ProjectFormStatus::Draft) {
            throw new PermissionDeniedException(
                '! غير ممكن التوقيع',
                'لا يمكن التوقيع إلا عندما تكون الاستمارة في وضع المسودة.'
            );
        }

        $isMember = $this->groupRepo->isMember($form->group_id, $user->id);

        if (!$isMember) {
            throw new PermissionDeniedException(
                '! صلاحيات غير كافية',
                'فقط أعضاء المجموعة يمكنهم التوقيع.'
            );
        }

        if ($this->repository->hasUserSigned($form->id, $user->id)) {
            throw new PermissionDeniedException(
                '! مكرر',
                'لقد قمت بتوقيع هذه الاستمارة مسبقاً.'
            );
        }

        $this->repository->signForm($form->id, $user->id);

        $this->regeneratePdf($form);

        // إشعار باقي أعضاء المجموعة
        $members = $form->group->members->pluck('user');
        $title = 'توقيع استمارة';
        $body = "{$user->name} قام بتوقيع استمارة المشروع.";
        foreach ($members as $member) {
            $this->dispatcherService->sendToUser($member, $title, $body);
        }
    }


    public function update(ProjectForm $form, array $data): void
    {
        $user = Auth::user();

        $groupMember = $user->groupMember;
        if (!$groupMember) {
            throw new PermissionDeniedException('! خطأ', 'المستخدم غير موجود بأي مجموعة.');
        }

        $groupId = $groupMember->group_id;

        if (!$this->groupRepo->isLeader($groupId, $user->id)) {
            throw new PermissionDeniedException('! صلاحيات', 'فقط قائد المجموعة يستطيع تعبئة الاستمارة.');
        }

        if (!in_array($form->status, [ProjectFormStatus::Draft, ProjectFormStatus::Rejected])) {
            throw new PermissionDeniedException('! لا يمكن التعديل', 'لا يمكنك تعديل الاستمارة في حالتها الحالية.');
        }

        if ($form->status === ProjectFormStatus::Rejected) {
            $hasChanged = $this->repository->hasFormChanged($form, $data);

            if (!$hasChanged) {
                throw new PermissionDeniedException('! طلب مكرر', 'قم بتعديل معلومات الاستمارة أو اختر مشرفًا مختلفًا.');
            }
        }

        $this->ensureFormPeriodIsActive("form1");

        $this->repository->update($form, $data);
        $this->regeneratePdf($form);
    }

    public function submitToSupervisor(ProjectForm $form): void
    {

        if (!in_array($form->status, [ProjectFormStatus::Draft, ProjectFormStatus::Rejected])) {
            throw new PermissionDeniedException('! لا يمكن الإرسال', 'لا يمكن إرسال الاستمارة في هذه الحالة.');
        }

        if ($form->status === ProjectFormStatus::Rejected && $form->updated_at <= $form->submission_date) {
            throw new PermissionDeniedException('! لم يتم التعديل', 'قم بتعديل النموذج قبل إعادة الإرسال.');
        }

        $this->ensureFormPeriodIsActive("form1");

        $groupMemberIds = $this->groupRepo->getGroupMemberIds($form->group_id);
        $signedUserIds = $form->signatures->pluck('user_id')->toArray();
        $unsigned = array_diff($groupMemberIds, $signedUserIds);

        if (!empty($unsigned)) {
            throw new PermissionDeniedException(
                '! لم يتم التوقيع',
                'جميع أعضاء المجموعة يجب أن يوقعوا قبل الإرسال للمشرف.'
            );
        }

        $this->repository->markAsSubmitted($form);

        // إشعار المشرف
        if ($form->user) {
            $title = '! استمارة مشروع بانتظار المراجعة';
            $body = "استمارة المشروع الخاصة بالمجموعة {$form->group->name} جاهزة للمراجعة.";
            $this->dispatcherService->sendToUser($form->user, $title, $body);
        }

    }

    public function getPreviewPdfBase64(ProjectForm $form): array
    {
        $user = auth()->user();

        $isSupervisor = $form->user_id === $user->id;
        $isMember = $this->groupRepo->isMember($form->group_id, $user->id);

        if (!$isSupervisor && !$isMember) {
            throw new PermissionDeniedException('! غير مصرح', 'لا يمكنك رؤية هذا الملف غير مخول لك بهذا.');
        }

        if (!$form->filled_form_file_path || !Storage::disk('public')->exists($form->filled_form_file_path)) {
            throw new PermissionDeniedException('! غير موجود', 'الملف غير متوفر أو لم يتم إنشاؤه بعد.');
        }

        $filePath = storage_path('app/public/' . $form->filled_form_file_path);
        $pdfContent = file_get_contents($filePath);
        $base64Pdf = base64_encode($pdfContent);

        return [
            'file_name' => 'project_form_' . $form->id . '.pdf',
            'file_type' => 'application/pdf',
            'file_base64' => $base64Pdf,
        ];
    }

    private function regeneratePdf(ProjectForm $form): void
    {
        if ($form->filled_form_file_path) {
            Storage::disk('public')->delete($form->filled_form_file_path);
        }

        // 1) جهّز الـ HTML
        $html = view('pdfs.project_form', compact('form'))->render();

        // 2) أنشئ كائن mPDF مضبوطاً للعربية
        $mpdf = new Mpdf([
            'mode'         => 'utf-8',
            'format'       => 'A4',
            'default_font' => 'cairo',
            'direction'    => 'rtl',
        ]);

        $mpdf->AddFontDirectory(storage_path('fonts'));
        $mpdf->fontdata['cairo'] = ['R' => 'Cairo-Regular.ttf'];
        $mpdf->SetFont('cairo');

        // 3) اكتب الـ HTML وحضّر المحتوى
        $mpdf->WriteHTML($html);
        $pdf = $mpdf->Output('', 'S'); // S = string

        // 4) خزّن الملف في storage/public/forms
        $filePath = sprintf('forms/project_form_%d_%d.pdf', $form->id, time());
        Storage::disk('public')->put($filePath, $pdf);

        // 5) حدِّث العمود في قاعدة البيانات
        $form->updateQuietly(['filled_form_file_path' => $filePath]);
    }

    private function ensureFormPeriodIsActive(string $formName): void
    {
        if (!$this->periodRepo->isFormPeriodActive($formName)) {
            throw new PermissionDeniedException(
                '! انتهى الوقت',
                'لا يمكنك تعديل أو إرسال هذا النموذج بعد انتهاء الفترة المحددة.'
            );
        }
    }

    public function signForm(int $formId): void
    {
        $form = $this->repository->findById($formId);

        if(!$form)
        {
            throw new ProjectManagementException('! لايمكنك اجراء هذه العملية','الاستمارة التي تحاول الوصول اليها غير موجودة', 404);
        }

        if($form->status === ProjectFormStatus::Approved)
        {
            throw new ProjectManagementException('! لايمكنك اجراء هذه العملية' , 'الاستمارة موقعة اساسا ولايمكنك اعادة توقيعها مرة اخرى' , 422);
        }

        if($form->status === ProjectFormStatus::Rejected)
        {
            throw new ProjectManagementException('! لايمكنك اجراء هذه العملية' , 'لايمكنك قبول استمارة بعد ان قمت برفضها' , 422);
        }

        if($form->user_id !== Auth::id())
        {
            throw new ProjectManagementException('! لايمكنك اجراء هذه العملية' , 'غير مصرح لك بتوقيع هذه الاستمارة لانك لست المشرف عليها' , 403);
        }

        $this->repository->approve($form);

        //send notification
        $leader = $this->repository->getLeaderGroupFromForm($form->group_id);
        $doctor = $form->users->name;
        if(!$leader)
        {
            throw new ProjectManagementException('! تم توقيع الاستمارة ولكن لن يتم ارسال اشعار','لن يتم ارسال اشعار الى مشرف الغروب لان هذا الغروب ليس لديه مشرف', 404);
        }
        $this->dispatcherService->sendToUser($leader->user , '! موافقة على فكرة الاستمارة' , " بالموفقة على الاستمارة الواحد الخاصة بكم$doctor قام الدكتور ");
    }

    public function rejectForm(int $formId ): void
    {
        $form = $this->repository->findById($formId);

        if(!$form)
        {
            throw new ProjectManagementException('! لايمكنك اجراء هذه العملية','الاستمارة التي تحاول الوصول اليها غير موجودة', 404);
        }

        if($form->status === ProjectFormStatus::Approved)
        {
            throw new ProjectManagementException('! لايمكنك اجراء هذه العملية' , 'لا يمكن رفض استمارة تم توقيعها بالفعل' , 422);
        }

        if($form->status === ProjectFormStatus::Rejected)
        {
            throw new ProjectManagementException('! لايمكنك اجراء هذه العملية' , 'الاستمارة مرفوضة بالفعل ولايمكنك رفضها مرة اخرى' , 422);
        }

        if($form->user_id !== Auth::id())
        {
            throw new ProjectManagementException('! لايمكنك اجراء هذه العملية' , 'غير مصرح لك برفض هذه الاستمارة لانك لست المشرف عليها' , 403);
        }

        $this->repository->reject($form);

        //send notification
        $leader = $this->repository->getLeaderGroupFromForm($form->group_id);
        $doctor = $form->users->name;
        if(!$leader)
        {
            throw new ProjectManagementException('! تم رفض الاستمارة ولكن لن يتم ارسال اشعار','لن يتم ارسال اشعار الى مشرف الغروب لان هذا الغروب ليس لديه مشرف', 404);
        }
        $this->dispatcherService->sendToUser($leader->user , '! رفض فكرة الاستمارة' , " برفض الاستمارة الواحد الخاصة بكم الرجاء المعاودة بتقديم فكرة اخرى$doctor قام الدكتور ");
    }

    public function downloadFormForDoctor(int $formId)
    {
        $form = $this->repository->findById($formId);

        if(!$form)
        {
            throw new ProjectManagementException('! لايمكنك اجراء هذه العملية','الاستمارة التي تحاول الوصول اليها غير موجودة', 404);
        }

        $filePath = $this->repository->getFilePath($form);

        if(!$filePath)
        {
            throw new ProjectManagementException('! لايمكنك اجراء هذه العملية','ملف الاستمارة التي تحاول تنزيلها غير موجود اساسا', 404);
        }

        return response()->download($filePath , basename($filePath) , ['Content-Type' => 'application/pdf']);
    }
}
