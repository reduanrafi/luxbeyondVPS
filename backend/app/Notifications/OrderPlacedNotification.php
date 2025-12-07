<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = config('app.frontend_url') . '/dashboard/orders/' . $this->order->order_number;

        return (new MailMessage)
            ->subject('Order Confirmation - #' . ($this->order->order_number ?? $this->order->id))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you for your order! We have received your order and it is now being processed.')
            ->line('Order Summary:')
            ->line('Order Number: #' . ($this->order->order_number ?? $this->order->id))
            ->line('Total Amount: ' . $this->order->currency . ' ' . number_format($this->order->total, 2))
            ->action('View Order Details', $url)
            ->line('We will notify you once your order has been shipped.')
            ->line('Thank you for shopping with us!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order Placed Successfully',
            'message' => 'Your order #' . ($this->order->order_number ?? $this->order->id) . ' has been placed successfully.',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->order->total,
            'type' => 'order_created'
        ];
    }
}
