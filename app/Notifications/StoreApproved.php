<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StoreApproved extends Notification
{
    use Queueable;
    public $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Store Approved!',
            'message' => "Your store '{$this->store->name}' is now live.",
            'icon' => 'check-circle',
            'url' => route('coach.dashboard')
        ];
    }
}
