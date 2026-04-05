<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

class RegistrationOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $otp;
    public User $user;
    public string $header_message;

    public function __construct(int $otp, User $user, string $message)
    {
        $this->otp = $otp;
        $this->user = $user;
        $this->header_message = $message;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->header_message,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-register.register-otp',
            text: 'emails.user-register.register-otp-text',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
