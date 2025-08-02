<?php
namespace App\Services;

use App\Enums\ProjectFormStatus;
use App\Exceptions\PermissionDeniedException;
use App\Models\ProjectForm;
use App\Models\User;
use App\Repositories\GroupMemberRepository;
use App\Repositories\ProjectFormRepository;
use Illuminate\Support\Facades\Auth;

class ProjectFormService
{
    public function __construct(
        protected ProjectFormRepository $repository,
        protected GroupMemberRepository $groupRepo,
    ) {}

    public function store(array $data): void
    {
        $user = Auth::user();

        $this->repository->create([
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
    }

    public function submitToSupervisor(ProjectForm $form): void
    {
        $user = Auth::user();

        if (!in_array($form->status, [ProjectFormStatus::Draft, ProjectFormStatus::Rejected])) {
            throw new PermissionDeniedException('لا يمكن الإرسال', 'لا يمكن إرسال الاستمارة في هذه الحالة.');
        }

        // تحقق من التعديل إذا كانت مرفوضة
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
}
