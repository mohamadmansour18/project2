<?php

namespace App\Jobs;

use App\Mail\SendOtpMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOtpCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2 ;
    public $timeout = 10 ;
    public string $email ;
    public string $name ;
    public string $otp ;
    /**
     * Create a new job instance.
     */
    public function __construct(string $email , string $otp , string $name)
    {
        $this->email = $email ;
        $this->otp = $otp ;
        $this->name = $name ;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new SendOtpMail($this->otp , $this->name));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Send OTP email job failed for user : ' . $this->email . 'has name : ' . $this->name);
        Log::error($exception->getMessage());
    }
}
