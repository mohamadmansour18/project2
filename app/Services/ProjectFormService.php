<?php
namespace App\Services;

use App\Enums\ProjectFormStatus;
use App\Exceptions\PermissionDeniedException;
use App\Models\ProjectForm;
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
    ) {}

    public function store(array $data): void
    {
        $user = Auth::user();

        if ($this->repository->existsForGroupByLeader($data['group_id'], $user->id)) {
            throw new PermissionDeniedException('عملية مكررة', 'لا يمكنك تعبئة الاستمارة أكثر من مرة لنفس المجموعة.');
        }

        $form = $this->repository->create([
            'group_id' => $data['group_id'],
            'user_id' => $data['user_id'],
            'arabic_title' => $data['arabic_title'],
            'english_title' => $data['english_title'],
            'description' => $data['description'],
            'project_scope' => $data['project_scope'],
            'targeted_sector' => $data['targeted_sector'],
            'sector_classification' => $data['sector_classification'],
            'stakeholders' => $data['stakeholders'],
            'status' => ProjectFormStatus::Draft,
        ]);

        $html = view('pdfs.project_form', ['form' => $form])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'cairo',
            'direction' => 'rtl',
        ]);

        $this->regeneratePdf($form);
    }

    public function sign(ProjectForm $form): void
    {
        $user = Auth::user();

        if ($form->status !== ProjectFormStatus::Draft) {
            throw new PermissionDeniedException(
                'غير ممكن التوقيع',
                'لا يمكن التوقيع إلا عندما تكون الاستمارة في وضع المسودة.'
            );
        }

        $isMember = $this->groupRepo->isMember($form->group_id, $user->id);

        if (!$isMember) {
            throw new PermissionDeniedException(
                'صلاحيات غير كافية',
                'فقط أعضاء المجموعة يمكنهم التوقيع.'
            );
        }

        if ($this->repository->hasUserSigned($form->id, $user->id)) {
            throw new PermissionDeniedException(
                'مكرر',
                'لقد قمت بتوقيع هذه الاستمارة مسبقاً.'
            );
        }

        $this->repository->signForm($form->id, $user->id);
    }


    public function update(ProjectForm $form, array $data): void
    {
        if (!in_array($form->status, [ProjectFormStatus::Draft, ProjectFormStatus::Rejected])) {
            throw new PermissionDeniedException('لا يمكن التعديل', 'لا يمكنك تعديل الاستمارة في حالتها الحالية.');
        }

        if ($form->status === ProjectFormStatus::Rejected) {
            $hasChanged = $this->repository->hasFormChanged($form, $data);

            if (!$hasChanged) {
                throw new PermissionDeniedException('طلب مكرر', 'قم بتعديل معلومات الاستمارة أو اختر مشرفًا مختلفًا.');
            }
        }

        $this->repository->update($form, $data);
        $this->regeneratePdf($form);
    }

    public function submitToSupervisor(ProjectForm $form): void
    {

        if (!in_array($form->status, [ProjectFormStatus::Draft, ProjectFormStatus::Rejected])) {
            throw new PermissionDeniedException('لا يمكن الإرسال', 'لا يمكن إرسال الاستمارة في هذه الحالة.');
        }

        if ($form->status === ProjectFormStatus::Rejected && $form->updated_at <= $form->submission_date) {
            throw new PermissionDeniedException('لم يتم التعديل', 'قم بتعديل النموذج قبل إعادة الإرسال.');
        }

        $groupMemberIds = $this->groupRepo->getGroupMemberIds($form->group_id);
        $signedUserIds = $form->signatures->pluck('user_id')->toArray();
        $unsigned = array_diff($groupMemberIds, $signedUserIds);

        if (!empty($unsigned)) {
            throw new PermissionDeniedException(
                'لم يتم التوقيع',
                'جميع أعضاء المجموعة يجب أن يوقعوا قبل الإرسال للمشرف.'
            );
        }

        $this->repository->markAsSubmitted($form);

    }

    public function downloadFilledForm(ProjectForm $form)
    {
        $user = Auth::user();

        // تحقق من الصلاحيات
        $isOwner = $form->user_id === $user->id;
        $isMember = $this->groupRepo->isMember($form->group_id, $user->id);

        if (!$isOwner && !$isMember && !$user->isSupervisor()) {
            throw new PermissionDeniedException(
                'غير مسموح',
                'لا تملك صلاحية لتحميل هذه الاستمارة.'
            );
        }

        // تحقق من وجود ملف PDF
        if (!$form->filled_form_file_path || !Storage::disk('public')->exists($form->filled_form_file_path)) {
            throw new \Exception('الملف غير متوفر أو لم يتم إنشاؤه بعد.');
        }

        // أرجع الملف كاستجابة قابلة للتحميل
        return Response::download(
            storage_path('app/public/' . $form->filled_form_file_path),
            'project_form_' . $form->id . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
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
}
