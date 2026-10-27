<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderSubmittedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public array $orderData;
    public string $recipientType; // 'admin' or 'sales_rep'

    /**
     * Create a new message instance.
     */
    public function __construct(array $orderData, string $recipientType = 'admin')
    {
        $this->orderData = $orderData;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $org = $this->orderData['organization'] ?? 'Customer';
        $typeLabel = $this->orderData['type_label'] ?? 'New Order';
        $amount = isset($this->orderData['total_revenue']) 
            ? '$' . number_format((float) $this->orderData['total_revenue'], 2)
            : '';

        $subject = "[The Commission Apparel] {$typeLabel}: {$org}" . ($amount ? " - {$amount}" : "");
        if ($this->recipientType === 'sales_rep') {
            $subject = "🔔 New Order from Your Client: {$org}" . ($amount ? " ({$amount})" : "");
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
            markdown: 'emails.orders.submitted',
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
