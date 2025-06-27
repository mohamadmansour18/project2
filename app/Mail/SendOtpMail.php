<?php

namespace App\Mail;

use App\Enums\OtpCodePurpose;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp ;
    public string $name ;
    public string $purpose ;
    /**
     * Create a new message instance.
     */
    public function __construct(string $otp , string $name , string $purpose)
    {
        $this->otp = $otp ;
        $this->name = $name ;
        $this->purpose = $purpose ;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if($this->purpose == OtpCodePurpose::Verification->value)
        {
            return new Envelope(
                subject: 'رمز التحقق الخاص بك لتأكيد بريدك الالكتروني !',
            );
        }
        else{
            return new Envelope(
                subject: 'رمز التحقق الخاص بك لاعادة تعين كلمة المرور !',
            );
        }

    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.otp',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
