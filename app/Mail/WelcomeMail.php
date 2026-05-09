<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @param mixed $user
     * @param string|null $subject
     */
    public function __construct($user, $subject = null)
    {
        $this->user = $user;
        $this->subject = $subject ?? 'Welcome to SECaaX! Your Account is Verified';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.user-register.welcome')
                    ->with([
                        'user' => $this->user,
                    ]);
    }
}
