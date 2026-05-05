<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class ExpertStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $status;
    public ?string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $status, ?string $reason = null)
    {
        $this->user = $user;
        $this->status = $status;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Account Status Update - ' . config('app.name');
        
        if ($this->status === 'active') {
            $subject = 'Your Account has been Activated - ' . config('app.name');
        } elseif ($this->status === 'suspended') {
            $subject = 'Your Account has been Suspended - ' . config('app.name');
        } elseif ($this->status === 'inactive') {
            $subject = 'Your Account has been Deactivated - ' . config('app.name');
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.experts.status-update',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
