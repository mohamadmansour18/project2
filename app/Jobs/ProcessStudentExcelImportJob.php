<?php

namespace App\Jobs;

use App\Imports\StudentImport;
use App\Mail\UserImportFailedMail;
use App\Mail\UserImportSuccessMail;
use App\Services\DashBoard_Services\UserManagementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessStudentExcelImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected string $AdminEmail;

    public function __construct(string $filePath, string $AdminEmail)
    {
        $this->filePath = $filePath;
        $this->AdminEmail = $AdminEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(UserManagementService $userManagementService): void
    {
        try {
            //import file from excel
            $import = new StudentImport();
            //storage_path return : storage/app/public/temp_excel/filename.Extension
            Excel::import($import, storage_path('app/public/' . $this->filePath));

            $validRows = $import->validRows;
            $importErrors = $import->errors;

            //move correct data from excel to database
            $result = $userManagementService->importStudentsFromExcel($validRows);
            Log::info(count($result['inserted']));
            //merge all errors array in one array like
            $allErrors = array_merge($importErrors, $result['failed']);

            if (!empty($allErrors)) {
                foreach ($allErrors as $error) {
                    Log::error('[ImportStudentJob] ' . $error);
                }
                Mail::to($this->AdminEmail)->queue(new UserImportFailedMail($allErrors , null , 'student'));
            } else {
                Mail::to($this->AdminEmail)->queue(new UserImportSuccessMail(count($result['inserted']) , 'student'));
            }

            //delete temp file after processing
            //without disk : the search should be just in folder storage/app
            if(Storage::disk('public')->exists($this->filePath))
            {
                Storage::disk('public')->deleteDirectory('temp_excel');
            }
        }catch (\Throwable $exception){
            Log::error('[ImportStudentsJob] خطأ غير متوقع: ' . $exception->getMessage());
        }
    }
}
