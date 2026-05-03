<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MasterOrderSubmitted extends Notification
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
            'title' => 'Master Order Submitted',
            'message' => "Store '{$this->store->name}' has submitted their master order for production.",
            'icon' => 'check-circle',
            'url' => route('admin.dashboard')
        ];
    }
}
