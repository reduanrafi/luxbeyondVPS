<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewProductRequestNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $productRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct($productRequest)
    {
        $this->productRequest = $productRequest;
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
        $frontendUrl = config('app.frontend_url', config('app.url'));
        // Fallback or specific admin URL
        $url = $frontendUrl . '/admin/product-requests/' . $this->productRequest->id;

        return (new MailMessage)
            ->subject('New Product Request Received')
            ->greeting('Hello Admin,')
            ->line('A new product request has been submitted.')
            ->line('Product URL: ' . $this->productRequest->url)
            ->line('Quantity: ' . $this->productRequest->quantity)
            ->line('Requested Price: ' . $this->productRequest->currency . ' ' . $this->productRequest->price)
            ->action('View Request', $url)
            ->line('Please review this request.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->productRequest->id,
            'title' => 'New Product Request',
            'message' => 'New request from User #' . $this->productRequest->user_id,
            'url' => '/admin/product-requests/' . $this->productRequest->id,
            'type' => 'product_request'
        ];
    }
}
