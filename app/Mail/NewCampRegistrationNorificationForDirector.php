<?php

namespace App\Mail;

use App\Models\User;
use App\Models\CampPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Director\Models\Camp;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCampRegistrationNorificationForDirector extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $referee;
    public $camp;
    public $payment;

    public function __construct(User $referee, Camp $camp, CampPayment $payment)
    {
        $this->referee = $referee;
        $this->camp = $camp;
        $this->payment = $payment;
    }

    public function build()
    {
        return $this->subject('New Referee Registered for Your Camp')
            ->view('emails.director.camp-registration-notification')
            ->with([
                'referee' => $this->referee,
                'camp' => $this->camp,
                'payment' => $this->payment,
            ]);
    }
}
