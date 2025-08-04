<?php

namespace App\Jobs;

use App\Imports\DoctorImport;
use App\Mail\DoctorImportFailedMail;
use App\Mail\DoctorImportSuccessMail;
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
use Throwable;

class ProcessDoctorExcelImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath ;
    protected string $AdminEmail ;
    public function __construct(string $filePath , string $AdminEmail)
    {
        $this->filePath = $filePath ;
        $this->AdminEmail = $AdminEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(UserManagementService $userManagementService): void
    {
        try {
            //import file from excel
            $import = new DoctorImport();
            //storage_path return : storage/app/public/temp_excel/filename.Extension
            Excel::import($import , storage_path('app/public/' . $this->filePath));

            $validRows = $import->validRows;
            $importErrors = $import->errors;

            //move correct data from excel to database
            $result = $userManagementService->importDoctorsFromExcel($validRows);

            //merge all errors array in one array like
            $allErrors = array_merge($importErrors , $result['failed']);

            //register the error list in log
            if(!empty($allErrors))
            {
                foreach ($allErrors as $error)
                {
                    Log::error('[ImportDoctorsJob] ' . $error);
                }

                Mail::to($this->AdminEmail)->queue(new DoctorImportFailedMail($allErrors , $result['inserted']));
            } else {
                Mail::to($this->AdminEmail)->queue(new DoctorImportSuccessMail(count($result['inserted'])));
            }

            //delete temp file after processing
            //without disk : the search should be just in folder storage/app
            if(Storage::disk('public')->exists($this->filePath))
            {
                Storage::disk('public')->deleteDirectory('temp_excel');
            }

        }catch (Throwable $e)
        {
            Log::error('[ImportDoctorsJob] خطأ غير متوقع: ' . $e->getMessage());
        }
    }
}
