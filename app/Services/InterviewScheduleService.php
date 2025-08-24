<?php

namespace App\Services;

use App\Models\InterviewCommittee;
use App\Repositories\InterviewScheduleRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InterviewScheduleService
{
    /*
      المنطقة الزمنية
     توقيت بداية موعد اي مقابلة في اي يوم من ايام المقابلات
     توقيت نهاية موعد اي مقابلة في اي يوم من ايام المقابلات
     مدة كل مقابلة
      عدد المقابلات الممكنة في اليوم الواحد علما ان الوقت بين البدء والانتهاء هو ست ساعات وفي كل ساعة يمكن ان يتم اجراء ثلاث مقابلات اي : 3 * 6 = 18 مقابلة لليوم الواحد
    */
    private const TZ = 'Asia/Damascus';
    private const DAY_START = '09:00';
    private const DAY_END   = '15:00';
    private const SLOT_MIN  = 20;
    private const SLOTS_PER_COMMITTEE = 18;
    public function __construct(
        protected InterviewScheduleRepository $repo,
    )
    {}

    public function generateAssignAndDownload(): BinaryFileResponse|JsonResponse
    {
        $year = now()->year;

        /** اولا : يتم تحضير مسار الحفظ لملف البيدي أف وبعدها يتم التأكد هل هذا المسار موجود مسبقا بنفس اسم الملف للسنة الحالية ؟ اذا كان نعم يتم الغاء عملية الانشاء **/
        $disk = Storage::disk('public');
        $dir = 'admin/interview';
        if(!$disk->exists($dir))
        {
            $disk->makeDirectory($dir, 0755, true);
        }
        $filename = "Interview_{$year}.pdf";
        $relPath  = $dir . '/' . $filename;
        $absPath = $disk->path($relPath);

        if($disk->exists($relPath))
        {
            return response()->json([
                'title' => 'لايمكن اتمام عملية الانشاء !',
                'body' => 'يوجد ملف مقابلات منشأ مسبقًا لهذه السنة. احذفه أولًا قبل إعادة الإنشاء',
                'statusCode' => 422
            ] , 422);
        }

        /** ثانيا : يتم جلب فترة المقابلات المحددة من رئيس القسم واذا لم تكن موجودة نظهر رسالة خطا **/
        $period = $this->repo->getCurrentYearPeriod();
        if (!$period)
        {
            return response()->json([
                'title' => 'لايمكن اتمام عملية الانشاء !',
                'body' => 'لا توجد فترة مقابلات مخزنة للسنة الحالية قم بتعيناه اولا ومن ثم حاول مرة اخرى',
                'statusCode' => 422
            ] , 422);
        }

        /** يتم التاكد من عدم وجود اي مواعيد مقابلات مخزنة للسنة الحلية */
        if($this->repo->interviewSchedulesExistForYear())
        {
            return response()->json([
                'title' => 'لايمكن اتمام عملية الانشاء !',
                'body' => 'هناك مواعيد مقابلات مخزّنة مسبقًا لهذه السنة. لا يمكن الإنشاء مجددًا',
                'statusCode' => 422
            ] , 422);
        }

        /** نستخرج الايام الفعلية من مواعيد المقابلات (بداية تاريخ المقابلات ونهايتها) سنستخرج الايام بصيغة تاريخ وليس بصيغة يوم كما هو مخزن لدينا **/
        $start = Carbon::parse($period->start_date , self::TZ)->startOfDay();
        $end   = Carbon::parse($period->end_date , self::TZ)->endOfDay();
        $allowedDays = is_array($period->days) ? $period->days : [];


        $dateList = [];                                                        //هنا سنخزن التواريخ الفعلية للايام التي فيها مقابلات على شكل تاريخ وليس على شكل اسم اليوم //
        $cur = $start->copy();                                                 //هنا سنأخذ نسخة من تاريخ بداية المقابلات من اجل ان لايتم التعديل على المتغير الاساسي الذي يحوي الوقت الفعلي //
        while($cur->lte($end))                                                 // هنا نفحص اذا كل يوم من تاريخ البداية هو اصغر او يساوي تاريخ النهاية ونستمر بالتكرار هكذا حتى يتطابق تاريخ البداية مع تاريخ النهاية حيث سنقوم بزيادة تاريخ البداية بمقدار يوم في كل تكرار //
        {
            if(in_array($cur->englishDayOfWeek , $allowedDays , true))   // لو اسم اليوم الحالي موجود ضمن مصفوفة ايام المقابلات نقوم باضافته الى مصفوفة تواريخ الايام //
            {
                $dateList [] = $cur->toDateString();
            }
            $cur->addDay();
        }
        if(empty($dateList))
        {
            return response()->json([
                'title' => 'لايمكن اتمام عملية الانشاء !',
                'body' => 'لا توجد أيام مقابلات فعلية ضمن الفترة المحددة بين بدء موعد المقابلات وانتهاء موعد المقابلات',
                'statusCode' => 422
            ] , 422);
        }

        /** ثالثا : نقوم بالتاكد من وجود لجان مقابلة للسنة الحالية وان عدد اللجان اكبر من عدد الايام كي لايبقى يوم بلا لجنة مقابلة */
        $committees = $this->repo->getUnassignedCommitteesForYear();
        if ($committees->isEmpty())
        {
            return response()->json([
                'title' => 'لايمكن اتمام عملية الانشاء !',
                'body' => 'لم تقم بتعيين لجان مقابلات للسنة الحالية قم بتعينهم اولا ومن ثم حاول مرة اخرى',
                'statusCode' => 422
            ] , 422);
        }

        if ($committees->count() < count($dateList))
        {
            return response()->json([
                'title' => 'لايمكن اتمام عملية الانشاء !',
                'body' => 'عدد اللجان أقل من عدد أيام المقابلات الرجاء زيادة عدد اللجان ومن ثم حاول مرة اخرى',
                'statusCode' => 422
            ] , 422);
        }

        /** رابعا : يتم جلب جميع الغروبات المؤهلة لان تقدم مقابلة نهائية */
        $eligibleGroups = $this->repo->getEligibleGroupsForYear();
        if($eligibleGroups->isEmpty())
        {
            return response()->json([
                'title' => 'لايمكن اتمام عملية الانشاء !',
                'body' => 'لاتوجد غروبات مؤهلة لأن تترشح الى المقابلات النهائية',
                'statusCode' => 422
            ] , 422);
        }

        /** نتحقق من السعة ان كل لجنة عندها 18 موعد فقط من الساعة 9 صباحة ال 3 عصرا كل 20 دقيقة يوجد مقابلة */
        $capacity = $committees->count() * self::SLOTS_PER_COMMITTEE;
        if($eligibleGroups->count() > $capacity)
        {
            return response()->json([
                'title' => 'لايمكن اتمام عملية الانشاء !',
                'body' => 'عدد اللجان غير كافي ليغطي جميع الغروبات التي يحق لها التقدم لمقابلة , الرجاء زيادة عدد اللجان ومن ثم المحاولة لاحقا',
                'statusCode' => 422
            ] , 422);
        }

        /** خامسا : توزيع اللجان على الايام بحيث يتم التوزيع بطريقة (راوند روبين) اي غطّ كل يوم بلجنة واحدة على الاقل ثم وزع باقي اللجان بهكذا نضمن العدالة في التوزيع وان كل يوم من ايام المقابلة يحتوي على لجنة واحدة على الاقل او اكثر **/
        $finalCommittees = [];                                   //كل عنصر من هذه المصفوفة عبارة عن : ['committee'=>InterviewCommittee, 'date'=>'Y-m-d']//
        $occupiedDatesCount = [];                                //عداد كم لجنة في كل يوم //
        $dateIdx = 0;                                            // مؤشر يوم سنقوم بتحريكه بشكل دائري //

        /** في الخطوة الاولى نقوم بتوزيع اللجان على كل الايام الموجودة لدينا وفي الخطوة التالية سنقوم بتوزيع ما تبقى من اللجان على باقي الايام */
        foreach ($dateList as $date)                             // اولا نقوم باعطاء لجنة لكل يوم من ايام المقابلة المحددة //
        {
            /** @var InterviewCommittee $committee */            // مجرد تعليق يخبر ال IDE بنوع المتغير //
            $committee = $committees->shift();                   // خذ اول لجنة من الكولكشن ومن ثتم قم بازالتها منه //
            if(!$committee)                                      // لو لم يعد هناك لجان في الكولكشن توقف واخرج من الحلقة //
            {
                break ;
            }
            $finalCommittees [] = [
                'committee' => $committee ,
                'date' => $date
            ];
            $occupiedDatesCount [$date] = 1;                     // نخزن في المصفوفة ان اليوم X اصبح لديه لجنة واحدة فيه //
        }

        /** الخطوة التالية توزيع ماتبقى من لجان على الايام المحددة بطريقة عادلة */
        foreach ($committees as $committee)                      // بسبب ال foreach السابقة وبفضل تابع ال shift اصبحت الكلوكشت الخاصة بعدد اللجان ناقصة بمقدار n لجنة حيث n يساوي عدد الايام المحددة للمقابلات وبالتالي بقي في الكولكشن اللجان التي لم يتم توزيعها على اي يوم //
        {
            $date = $dateList[$dateIdx % count($dateList)];      // لنفترض ان عدد ايام المقابلات هي 3 فستكون البواقي في كل دورة هي 0و1و2 ثم 0و1و2 وهكذا نضمن التوزيع العادل حيث يتم اختيار يوم المقابلة الذي سنوزع عليه ماتبقى من اللجان بالدور //
            $finalCommittees [] = [
                'committee' => $committee,
                'date' =>  $date
            ];
            $occupiedDatesCount[$date] = ($occupiedDatesCount[$date] ?? 0) + 1;         // نقوم بالزيادة على عداد عدد اللجان التي ستقابل في اليوم المحدد بمقدار واحد //
            $dateIdx++;                                                                 //نزيد المؤشر بمقدار واحد من اجل ان يقوم باحضار اليوم التالي في مصفوفة الايام //
        }

        /** سادسا : بناء السلوتات الزمنية لكل لجنة في يومها المحدد حيث ان مدة كل سلوت هي عشرين دقيقة */
        $allSlots = [];                                 // كل عنصر من هذه المصفوفة سيكون له الشكل : ['committee'=>InterviewCommittee,'date'=>'Y-m-d','start'=>Carbon,'end'=>Carbon]
        foreach ($finalCommittees as $item)
        {
            /** @var InterviewCommittee $comm */
            $comm = $item['committee'];
            $date = $item['date'];

            $startTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . self::DAY_START, self::TZ);        // نبدا الوقت في كل دورة من الحلقة من التاريخ المحدد ومن الساعة التاسعة صباحا //
            $endTime   = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . self::DAY_END,   self::TZ);        // ينتهي الوقت في كل دورة من الحلقة من التاريخ المحدد ومن الساعة الثالثة عصرا //

            $slotStart = $startTime->copy();
            while($slotStart->lt($endTime))                                                                                          // الحلقة ستبقى تعيد نفسها طول ما ان وقت البداية اصغر من وقت النهاية المحدد //
            {
                $slotEnd = $slotStart->copy()->addMinutes(self::SLOT_MIN);                                                     // نقوم بأخذ وقت البداية ونزيد عليه بمقدار 20 دقيقة لحتى يصبح وقت البداية مساويا لوقت النهاية //
                if($slotEnd->gt($endTime))
                {
                    break ;
                }
                $allSlots[] = [
                    'committee' => $comm,
                    'date'      => $date,
                    'start'     => $slotStart->copy(),
                    'end'       => $slotEnd->copy(),
                ];
                $slotStart->addMinutes(self::SLOT_MIN);                                                                         // انتقل للموعد الذي بعده من اجل نفس اليوم اي بمعنى اخر زيد عداد الحلقة بمقدار كذا //
            }
        }

        /** نرتب السلوتات تصاعديا اولا حسب اليوم ثم حسب اللجنة ثم حسب الوقت من اجل العرض بطريقة مرتبة */
        usort($allSlots , fn($a, $b) =>                                                                   // نقوم باستخدام التابع usort بترتيب مصفوفة السلوتات وفق دالة مقارنة متخصصة fn حيث يتم المقارنة بهذا التابع fn وباستخدام العملية <=> حيث ان هذه العملية تعيد -1 اذا كان اليسار اصغر من اليمين و 0 اذا متساويين و 1 اذا اليسار اكبر من اليمين //
            [$a['date'], $a['committee']->id, $a['start']->timestamp]                                            // هنا يتم المقارنة حسب مصفوفة من القيم اولا حسب ال date اذا تساوا في المصفوفتين نقارن حسب id اللجنة اذا تساوا نقارن اخيرا حسب start وبالتالي سيعدا ترتيب المصفوفة حسب دالة المقارنة بال date اولا ثم ال id ثم ال time //
            <=>
            [$b['date'], $b['committee']->id, $b['start']->timestamp]);

        /**Example of sorting :
        [
             ['date' => '2025-09-01', 'committee' => {id:1}, 'start' => 09:00],
             ['date' => '2025-09-01', 'committee' => {id:1}, 'start' => 10:00],
             ['date' => '2025-09-01', 'committee' => {id:3}, 'start' => 13:00],
             ['date' => '2025-09-02', 'committee' => {id:2}, 'start' => 09:00],
        ]
        **/

        /** سابعا : نوزع الغروبات على السلوتات بالتساوي بطريقة (الراوند روبن) حيث انه يكون مجموعة واحدة لكل سلوت */
        $groups = $eligibleGroups->values();                    // مصفوفة مرتبة تصاعديا من الغروبات حسب ال id وذلك بفضل تابع جلب الغروبات الموجود في الريبو //
        $rowsByCommittee = [];                                  // نبني createMany لكل لجنة حسب ال id الخاص بكل لجنة //
        $groupIdx = 0;                                          // مؤشر اي غروب ينحجز له موعد مقابلة الان //
        $seenGroup = [];                                        // حماية اضافة نتأكد فيها اننا لانقوم بتكرار نفس الغروب على لجنة ثانية //

        foreach ($allSlots as $slot)
        {
            if($groupIdx >= $groups->count())                   // اذا مؤشر الغروبات اصبح اكبر من عدد الغروبات المسموح لها التقدم للمقابلة توقف واخرج من حلقة التكرار بهذا نكون قد وزعنا جميع الغروبات //
            {
                break;
            }

            $g = $groups[$groupIdx];                            // خذ الغروب الحالي فقط في كل تكرار مع زيادة تصاعديا بالعداد غروب غروب //

            if(isset($seenGroup[$g->id]))                       // اذا الغروب الحالي كان مكررا اي تم توزيعه على فترة نتخطاه وتزيد العداد بمقدار واحد //
            {
                $groupIdx++;
                continue;
            }

            $seenGroup[$g->id] = true;                          // نحدد فيها ان الغروب ذو ال id كذا تم اخذه وتحديد مقابلة له //

            $rowsByCommittee[$slot['committee']->id][] = [      // اي مصفوفة مجمعة حسب id اللجنة //
                'group_id' => $g->id,
                'interview_date' => $slot['date'],
                'interview_time' => $slot['start'],
                'interview_end_time' => $slot['end'],
                'created_at'         => now(),
                'updated_at'         => now(),
            ];

            $groupIdx++;

            /**Example of $rowsByCommittee :
            [
             // committee->id = 1
              1 => [
                      [
                          'group_id'           => 101,
                          'interview_date'     => '2025-09-01',
                          'interview_time'     => '2025-09-01 09:00:00',
                          'interview_end_time' => '2025-09-01 10:00:00',
                          'created_at'         => '2025-08-24 12:34:56',
                          'updated_at'         => '2025-08-24 12:34:56',
                      ],[],[],[],.....
              ],

             // committee->id = 2
              2 => [
                      [
                          'group_id'           => 102,
                          'interview_date'     => '2025-09-01',
                          'interview_time'     => '2025-09-01 10:00:00',
                          'interview_end_time' => '2025-09-01 11:00:00',
                          'created_at'         => '2025-08-24 12:34:56',
                          'updated_at'         => '2025-08-24 12:34:56',
                      ],[],[],[],.....
              ],
            ]
             **/
        }

        /** ثامنا : انشاء المواعيد جميعها عبر اليكوينت وتحديث الايام وتواريخ بدء المقابلة والانتهاء لكل لجنة في جدول اللجان */
        try {
            DB::transaction(function () use ($finalCommittees, $rowsByCommittee) {
                foreach ($finalCommittees as $item)
                {
                    /** @var InterviewCommittee $c */
                    $c = $item['committee'];
                    $date = $item['date'];

                    if(is_null($c->days) || is_null($c->start_interview_time) || is_null($c->end_interview_time))
                    {
                        $startTs = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . self::DAY_START, self::TZ);
                        $endTs   = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . self::DAY_END,   self::TZ);

                        $this->repo->updateCommitteeAssignment($c , $date , $startTs , $endTs);
                    }

                    $rows = $rowsByCommittee[$c->id] ?? [];
                    if(!empty($rows))
                    {
                        $this->repo->createSchedulesForCommittee($c , $rows);
                    }
                }
            });
        }catch (\Throwable $e) {
            Log::error('Interview schedule transaction failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'title' => 'حدث خطا غير متوقع في قاعدة البيانات !' ,
                'body' => 'تعذر إتمام عملية التوزيع والحفظ داخل قاعدة المعطيات (تم التراجع عن كل التغييرات)',
            ], 500);
        }

        /** تاسعا : واخيرا بعد تخزين البيانات في الداتا بيز يتم حفظ وتنزيل ملف اعلانات المقابلات النهائية في ملفات المشروع */
        try {

            $logoPath = storage_path('app/public/application_logo/logo.jpg');
            $logoImg  = file_exists($logoPath) ? 'file://' . $logoPath : '';

            $schedules = $this->repo->getSchedulesForYearWithRelations();
            $byDay = $schedules->groupBy(fn($row) => (string) $row->interview_date);

            $html = view('pdfs.interviewSchedule', [
                'year'     => $year,
                'period'   => $period,
                'dateList' => array_values(array_unique(
                    $schedules->pluck('interview_date')->map(fn($d)=>(string)$d)->toArray()
                )),
                'byDay'    => $byDay,
                'logoImg'  => $logoImg,
            ])->render();

            $mpdfTemp = storage_path('app/mpdf-temp');
            if (!File::exists($mpdfTemp)) {
                File::makeDirectory($mpdfTemp, 0755, true);
            }

            $mpdf = new Mpdf([
                'mode'             => 'utf-8',
                'format'           => 'A4',
                'directionality'   => 'rtl',
                'autoLangToFont'   => true,
                'autoScriptToLang' => true,
                'tempDir'          => $mpdfTemp,
            ]);

            $mpdf->WriteHTML($html);
            $mpdf->Output($absPath, Destination::FILE);

            return response()->download($absPath, $filename);
        }catch(\Throwable $e)
        {
            Log::error('Interview PDF generation failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'title' => 'حدث خطا غير متوقع اثناء عملية انشاء/تنزيل الملف !' ,
                'message' => 'تم حفظ المواعيد وتحديث اللجان، لكن فشل إنشاء/تنزيل الملف',
            ], 500);
        }

    }

    public function deleteCurrentYearAssets(): JsonResponse
    {
        $year = now()->year ;

        $disk = Storage::disk('public');
        $dir  = 'admin/interview';
        $filename = "Interview_{$year}.pdf";
        $relPath  = $dir . '/' . $filename;

        if (!$disk->exists($relPath)) {
            return response()->json([
                'title' => 'لايمكن اتمام عملية الحذف !',
                'body' => 'لا يوجد ملف مواعيد للسنة الحالية ليتم حذفه',
            ], 404);
        }

        $period = $this->repo->getCurrentYearPeriod();
        if(!$period)
        {
            return response()->json([
                'title' => 'لايمكن اتمام عملية الحذف !',
                'body' => 'لا توجد فترة مقابلات محدّدة لهذه السنة للتحقق من صلاحية الحذف',
            ], 422);
        }

        $today = Carbon::now(self::TZ)->toDateString();
        $startDate = Carbon::parse($period->start_date, self::TZ)->toDateString();

        if($today > $startDate)
        {
            return response()->json([
                'title' => 'لايمكن اتمام عملية الحذف !',
                'body' => 'لا يمكن حذف ملف هذه السنة بعد بدء فترة المقابلات',
            ], 422);
        }

        try {
            if (!$disk->delete($relPath)) {
                Log::error("Failed to delete file: {$relPath}");
                return response()->json([
                    'title' => 'حدث خطا غير متوقع اثناء عملية حذف الملف !' ,
                    'message' => 'تعذر حذف الملف من نظام الملفات.',
                ], 500);
            }
        }catch (\Throwable $e) {
            Log::error('Filesystem delete failed', ['message' => $e->getMessage()]);
            return response()->json([
                'title' => 'حدث خطا غير متوقع اثناء عملية حذف الملف !' ,
                'message' => 'حدث غير متوقع اثناء اتمام العملية الرجاء المحاولة لاحقا',
            ], 500);
        }

        try {
            DB::transaction(function () use ($year) {
                $this->repo->deleteSchedulesForYear($year);
                $this->repo->resetCommitteesForYear($year);
            });

            return response()->json([
                'title' => 'تمت عملية العملية بنجاح !' ,
                'body' => 'تم حذف الملف بنجاح والتراجع عن جميع التعديلات في الداتا بيز' ,
            ] , 200);
        }catch (\Throwable $e) {
            Log::error('Interview cleanup transaction failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'title' => 'حدث خطا غير متوقع اثناء عملية حذف المعطيات من جداول الداتابيز !' ,
                'body' => 'تم حذف الملف، لكن فشلت عملية التراجع عن تغييرات الداتابيس',
            ], 500);
        }
    }
}
