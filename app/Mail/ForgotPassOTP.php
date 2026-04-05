<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class ForgotPassOTP extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $user;
    public $mailSubject;

    public function __construct($otp, $user, $subject = null)
    {
        $this->otp = $otp;
        $this->user = $user;
        $this->mailSubject = $subject ?? 'Password Reset OTP - SecaaX';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.forgot-pass.forgot-password-otp',
            text: 'emails.forgot-pass.forgot-password-otp-text',
            with: [
                'otp' => $this->otp,
                'user' => $this->user,
            ]
        );
    }
}
