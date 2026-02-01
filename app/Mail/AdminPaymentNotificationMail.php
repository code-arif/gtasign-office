<?php

namespace App\Mail;

use App\Models\User;
use App\Models\CampPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Modules\Director\Models\Camp;
use Illuminate\Queue\SerializesModels;

class AdminPaymentNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $camp;
    public $payment;
    public $admin;
    public $subject;

    public function __construct(User $user, Camp $camp, CampPayment $payment, $admin = null, $subject = null)
    {
        $this->user = $user;
        $this->camp = $camp;
        $this->payment = $payment;
        $this->admin = $admin;
        $this->subject = $subject ?? 'New Payment Received - Whistle Works';
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.admin.payment-notification')
            ->with([
                'user' => $this->user,
                'camp' => $this->camp,
                'payment' => $this->payment,
                'admin' => $this->admin,
            ]);
    }
}
