<?php

namespace App\Mail;

use App\Models\TeamStore;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StoreRequestedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public TeamStore $store;
    public string $recipientType; // 'admin' or 'sales_rep'

    /**
     * Create a new message instance.
     */
    public function __construct(TeamStore $store, string $recipientType = 'admin')
    {
        $this->store = $store;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $storeName = $this->store->name;
        $org = $this->store->user?->organization ?: 'Team Store';

        if ($this->recipientType === 'sales_rep') {
            $subject = "🛒 New Team Store Request from Your Client: {$storeName}";
        } else {
            $subject = "[The Commission Apparel] New Team Store Request: {$storeName} ({$org})";
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
            markdown: 'emails.stores.requested',
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
