<?php

namespace App\Services;

use App\Enums\ProjectFormStatus;
use App\Enums\UserRole;
use App\Exceptions\PermissionDeniedException;
use App\Helpers\ImageHelper;
use App\Helpers\UrlHelper;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\GradeException;
use App\Models\Group;
use App\Models\User;
use App\Repositories\GroupInvitationRepository;
use App\Repositories\GroupMemberRepository;
use App\Repositories\GroupRepository;
use App\Enums\GroupMemberRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GroupService
{


    public function __construct(
      protected GroupRepository $groupRepo,
      protected GroupMemberRepository $groupMemberRepo,
      protected GroupInvitationRepository $groupInvitationRepo) {
    }

    public function createGroup(CreateGroupRequest $request, User $creator): Group
    {
        //image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = ImageHelper::storeImageWithCustomName($request->file('image'), 'group_images', $request->name);
        }

        //QR
        $qrImagePathForDb = ImageHelper::generateAndStoreQrCode($request->name);

        //Create group
        $group = $this->groupRepo->create([
            'name' => $request->name,
            'description' => $request->description,
            'speciality_needed' => $request->speciality_needed,
            'framework_needed' => $request->framework_needed,
            'type' => $request->type,
            'qr_code' => $qrImagePathForDb,
            'number_of_members' => 1,
            'image' => $imagePath,
        ]);

        //add Leader
        $this->groupMemberRepo->create($group->id, $creator->id, GroupMemberRole::Leader);


        // send invitations
        if ($request->has('invitations')) {
            foreach ($request->invitations as $inviteeId) {
                $this->groupInvitationRepo->create($group->id, $inviteeId, $creator->id);
            }
        }


        return $group;
    }

    public function updateGroup(UpdateGroupRequest $request, Group $group): Group
    {
        //data
        $data = [];
        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['name', 'description', 'speciality_needed', 'framework_needed', 'type'])) {
                $data[$key] = $value;
            }
        }

        //image
        if ($request->hasFile('image')) {
            $data['image'] = ImageHelper::storeImageWithCustomName($request->file('image'), 'group_images', $group->name);
        }

        //update
        $this->groupRepo->update($group, $data);

        return $group;
    }

    public function getGroupData(Group $group): array
    {
        $data = $this->groupRepo->getGroupDetails($group);

        $data['image'] = UrlHelper::imageUrl($data['image']);

        return $data;
    }

    public function changeLeadership(Group $group, int $currentLeaderId, int $newLeaderId): void
    {
        if (!$this->groupMemberRepo->isMember($group->id, $newLeaderId)) {
            throw new PermissionDeniedException(
                'عضو غير موجود',
                'المستخدم المحدد ليس عضوًا في المجموعة',
                400
            );
        }

        if ($currentLeaderId === $newLeaderId) {
            throw new PermissionDeniedException(
                'خطأ في النقل',
                'لا يمكنك نقل القيادة لنفسك',
                400
            );
        }

        $this->groupMemberRepo->updateRole($group->id, $newLeaderId, GroupMemberRole::Leader);
        $this->groupMemberRepo->updateRole($group->id, $currentLeaderId, GroupMemberRole::Member);
    }

    public function getIncompletePublicGroups(): array
    {
        $userId = auth()->id();
        $groups = $this->groupRepo->getIncompletePublicGroupsForCurrentYear();

        return $groups->map(function ($group) use ($userId) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'image' => UrlHelper::imageUrl($group->image),
                'specialities_needed' => $group->speciality_needed,
                'members_count' => $group->number_of_members,
                'has_requested_join' => $group->joinRequests->isNotEmpty(), // true/false
            ];
        })->toArray();
    }


    public function getMyGroup(int $userId): ?array
    {
        $group = $this->groupRepo->getUserGroup($userId);

        if (!$group) {
            throw new PermissionDeniedException(
                'خطأ',
                'أنت لست عضو في أي مجموعة',
                404
            );
        }

        return $this->getGroupData($group);
    }

    public function getGroupDetails2(int $groupId): array
    {
        $group = $this->groupRepo->getGroupWithRelations($groupId);

        $form1 = $group->projectForm()->first();

        $supervisorSignature = $form1?->signatures()
            ->whereHas('user', function ($q) {
                $q->where('role', UserRole::Doctor);
            })
            ->with('user')
            ->first();

        return [
            'supervisor_name' => $supervisorSignature?->user?->name,
            'group_created_at' => $group->created_at->toDateString(),
            'idea_arabic_name' => $form1?->arabic_title,
            'members_count' => $group->members->count(),
            'members' => $group->members->map(function ($member) {
                return [
                    'name' => $member->user->name,
                    'speciality' => $member->user->profile?->student_speciality,
                    'student_status' => $member->user->profile?->student_status,
                    'image' => UrlHelper::imageUrl( $member->user->profile?->profile_image),
                    'is_leader' => $member->role === GroupMemberRole::Leader,
                ];
            }),
            'qr_code' => UrlHelper::imageUrl($group->qr_code),
        ];
        }

    public function getMyGroupDetails(): ?array
    {
        $user = auth()->user();
        $group = $this->groupRepo->getUserGroupWithRelations($user->id);

        if (!$group) {
            throw new PermissionDeniedException(
                'خطأ',
                'أنت لست عضو في أي مجموعة',
                404
            );
        }

        $form1 = $group->projectForm()->first();

        $supervisorSignature = $form1?->signatures()
            ->whereHas('user', function ($q) {
                $q->where('role', UserRole::Doctor);
            })
            ->with('user')
            ->first();

        return [
            'group_id' => $group->id,
            'supervisor_name' => $supervisorSignature?->user?->name,
            'group_created_at' => $group->created_at->toDateString(),
            'idea_arabic_name' => $form1?->arabic_title,
            'members_count' => $group->members->count(),
            'members' => $group->members->map(function ($member) {
                return [
                    'name' => $member->user->name,
                    'speciality' => $member->user->profile?->student_speciality,
                    'student_status' => $member->user->profile?->student_status,
                    'image' => UrlHelper::imageUrl( $member->user->profile?->profile_image),
                    'is_leader' => $member->role === GroupMemberRole::Leader,
                ];
            }),
            'qr_code' => UrlHelper::imageUrl($group->qr_code),
        ];
    }

    public function getGroupDataForDoctor()
    {
        $groups = $this->groupRepo->getAllGroupsWithForms();

        return $groups->map(function ($group) {

            return [
                'id' => $group->id,
                'name' => $group->name,
                'group_image' => UrlHelper::imageUrl($group->image) ,
                'number_of_members' => $this->getCustomGroupNumber($group),
                'form1' => $this->checkForm1($group),
                'form2' => $this->checkForm2($group),
            ];
        });
    }

    public function getGroupFormOneForDoctor()
    {
        $doctorId = Auth::id();

        $groups = $this->groupRepo->getDoctorFormOneGroups($doctorId);

        return $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'group_image' => UrlHelper::imageUrl($group->image) ,
                'number_of_members' => $this->getCustomGroupNumber($group),
                'form1' => $this->checkForm1($group),
                'form2' => $this->checkForm2($group),
            ];
        });
    }

    public function getGroupDetailsForFinalInterview($groupId)
    {
        $data = $this->groupRepo->getGroupDetailsInFinalInterview($groupId);

        $group = $data['group'];
        $schedule = $data['schedule'];
        $form1 = $data['form1'];
        $form2 = $data['form2'];
        $grade = $data['grade'];
        $exceptions = $data['exceptionGrades'];
        $isSupervisor = $data['isSupervisor'];

        return [
            'interview_information' => $schedule ? [
                'time' => $this->formatTime($schedule->interview_time),
                'day' => $this->getDayName($schedule->interview_date),
                'date' => $schedule->interview_date?->format('d/m/Y')
            ] : [],

            'main' => [
                'group_name' => $group->name,
                'project_title' => $form1 ? $form1->arabic_title : null,
                'leader' => optional($group->members()->where('role' , GroupMemberRole::Leader->value)->first()?->user)->name ,
                'members_count' => $this->getCustomGroupNumber($group),
                'members' => $group->members->map(function ($member){
                    return [
                        'id' => $member->user->id,
                        'name' => $member->user->name,
                        'profile_image' => optional($member->user->profile)->profile_image ?? null
                    ];
                })->values(),
            ],

            'project' => [
                'form_supervisor' => $form1 ? optional($form1->users)->name : "لايوجد مشرف",
                'form1_date' => $form1 ? Carbon::parse($form1->submission_date)->format('d/m/Y') : "غير موجودة",
                'form2_date' => $form2 ? $form2->created_at->format('d/m/Y') : "غير موجودة",
            ],

            'grade' => $grade ? [
                'presentation_grade' => $grade->presentation_grade,
                'project_grade' => $grade->project_grade,
                'total_grade' => $grade->total_grade,
                'exceptions' => $exceptions->map(function ($exception){
                    return [
                        'id' => $exception->user->id,
                        'name' => $exception->user->name
                    ];
                })->values(),
            ] : [],

            'is_Supervisor' => $isSupervisor,
        ];
    }

    private function getCustomGroupNumber($group): string
    {
        $numberOfMembers = $group->number_of_members;

        return $numberOfMembers > 5 ? "6/$numberOfMembers" : "5/$numberOfMembers";
    }

    private function checkForm1($group): ?string
    {
        $form1 = $group->projectForm()->whereIn('status' , [ProjectFormStatus::Approved->value , ProjectFormStatus::Pending->value])->first();

        return $form1 ? "# استمارة 1" : null ;
    }

    private function checkForm2($group): ?string
    {
        $form2 = $group->projectForm2()->first();

        return $form2 ?"# استمارة 2" : null ;
    }

    private function formatTime($time): string
    {
        $formatted = $time->format('h:i');
        $suffix = $time->format('A') === 'AM' ? 'ص' : 'م';
        return $formatted . $suffix;
    }

    private function getDayName($date): string
    {
        $days = [
            'Saturday' => 'السبت',
            'Sunday' => 'الاحد',
            'Monday' => 'الاثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الاربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
        ];
        return $days[$date->format('l')] ?? '';
    }

    public function getGroupsWithForms()
    {
        return $this->groupRepo->getAllWithForms();
    }

    public function searchGroupsByName(string $keyword)
    {
        $groups = $this->groupRepo->searchByName($keyword);

        if ($groups->isEmpty()) {
            throw new PermissionDeniedException(
                'خطأ',
                'لا يوجد مجموعة بهذا الاسم',
                404
            );
        }

        return $groups;
    }

    public function getGroupDetailsForFormOneRequest(int $groupId): array
    {
        $group = $this->groupRepo->getGroupWithRelation($groupId);

        //group_information
        $leader = $group->members->where('role' , GroupMemberRole::Leader)->first();
        //dd($group);
        $groupInformation = [
            'group_name'    => $group->name,
            'leader'        => optional($leader?->user)->name,
            'members_count' => $this->getCustomGroupNumber($group),
            'members'       => $group->members->map(function ($member){
                return [
                    'id'    => optional($member->user)->id,
                    'name'  => optional($member->user)->name,
                    'profile_image' => UrlHelper::imageUrl(optional($member->user->profile)->profile_image)
                ];
            })->values(),
        ];

        //form_1
        $form = $group?->projectForms;
        $formData = $form ? [
            'form_id' => $form->id ,
            'arabic_title' => $form->arabic_title,
            'creation_date' => $form->updated_at->format('d/m/Y'),
            'filled_form_file_path' => UrlHelper::imageUrl($form->filled_form_file_path),
        ] : [];

        //interview_information
        $schedule = $group?->interviewSchedule;

        $interviewInformation = [];
        if($schedule)
        {
            $interviewInformation = [
                'date' => $schedule->interview_date->format('d/m/Y') ,
                'day'  => $this->getDayName($schedule->interview_date) ,
            ];
        }

        //group_grade
        $grade = $group->projectGrade?->first();

        $gradeData = [];
        if($grade)
        {
            $exception = GradeException::query()->where('grade_id' , $grade->id)->pluck('student_id')->toArray();

            $membersWithGrade = $group->members->filter(function ($member) use ($exception){
                return !in_array($member->user_id , $exception);
            })
                ->map(function ($member){
                    return [
                        'id' => $member->user_id,
                        'name' => optional($member->user)->name,
                        'profile_image' => UrlHelper::imageUrl(optional($member->user->profile)->profile_image)
                    ];
                })
                ->values();

            $gradeData = [
                'presentation_grade' => $grade->presentation_grade,
                'project_grade'      => $grade->project_grade,
                'total_grade'        => $grade->total_grade,
                'member_with_grade'  => $membersWithGrade,
            ];
        }

        return [
            'group_information' => $groupInformation,
            'form_1'            => $formData,
            'interview_information' => $interviewInformation,
            'group_grade'       => $gradeData,
        ];
    }

    public function getGroupProject(int $groupId){
        $groups = $this->groupRepo->getGroupProject($groupId);

        if (!$groups) {
            throw new PermissionDeniedException(
                'خطأ',
                'لا يوجد مجموعة بهذا الاسم',
                404
            );
        }

        return $groups;
    }

    public function groupsWithFiveMembers(){
        $groups = $this->groupRepo->getGroupsWithFiveMembers();

        if (!$groups) {
            throw new PermissionDeniedException(
                'خطأ',
                'لا يوجد مجموعة بهذا الاسم',
                404
            );
        }
        $groups->transform(function ($group) {
            $group->image = UrlHelper::imageUrl($group->image);
            return $group;
        });

        return $groups;
    }

    public function leaveGroup(int $groupId): void
    {
        $user = Auth::user();

        // تحقق انه عضو بالمجموعة
        if (!$this->groupMemberRepo->isMember($groupId, $user->id)) {
            throw new PermissionDeniedException('خطأ', 'أنت لست عضواً في هذه المجموعة.');
        }

        // إذا كان ليدر
        if ($this->groupMemberRepo->isLeader($groupId, $user->id)) {
            throw new PermissionDeniedException('غير مسموح', 'لا يمكنك مغادرة المجموعة بصفتك ليدر، انقل القيادة لعضو آخر أولاً.');
        }

        // تحقق من الاستمارة الأولى

        $group = $this->groupRepo->getById($groupId);
        $form1 = $group->projectForm()->first(); // <-- هنا نأخذ النموذج الفعلي

        if ($form1 && $form1->status === ProjectFormStatus::Approved) {
            throw new PermissionDeniedException('غير مسموح', 'لا يمكن مغادرة المجموعة بعد الموافقة على الاستمارة 1.');
        }

        // حذف العضو من المجموعة
        $group->members()->where('user_id', $user->id)->delete();

        // تحديث عدد الأعضاء
        $group->decrement('number_of_members');
    }
}
