<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductRequestSubmittedNotification extends Notification
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
        $url = $frontendUrl . '/dashboard/requests/' . $this->productRequest->id;

        return (new MailMessage)
            ->subject('Product Request Submitted')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We have received your product request. Our admin team will review the details.')
            ->line('**Request Details:**')
            ->line('Product URL: ' . $this->productRequest->url)
            ->line('Quantity: ' . $this->productRequest->quantity)
            ->line('Declared Price: ' . $this->productRequest->currency . ' ' . number_format($this->productRequest->price, 2))
            ->line('Estimated Total: ৳' . number_format($this->productRequest->total_amount_bdt, 2))
            ->action('View Request Status', $url)
            ->line('We will notify you once the request is approved.');
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
            'title' => 'Product Request Submitted',
            'message' => 'Your product request #' . $this->productRequest->id . ' has been submitted successfully.',
            'url' => '/dashboard/requests/' . $this->productRequest->id,
            'type' => 'product_request'
        ];
    }
}
