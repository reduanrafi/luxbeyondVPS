<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'title' => 'New Order Received',
            'message' => "New order #{$this->order->order_number} received from {$this->order->user->name}.",
            'url' => "/admin/orders/{$this->order->id}",
            'type' => 'admin_new_order',
            'amount' => $this->order->total
        ];
    }
}
