<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPassOTP extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $otp;
    public $user;
    public $subject;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $user, $subject = null)
    {
        $this->otp = $otp;
        $this->user = $user;
        $this->subject = $subject ?? 'Password Reset OTP - Whistle Works';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.forgot-pass.forgot-password-otp',
            with: [
                'otp' => $this->otp,
                'user' => $this->user,
            ]
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
