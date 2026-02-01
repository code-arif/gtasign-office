<?php

namespace App\Mail;


use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Director\Models\Camp;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Director\Models\CampRefereeCheckin;

class RegistrationConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $camp;
    public $registration;
    public $subject;

    public function __construct(User $user, Camp $camp, CampRefereeCheckin $registration, $subject = null)
    {
        $this->user = $user;
        $this->camp = $camp;
        $this->registration = $registration;
        $this->subject = $subject ?? 'Registration Confirmed - Whistle Works';
    }

    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.registration.confirmation')
                    ->with([
                        'user' => $this->user,
                        'camp' => $this->camp,
                        'registration' => $this->registration,
                    ]);
    }
}
