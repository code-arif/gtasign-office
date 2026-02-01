<?php

namespace App\Mail;

use App\Models\User;
use App\Models\CampPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Director\Models\Camp;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentSuccessfulMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $camp;
    public $payment;
    public $subject;

    public function __construct(User $user, Camp $camp, CampPayment $payment, $subject = null)
    {
        $this->user = $user;
        $this->camp = $camp;
        $this->payment = $payment;
        $this->subject = $subject ?? 'Payment Successful - Whistle Works';
    }

    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.payment.successful')
                    ->with([
                        'user' => $this->user,
                        'camp' => $this->camp,
                        'payment' => $this->payment,
                    ]);
    }
}
