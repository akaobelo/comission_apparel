<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ParentOrderPlaced extends Notification
{
    use Queueable;
    public $athleteName;
    public $storeName;

    public function __construct($athleteName, $storeName)
    {
        $this->athleteName = $athleteName;
        $this->storeName = $storeName;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'New Order Received',
            'message' => "{$this->athleteName} placed an order in '{$this->storeName}'.",
            'icon' => 'box',
            'url' => route('coach.dashboard')
        ];
    }
}
