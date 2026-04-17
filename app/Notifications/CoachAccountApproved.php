<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoachAccountApproved extends Notification
{
    use Queueable;

    public function __construct() {}

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Your Commission Apparel Account is Approved!')
                    ->greeting("Hello {$notifiable->name},")
                    ->line('Your coach application has been approved by our team.')
                    ->line('You can now log in to your dashboard and start creating custom team stores.')
                    ->action('Go To Dashboard', url('/coach/dashboard'))
                    ->line('Thank you for choosing The Commission Apparel!');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Account Approved',
            'message' => 'Your coach account has been approved. You can now create stores!',
            'icon' => 'shield-check',
            'url' => route('coach.dashboard')
        ];
    }
}
