<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Director\Models\Camp;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentSessionExpiredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $camp;
    public $sessionId;
    public $subject;

    public function __construct(User $user, Camp $camp, $sessionId = null, $subject = null)
    {
        $this->user = $user;
        $this->camp = $camp;
        $this->sessionId = $sessionId;
        $this->subject = $subject ?? 'Payment Session Expired - Whistle Works';
    }

    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.payment.session-expired')
                    ->with([
                        'user' => $this->user,
                        'camp' => $this->camp,
                        'sessionId' => $this->sessionId,
                    ]);
    }
}
