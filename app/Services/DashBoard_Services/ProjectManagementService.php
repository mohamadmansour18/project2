<?php

namespace App\Services\DashBoard_Services;

use App\Enums\FormSubmissionPeriodFormName;
use App\Exceptions\ProjectManagementException;
use App\Helpers\UrlHelper;
use App\Repositories\FormSubmissionPeriodRepository;
use App\Repositories\InterviewCommitteeRepository;
use App\Repositories\InterviewPeriodRepository;
use App\Repositories\UserRepository;
use App\Services\FcmNotificationDispatcherService;
use App\Traits\ApiSuccessTrait;
use Carbon\Carbon;
use DateInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectManagementService
{
    use ApiSuccessTrait;
    public function __construct(
        protected FormSubmissionPeriodRepository $formSubmissionPeriodRepository,
        protected UserRepository $userRepository,
        protected FcmNotificationDispatcherService $fcmNotificationDispatcherService,
        protected InterviewPeriodRepository $interviewPeriodRepository,
        protected InterviewCommitteeRepository $interviewCommitteeRepository,
    )
    {}

    /**
     * @throws ProjectManagementException
     */
    public function createForm(array $data , string $formName): void
    {
        $exists = $this->formSubmissionPeriodRepository->existsFormForCurrentYear($formName);

        if($exists)
        {
            throw new ProjectManagementException('! لايمكن اتمام هذه العملية' , 'لايمكنك انشاء مواعيد جديدة للسنة الحالية للاستمارة لانه يوجد مواعيد حالية' , 422);
        }

        $this->checkDate($data);

        DB::transaction( function () use ($data, $formName) {
            $form = $this->formSubmissionPeriodRepository->createForm([
                'form_name' => $formName == 'form1' ? FormSubmissionPeriodFormName::Form1 : FormSubmissionPeriodFormName::Form2,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]);

            $start_date = Carbon::parse($data['start_date'])->format('Y-m-d');

            $students = $this->userRepository->getStudentCurrentYear();
            if ($students->isNotEmpty()) {
                if ($formName == "form1") {
                    $this->fcmNotificationDispatcherService->sendToUsers($students, 'تعين موعد الاستمارة 1', "قام رئيس القسم بتعين موعد تقديم الاستمارة واحد بدءا من : $start_date");
                } elseif ($formName == "form2") {
                    $this->fcmNotificationDispatcherService->sendToUsers($students, 'تعين موعد الاستمارة 2', "قام رئيس القسم بتعين موعد تقديم الاستمارة اثنان بدءا من : $start_date");
                }
            }
        });
    }

    /**
     * @throws ProjectManagementException
     */
    public function updateForm(int $formId , array $data): void
    {
        $form = $this->formSubmissionPeriodRepository->findById($formId);

        if(!$form)
        {
            throw new ProjectManagementException('! لايمكن اتمام هذه العملية' , 'لم يتم العثور على الاستمارة المطلوبة' , 404);
        }

        $this->checkDate($data);

        DB::transaction( function () use ($form, $data) {
            $updateForm = $this->formSubmissionPeriodRepository->updateForm($form, [
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]);

            $students = $this->userRepository->getStudentCurrentYear();
            $currentYear = now()->year;
            if ($students->isNotEmpty()) {
                if ($updateForm->form_name === FormSubmissionPeriodFormName::Form1) {
                    $this->fcmNotificationDispatcherService->sendToUsers($students, 'تعديل موعد الاستمارة 1', "قام رئيس القسم بتعديل موعد تقديم الاستمارة واحد للعام : $currentYear");
                } elseif ($updateForm->form_name === FormSubmissionPeriodFormName::Form2) {
                    $this->fcmNotificationDispatcherService->sendToUsers($students, 'تعديل موعد الاستمارة 2', "قام رئيس القسم بتعديل موعد تقديم الاستمارة اثنان للعام : $currentYear");
                }
            }
        });
    }

    /**
     * @throws ProjectManagementException
     */
    public function forceDeleteForm(int $formId): void
    {
        $form = $this->formSubmissionPeriodRepository->findById($formId);

        if(!$form)
        {
            throw new ProjectManagementException('! لايمكن اتمام هذه العملية' , 'لم يتم العثور على الاستمارة المطلوبة' , 404);
        }

        $this->formSubmissionPeriodRepository->deleteForm($form);
    }

    /**
     * @throws ProjectManagementException
     */
    public function getForm(string $formName): array
    {
        $form = $this->formSubmissionPeriodRepository->getFormForCurrentYear($formName);

        if(!$form)
        {
            throw new ProjectManagementException('! لايمكن اتمام هذه العملية' , 'لم يتم العثور على الاستمارة المطلوبة' , 404);
        }

        return [
            'id' => $form->id,
            'start_date' => $form->start_date->format('Y-m-d'),
            'end_date' => $form->end_date->format('Y-m-d'),
        ];
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @throws ProjectManagementException
     */
    public function createInterview(array $data): void
    {
        $interview = $this->formSubmissionPeriodRepository->existsFormForCurrentYear(FormSubmissionPeriodFormName::Interviews->value);
        if($interview)
        {
            throw new ProjectManagementException('! لايمكن اتمام هذه العملية', 'تم تعيين موعد مقابلات نهائية بالفعل لهذه السنة', 422);
        }

        $this->checkDate($data);

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        $coveredDays = $start->diffInDays($end) + 1;
        if ($coveredDays > 7) {
            throw new ProjectManagementException('! لايمكن اتمام هذه العملية', 'يجب ألا تتجاوز فترة المقابلات سبعة أيام (بما في ذلك يوم البداية والنهاية)', 422);
        }

        //check if days located in [start date - end date]
        $validDays = [];
        $period = new \DatePeriod($start , new DateInterval('P1D'), $end->copy()->addDay());
        foreach($period as $date)
        {
            $validDays [] = $date->format('l');
        }

        foreach ($data['days'] as $day)
        {
            if(!in_array($day, $validDays , true))
            {
                throw new ProjectManagementException('خطأ في الأيام',"اليوم $day لا يقع ضمن الفترة المحددة", 422);
            }
        }

        DB::transaction(function () use ($data , $start , $end){
            $form = $this->formSubmissionPeriodRepository->createForm([
                'form_name' => FormSubmissionPeriodFormName::Interviews,
                'start_date' => $start,
                'end_date' => $end,
            ]);

            $this->interviewPeriodRepository->create([
                'start_date' => $start,
                'end_date'   => $end,
                'days'       => $data['days'],
            ]);

            $students = $this->userRepository->getStudentCurrentYear();
            if($students->isNotEmpty())
            {
                $this->fcmNotificationDispatcherService->sendToUsers(
                    $students,
                    '! تعيين موعد المقابلات النهائية',
                    "قام رئيس القسم بتحديد موعد المقابلات النهائية بدءًا من : {$start->format('Y-m-d')}"
                );
            }
        });
    }

    public function updateInterview(array $data , int $periodId): void
    {
        $form = $this->formSubmissionPeriodRepository->getFormForCurrentYear(FormSubmissionPeriodFormName::Interviews->value);
        $interPeriod = $this->interviewPeriodRepository->findOrFail($periodId);

        if(!$form)
        {
            throw new ProjectManagementException('! لايمكن اتمام هذه العملية', 'لا يوجد مواعيد مقابلات حالية للسنة الحالية', 404);
        }

        $this->checkDate($data);

        $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
        $data['end_date']   = date('Y-m-d', strtotime($data['end_date']));

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        $coveredDays = $start->diffInDays($end) + 1;
        if ($coveredDays > 7) {
            throw new ProjectManagementException('! لايمكن اتمام هذه العملية', 'يجب ألا تتجاوز فترة المقابلات سبعة أيام (بما في ذلك يوم البداية والنهاية)', 422);
        }


        //check if days located in [start date - end date]
        $validDays = [];
        $period = new \DatePeriod($start , new DateInterval('P1D'), $end->copy()->addDay());
        foreach($period as $date)
        {
            $validDays [] = $date->format('l');
        }

        foreach ($data['days'] as $day)
        {
            if(!in_array($day, $validDays , true))
            {
                throw new ProjectManagementException('! خطأ في الأيام',"اليوم $day لا يقع ضمن الفترة المحددة", 422);
            }
        }

        DB::transaction(function () use ($data , $form , $interPeriod){
            $this->formSubmissionPeriodRepository->updateForm($form , $data);
            $this->interviewPeriodRepository->update($interPeriod , $data);

            $students = $this->userRepository->getStudentCurrentYear();
            if($students->isNotEmpty())
            {
                $this->fcmNotificationDispatcherService->sendToUsers(
                    $students,
                    '! تعديل موعد المقابلات النهائية',
                    "قام رئيس القسم بتعديل موعد المقابلات النهائية: من {$data['start_date']} إلى {$data['end_date']}"
                );
            }
        });


    }

    public function deleteInterview(int $periodId): void
    {
        DB::transaction(function () use ($periodId){
            $interview = $this->interviewPeriodRepository->findOrFail($periodId);
            $formSubmissionPeriod = $this->formSubmissionPeriodRepository->getFormForCurrentYear(FormSubmissionPeriodFormName::Interviews->value);

            if(!$formSubmissionPeriod)
            {
                throw new ProjectManagementException('! لا يمكن اجراء هذه العملية', 'سجل مواعيد المقابلات النهائية المحدد الذي تحاول الوصول اليه غير موجود', 404);
            }

            $this->interviewPeriodRepository->forceDelete($interview);
            $this->formSubmissionPeriodRepository->deleteForm($formSubmissionPeriod);
        });
    }

    public function getInterview(): array
    {
        $interview = $this->interviewPeriodRepository->getCurrentYearInterview();

        if(!$interview)
        {
            throw new ProjectManagementException('! لايمكنك اجراء هذه العملية' , 'لم يتم العثور على مواعيد مقابلات للسنة الحالية' , 404);
        }

        return [
            'id'         => $interview->id,
            'start_date' => $interview->start_date,
            'end_date'   => $interview->end_date,
            'days'       => $interview->days,
        ];
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    public function generateAndDownloadFormsDate(): BinaryFileResponse|JsonResponse
    {
        try {
            //fetch dates of all forms (1 & 2 & interview)
            $year = now()->year;
            $form1 = $this->formSubmissionPeriodRepository->getFormForCurrentYear(FormSubmissionPeriodFormName::Form1->value);
            $form2 = $this->formSubmissionPeriodRepository->getFormForCurrentYear(FormSubmissionPeriodFormName::Form2->value);
            $interview = $this->interviewPeriodRepository->getCurrentYearInterview();

            //verify if data exists or not
            if (!$form1 && !$form2 && !$interview) {
               return response()->json([
                   'title' => '! لا يمكن إنشاء الملف',
                   'body' => 'لا توجد مواعيد للاستمارتيْن أو للمقابلات النهائية في السنة الحالية',
                   'statusCode' => 422
               ], 422);
            }

            //translate days of week
            $dayMap = [
                'Sunday' => 'الأحد',
                'Monday' => 'الاثنين',
                'Tuesday' => 'الثلاثاء',
                'Wednesday' => 'الأربعاء',
                'Thursday' => 'الخميس',
                'Friday' => 'الجمعة',
                'Saturday' => 'السبت',
            ];

            $daysArabic = [];
            if ($interview && is_array($interview->days)) {
                foreach ($interview->days as $day) {
                    $daysArabic [] = $dayMap[$day] ?? $day;
                }
            }

            //fetch logo
            $logoPath = storage_path('app/public/application_logo/logo.jpg');
            $logoImg = file_exists($logoPath) ? 'file://' . $logoPath : '';

            //fetch HTML code from blade to convert it to PDF
            $html = view('pdfs.announcementFormDate', [
                'year' => $year,
                'form1' => $form1,
                'form2' => $form2,
                'interview' => $interview,
                'daysAr' => $daysArabic,
                'logoImg' => $logoImg,
            ])->render();

            //create dir related of mPDF to put inside it configuration the library needs it
            $mpdfTemp = storage_path('app/mpdf-temp');
            if (!File::exists($mpdfTemp)) {
                File::makeDirectory($mpdfTemp, 0755, true);
            }

            //file store path
            $disk = Storage::disk('public');
            $dir = 'admin/announcement/';
            if (!$disk->exists($dir)) {
                $disk->makeDirectory($dir , 0755 , true);
            }
            $filename = "announcementFormDate_{$year}_" . now()->format('YmdHis') . ".pdf";
            $relativePath = $dir . '/' . $filename;
            $absolutePath = $disk->path($relativePath);

            //create PDF and store it in specific path
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'directionality' => 'rtl',
                'autoLangToFont' => true,
                'autoScriptToLang' => true,
                'tempDir' => $mpdfTemp,
            ]);

            $mpdf->WriteHTML($html);
            $mpdf->Output($absolutePath, Destination::FILE);

            //download file then delete it from my file project
            return response()->download($absolutePath, $filename)->deleteFileAfterSend(true);
        }
        catch(\Throwable $exception)
        {
            Log::error('PDF generation failed' , [
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTraceAsString(),
            ]);
            throw new ProjectManagementException('! حدث خطأ اثناء التنفيذ', 'حدث خطا غير متوقع يرجى اعادة المحاولة لاحقا', 500);
        }
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function getAvailableDoctorsNotInCommittee(): Collection|array
    {
        $doctor = $this->userRepository->getAvailableDoctors();
        if(!$doctor)
        {
            return [];
        }
        return $doctor;
    }

    public function createCommittee(int $doctor1ID , int $doctor2ID , int $supervisorId): void
    {
        $firstDoctor = $this->interviewCommitteeRepository->existsForDoctorInYear($doctor1ID);
        if($firstDoctor)
        {
            throw new ProjectManagementException("! لايمكن اجراء هذه العملية","الدكتور {$doctor1ID} موجود بالفعل في لجنة لهذه السنة" , 422);
        }

        $twoDoctor = $this->interviewCommitteeRepository->existsForDoctorInYear($doctor2ID);
        if($twoDoctor)
        {
            throw new ProjectManagementException("! لايمكن اجراء هذه العملية","الدكتور {$doctor2ID} موجود بالفعل في لجنة لهذه السنة" , 422);
        }

        if(!in_array($supervisorId , [$doctor1ID , $doctor2ID]))
        {
            throw new ProjectManagementException("! لايمكن اجراء هذه العملية","الدكتور المشرف يجب أن يكون أحد الدكتورين المختارين" , 422);
        }

        $memberId = ($supervisorId === $doctor1ID) ? $doctor2ID : $doctor1ID ;

        $interviewCommittee = $this->interviewCommitteeRepository->createCommittee([
            'supervisor_id' => $supervisorId,
            'member_id'     => $memberId,
        ]);
    }

    public function getCommitteesForCurrentYear()
    {
        $committees = $this->interviewCommitteeRepository->getCommitteesForCurrentYear();

        return $committees->map(function($committee){
            return [
                'id' => $committee->id,
                'first_doctor' => $committee->adminSupervisor->name,
                'profileImage_One' => UrlHelper::imageUrl($committee->adminSupervisor->profile->profile_image) ?? null,
                'second_doctor' => $committee->adminMember->name,
                'profileImage_two' => UrlHelper::imageUrl($committee->adminMember->profile->profile_image) ?? null,
                'supervisor_name' => $committee->supervisor_id === $committee->adminSupervisor->id ? $committee->adminSupervisor->name : $committee->adminMember->name,
            ];
        });
    }

    public function deleteCommittee(int $committeeID): void
    {
        $committee = $this->interviewCommitteeRepository->findOrFillById($committeeID);

        if(!is_null($committee->days) || !is_null($committee->start_interview_time) || !is_null($committee->end_interview_time))
        {
            throw new ProjectManagementException('! لايمكن اجراء هذه العملية' , 'لا يمكن حذف اللجنة بعد تحديد مواعيد المقابلات لها' , 422);
        }

        $this->interviewCommitteeRepository->forceDelete($committee);
    }

    public function notifyInterviewCommitteeDoctors(): void
    {
        $doctors = $this->userRepository->getDoctorInCommitteeCurrentYear();

        if($doctors->isEmpty())
        {
            throw new ProjectManagementException('! لايوجد لجان للسنة الحالية' , 'لم يتم العثور على اي دكتور ضمن اي لجنة في السنة الحلية حتى يتم ارسال اشعار له' , 404);
        }

        $this->fcmNotificationDispatcherService->sendToUsers($doctors,'تم اختيارك كلجنة مقابلة', 'لقد تم اختيارك لتكون ضمن لجنة مقابلة في السنة الحالية');
    }

    public function generateAndDownloadCommittee(): BinaryFileResponse|JsonResponse
    {
        $committees = $this->interviewCommitteeRepository->getCommitteesForYearOrdered();

        if($committees->isEmpty())
        {
            return response()->json([
                'title' => '! لا يمكن إنشاء الملف',
                'body' => 'لاتوجد لجان مقابلات للسنة الحالية قم بتعين لجان اولا',
                'statusCode' => 404
            ], 404);
        }

        try {
            //fetch logo from project files
            $logoPath = storage_path('app/public/application_logo/logo.jpg');
            $logoImg  = file_exists($logoPath) ? 'file://' . $logoPath : '';
            $currentYear = now()->year;

            //store html & css blade class in var and send to it some data
            $html = view('pdfs.committeeInterview' , [
                'year'       => $currentYear,
                'committees' => $committees,
                'logoImg'    => $logoImg,
            ])->render();

            //create temp directory related of mpdf library
            $mpdfTemp = storage_path('app/mpdf-temp');
            if (!File::exists($mpdfTemp)) {
                File::makeDirectory($mpdfTemp, 0755, true);
            }

            //file store path
            $disk = Storage::disk('public');
            $dir = 'admin/committee';
            if (!$disk->exists($dir)) {
                $disk->makeDirectory($dir , 0755 , true);
            }
            $filename = "announcementCommittee_{$currentYear}_" . now()->format('YmdHis') . ".pdf";
            $relativePath = $dir . '/' . $filename;
            $absolutePath = $disk->path($relativePath);


            //generate the pdf file and store it in project files
            $mpdf = new Mpdf([
                'mode'             => 'utf-8',
                'format'           => 'A4',
                'directionality'   => 'rtl',
                'autoLangToFont'   => true,
                'autoScriptToLang' => true,
                'tempDir'          => $mpdfTemp,
            ]);

            $mpdf->WriteHTML($html);
            $mpdf->Output($absolutePath, Destination::FILE);

            return response()->download($absolutePath, $filename)->deleteFileAfterSend(true);

        }catch (\Throwable $exception)
        {
            Log::error('PDF generation failed' , [
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTraceAsString(),
            ]);
            throw new ProjectManagementException('! حدث خطأ اثناء التنفيذ', 'حدث خطا غير متوقع يرجى اعادة المحاولة لاحقا', 500);
        }
    }

    //--------------------->>>>>>>>>>[HELPERS]<<<<<<<<<<---------------------//
    private function checkDate(array $data): void
    {
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $currentYear = now()->year;

        if($start->year !== $currentYear || $end->year !== $currentYear)
        {
            throw new ProjectManagementException('! خطأ في التواريخ المدخلة', 'يجب أن تكون التواريخ المدخلة ضمن السنة الحالية', 422);
        }

        if ($start->lte(now())) {
            throw new ProjectManagementException('! خطأ في التواريخ المدخلة', 'تاريخ البدء يجب أن يكون بعد تاريخ اليوم', 422);
        }


        if($end->lt($start))
        {
            throw new ProjectManagementException('! خطأ في التواريخ المدخلة' , 'تاريخ الانتهاء لا يمكن ان يكون قبل تاريخ البدء' , 422);
        }

        if($start->gt($end)){
            throw new ProjectManagementException('! خطأ في التواريخ المدخلة' , 'تاريخ البدء لا يمكن ان يكون بعد تاريخ الانتهاء' , 422);
        }

        $diff = $start->diffInDays($end);
        if ($diff < 2) {
            throw new ProjectManagementException('! خطأ في التواريخ المدخلة', 'يجب أن يفصل بين تاريخ البدء والانتهاء يوم واحد على الأقل', 422);
        }
    }

}
