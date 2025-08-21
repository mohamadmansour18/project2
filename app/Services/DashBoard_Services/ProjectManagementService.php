<?php

namespace App\Services\DashBoard_Services;

use App\Enums\FormSubmissionPeriodFormName;
use App\Exceptions\FormException;
use App\Models\FormSubmissionPeriod;
use App\Models\User;
use App\Repositories\FormSubmissionPeriodRepository;
use App\Repositories\UserRepository;
use App\Services\FcmNotificationDispatcherService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProjectManagementService
{
    public function __construct(
        protected FormSubmissionPeriodRepository $formSubmissionPeriodRepository,
        protected UserRepository $userRepository,
        protected FcmNotificationDispatcherService $fcmNotificationDispatcherService,
    )
    {}

    public function createForm(array $data , string $formName): void
    {
        $exists = $this->formSubmissionPeriodRepository->existsFormForCurrentYear($formName);

        if($exists)
        {
            throw new FormException('لايمكن اتمام هذه العملية !' , 'لايمكنك انشاء مواعيد جديدة للسنة الحالية للاستمارة لانه يوجد مواعيد حالية' , 422);
        }

        $form = $this->formSubmissionPeriodRepository->createForm([
            'form_name' => $formName == 'form1' ? FormSubmissionPeriodFormName::Form1 : FormSubmissionPeriodFormName::Form2,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);

        $students = $this->userRepository->getStudentCurrentYear();
        if($students->isNotEmpty())
        {
            if($formName == "form1") {
                $this->fcmNotificationDispatcherService->sendToUsers($students, 'تعين موعد الاستمارة 1', "قام رئيس القسم بتعين موعد الاستمار واحد بدءا من : $form->start_date");
            }
            elseif ($formName == "form2"){
                $this->fcmNotificationDispatcherService->sendToUsers($students, 'تعين موعد الاستمارة 2', "قام رئيس القسم بتعين موعد الاستمار اثنان بدءا من : $form->start_date");
            }
        }
    }

    public function updateForm(int $formId , array $data)
    {
        $form = $this->formSubmissionPeriodRepository->findById($formId);

        if(!$form)
        {
            throw new FormException('لايمكنك اجراء هذه العملية !' , 'لم يتم العثور على الاستمارة المطلوبة' , 404);
        }

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        if($end->lt($start))
        {
            throw new FormException('خطأ تحقق !' , 'تاريخ الانتهاء لا يمكن ان يكون قبل تاريخ البدء' , 422);
        }

        if($start->gt($end)){
            throw new FormException('خطأ تحقق !' , 'تاريخ البدء لا يمكن ان يكون بعد تاريخ الانتهاء' , 422);
        }

        $updateForm = $this->formSubmissionPeriodRepository->updateForm($form , [
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'],
        ]);

        $students = $this->userRepository->getStudentCurrentYear();
        $currentYear = now()->year;

        if($students->isNotEmpty())
        {
            if($updateForm->form_name === FormSubmissionPeriodFormName::Form1) {
                $this->fcmNotificationDispatcherService->sendToUsers($students, 'تعديل موعد الاستمارة 1', "قام رئيس القسم بتعديل موعد الاستمار واحد للعام : $currentYear");
            }
            elseif ($updateForm->form_name === FormSubmissionPeriodFormName::Form2)
            {
                $this->fcmNotificationDispatcherService->sendToUsers($students, 'تعديل موعد الاستمارة 2', "قام رئيس القسم بتعديل موعد الاستمار اثنان للعام : $currentYear");
            }
        }
    }

    public function forceDeleteForm(int $formId): void
    {
        $form = $this->formSubmissionPeriodRepository->findById($formId);

        if(!$form)
        {
            throw new FormException('لايمكنك اجراء هذه العملية !' , 'لم يتم العثور على الاستمارة المطلوبة' , 404);
        }

        $this->formSubmissionPeriodRepository->deleteForm($form);
    }

    public function getForm(string $formName): array
    {
        $form = $this->formSubmissionPeriodRepository->getFormForCurrentYear($formName);

        if(!$form)
        {
            throw new FormException('لايمكنك اجراء هذه العملية !' , 'لم يتم العثور على الاستمارة المطلوبة' , 404);
        }

        return [
            'id' => $form->id,
            'start_date' => $form->start_date->format('Y-m-d'),
            'end_date' => $form->end_date->format('Y-m-d'),
        ];
    }



}
