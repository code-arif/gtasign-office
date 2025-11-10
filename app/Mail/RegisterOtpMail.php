<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegisterOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp , $user;

    public function __construct($otp , $user)
    {
       $this->otp = $otp;
       $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Your OTP for Email Verification')
                    ->view('mail.otpmail')
                    ->with(['otp' => $this->otp , 'user' => $this->user]);
    }
}
