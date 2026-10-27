<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLeadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public QuoteRequest $quoteRequest;

    /**
     * Create a new message instance.
     */
    public function __construct(QuoteRequest $quoteRequest)
    {
        $this->quoteRequest = $quoteRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $org = $this->quoteRequest->organization_name ?: 'New Organization';
        $name = trim($this->quoteRequest->first_name . ' ' . $this->quoteRequest->last_name) ?: 'New Lead';
        $isMockup = ($this->quoteRequest->package_type === 'free_3d_mockup' || $this->quoteRequest->lead_source === 'landing_page_free_mockup');
        $label = $isMockup ? 'New Free Mockup Inquiry' : 'New Quote Inquiry';

        return new Envelope(
            subject: "[The Commission Apparel] {$label}: {$org} - {$name}",
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($this->quoteRequest->email, $name),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leads.new_lead',
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
