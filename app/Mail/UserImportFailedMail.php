<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserImportFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $errors;
    public ?array $successful;

    public string $purpose;
    /**
     * Create a new message instance.
     */
    public function __construct(array $errors , ?array $successful , string $purpose)
    {
        $this->errors = $errors;
        $this->successful = $successful;
        $this->purpose = $purpose;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if($this->purpose === 'doctor') {
            return new Envelope(
                subject: 'فشل جزئي في استيراد بيانات الدكاترة',
            );
        }
        else{
            return new Envelope(
                subject: 'فشل جزئي في استيراد بيانات الطلاب',
            );
        }

    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if($this->purpose === 'doctor') {
            return new Content(
                html: 'emails.doctors.failedImportExcel',
            );
        }
        else {
            return new Content(
                html: 'emails.students.failedImportExcel',
            );
        }
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
