<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CoachRegistered extends Notification
{
    use Queueable;
    public $coach;

    public function __construct($coach)
    {
        $this->coach = $coach;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'New Coach Registration',
            'message' => "{$this->coach->name} has registered and awaits approval.",
            'icon' => 'user-plus',
            'url' => route('admin.dashboard')
        ];
    }
}
