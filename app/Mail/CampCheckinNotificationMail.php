<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Director\Models\Camp;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Director\Models\CampRefereeCheckin;

class CampCheckinNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $director;
    public $referee;
    public $camp;
    public $registration;
    public $subject;

    public function __construct(User $director, User $referee, Camp $camp, CampRefereeCheckin $registration, $subject = null)
    {
        $this->director = $director;
        $this->referee = $referee;
        $this->camp = $camp;
        $this->registration = $registration;
        $this->subject = $subject ?? 'New Check-in: ' . $referee->first_name . ' has arrived - ' . $camp->camp_name;
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.camp.checkin-notification')
            ->with([
                'director' => $this->director,
                'referee' => $this->referee,
                'camp' => $this->camp,
                'registration' => $this->registration,
            ]);
    }
}
