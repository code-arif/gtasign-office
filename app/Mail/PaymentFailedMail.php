<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Director\Models\Camp;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentFailedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $camp;
    public $error;
    public $subject;

    public function __construct(User $user, Camp $camp, $error = null, $subject = null)
    {
        $this->user = $user;
        $this->camp = $camp;
        $this->error = $error;
        $this->subject = $subject ?? 'Payment Failed - Whistle Works';
    }

    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.payment.failed')
                    ->with([
                        'user' => $this->user,
                        'camp' => $this->camp,
                        'error' => $this->error,
                    ]);
    }
}
