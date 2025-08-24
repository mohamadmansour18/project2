<?php

namespace App\Services\DashBoard_Services;

use App\Exceptions\ProjectManagementException;
use App\Helpers\UrlHelper;
use App\Repositories\GroupGradeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GradeManagementService
{
    public function __construct(
        protected GroupGradeRepository $groupGradeRepository,
    )
    {}

    public function getGradesForLastThreeYears(): array
    {
        $gradesByYear = $this->groupGradeRepository->getGradesForLastThreeYears();

        $years = [now()->year, now()->year - 1, now()->year - 2];

        $response = [];

        foreach ($years as $year) {
            $response[$year] = ($gradesByYear[$year] ?? collect())
                ->map(function ($grade) {
                    $group     = $grade->group;                 // BelongsTo (مفرد)
                    $committee = $grade->committee;             // BelongsTo (مفرد)

                    $projectForm = $group?->projectForm;        // HasOne (مفرد)
                    $interview   = $group?->interviewSchedule;  // HasOne (مفرد)

                    return [
                        'id'            => $grade->id,
                        'group_name'    => $group?->name ?? '',
                        'group_id'      => $group?->id ?? '',
                        'idea_name'     => $projectForm?->arabic_title ?? '',
                        'committee'     => [
                            [
                                'name'  => $committee?->adminSupervisor?->name ?? '',
                                'image' => UrlHelper::imageUrl($committee?->adminSupervisor?->profile?->profile_image),
                            ],
                            [
                                'name'  => $committee?->adminMember?->name ?? '',
                                'image' => UrlHelper::imageUrl($committee?->adminMember?->profile?->profile_image),
                            ],
                        ],
                        'grade'          => $grade->total_grade !== null ? "100/{$grade->total_grade}" : 'لايوجد',
                        'is_edited'      => $grade->is_edited ? 'تم التعديل' : 'لم يتم التعديل',
                        'previous_grade' => $grade->is_edited ? $grade->previous_total_grade : null,
                        'interview_date' => $interview?->interview_date ?? null,
                    ];
                })
                ->values()
                ->all();
        }

        return $response;
    }

    public function generateAndDownloadGrade(): BinaryFileResponse|JsonResponse
    {
        $year = now()->year;

        //fetch data
        $grades = $this->groupGradeRepository->getGradesWithRelationsForYear();

        //verify data is not empty
        if($grades->isEmpty())
        {
            return response()->json([
                'title' => 'لا يمكن إنشاء الملف !',
                'body' => 'لاتوجد علمات لطلاب السنة الرابعة للسنة الحالية',
                'statusCode' => 404
            ], 404);
        }

        //building display data + Statistics
        $groupsData = [];
        $groupIndex = 0;
        $passed = 0;
        $failed = 0;

        foreach ($grades as $grade)
        {
            $group = $grade->group;
            if(!$group)
            {
                continue;
            }

            $groupIndex++;
            $groupName = $group->name ?? ('مجموعة #' . $group->id);
            $groupGrade  = (int) ($grade->total_grade ?? 0);

            $exceptedUserIds = $grade->GradeExceptions?->pluck('student_id')->all() ?? [];
            $membersRows = [];
            foreach ($group->members as $member)
            {
                $user = $member->user;

                $userId = $user?->id;
                $userName      = $user?->name ?? '-';
                $universityNumber    = $user?->university_number ?? '-';
                $isExcepted    = $userId ? in_array($userId, $exceptedUserIds, true) : false;
                $studentGrade  = $isExcepted ? 0 : $groupGrade;

                if($studentGrade >= 60)
                {
                    $passed++;
                } else {
                    $failed++;
                }

                $membersRows[] = [
                    'name'         => $userName,
                    'exam_number'  => $universityNumber,
                    'grade'        => $studentGrade,
                    'is_excepted'  => $isExcepted,
                ];
            }

            $groupsData[] = [
                'index'   => $groupIndex,
                'name'    => $groupName,
                'grade'    => $groupGrade,
                'members' => $membersRows,
            ];
        }

         if(empty($groupsData))
         {
             return response()->json([
                 'title' => 'لا يمكن إنشاء الملف !',
                 'body' => 'لاتوجد بيانات صالحة ليتم توليدها البيانات تواجه خللا ما',
                 'statusCode' => 422
             ], 422);
         }

        $totalStudents = $passed + $failed ;
        $successRate   = $totalStudents > 0 ? round(($passed / $totalStudents) * 100, 2) : 0.0;

        try {
            //fetch logo
            $logoPath = storage_path('app/public/application_logo/logo.jpg');
            $logoImg  = file_exists($logoPath) ? 'file://' . $logoPath : '';

            $html = view('pdfs.gradeReport' , [
                'year'         => $year,
                'groups'       => $groupsData,
                'passed'       => $passed,
                'failed'       => $failed,
                'successRate'  => $successRate,
                'logoImg'      => $logoImg,
            ])->render();


            //mPDT temp folder
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
            $filename = "grade_{$year}_" . now()->format('YmdHis') . ".pdf";
            $relativePath = $dir . '/' . $filename;
            $absolutePath = $disk->path($relativePath);

            //generate and save file in project
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
            throw new ProjectManagementException('حدث خطأ اثناء التنفيذ !', 'حدث خطا غير متوقع يرجى اعادة المحاولة لاحقا', 500);
        }
    }
}
