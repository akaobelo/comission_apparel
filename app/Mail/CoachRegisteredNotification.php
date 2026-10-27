<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoachRegisteredNotification extends Mailable
{
    use Queueable, SerializesModels;

    public User $coach;
    public string $recipientType; // 'admin' or 'sales_rep'

    /**
     * Create a new message instance.
     */
    public function __construct(User $coach, string $recipientType = 'admin')
    {
        $this->coach = $coach;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $name = trim($this->coach->first_name . ' ' . $this->coach->last_name) ?: 'New Coach';
        $org = $this->coach->organization ?: 'New Organization';

        if ($this->recipientType === 'sales_rep') {
            $subject = "🎉 New Coach Joined Under Your Partner Link: {$name} ({$org})";
        } else {
            $subject = "[The Commission Apparel] New Coach Registration: {$name} - {$org}";
        }

        return new Envelope(
            subject: $subject,
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($this->coach->email, $name),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.coaches.registered',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
