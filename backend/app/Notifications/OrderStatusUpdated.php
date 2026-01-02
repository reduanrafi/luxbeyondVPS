<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    public $order;
    public $status;

    public function __construct(Order $order, $status)
    {
        $this->order = $order;
        $this->status = $status;
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
            'title' => 'Order Status Updated',
            'message' => "Your order #{$this->order->order_number} status has been updated to {$this->status}.",
            'url' => "/dashboard/orders/{$this->order->order_number}",
            'type' => 'order_status_update',
            'status' => $this->status
        ];
    }
}
