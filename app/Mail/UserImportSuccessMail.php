<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserImportSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $count ;
    public string $purpose;
    /**
     * Create a new message instance.
     */
    public function __construct(int $count , string $purpose)
    {
        $this->count = $count;
        $this->purpose = $purpose;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if($this->purpose === 'doctor')
        {
            return new Envelope(
                subject: 'تم استيراد بيانات الدكاترة بنجاح',
            );
        }
        else{
            return new Envelope(
                subject: 'تم استيراد بيانات الطلاب بنجاح',
            );
        }

    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if($this->purpose === 'doctor')
        {
            return new Content(
                html: 'emails.doctors.successImportExcel',
                with: ['count' => $this->count],
            );
        }
        else {
            return new Content(
                html: 'emails.students.successImportExcel',
                with: ['count' => $this->count],
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
