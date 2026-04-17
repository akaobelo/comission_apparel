<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StoreCreated extends Notification
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
            'title' => 'New Store Pending',
            'message' => "A new store '{$this->store->name}' requires approval.",
            'icon' => 'shopping-cart',
            'url' => route('admin.dashboard')
        ];
    }
}
