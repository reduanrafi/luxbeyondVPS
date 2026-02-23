<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductRequestStatusUpdated extends Notification
{
    use Queueable;

    public $productRequest;
    public $statusName;

    /**
     * Create a new notification instance.
     */
    public function __construct($productRequest, $statusName)
    {
        $this->productRequest = $productRequest;
        $this->statusName = $statusName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', config('app.url'));
        $url = $frontendUrl . '/dashboard/requests/' . $this->productRequest->id;

        return (new MailMessage)
            ->subject('Product Request Status Updated')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your product request status has been updated to: **' . strtoupper(str_replace('_', ' ', $this->statusName)) . '**.')
            ->line('Request Details:')
            ->line('Product: ' . $this->productRequest->product_name)
            ->line('Quantity: ' . $this->productRequest->quantity)
            ->action('View Request Details', $url)
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
            'request_id' => $this->productRequest->id,
            'title' => 'Request Status Updated',
            'message' => 'Your product request #' . $this->productRequest->id . ' status has been updated to ' . str_replace('_', ' ', $this->statusName),
            'url' => '/dashboard/requests/' . $this->productRequest->id,
            'type' => 'product_request_status'
        ];
    }
}
