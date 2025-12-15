<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravellerStatusUpdated extends Notification
{
    use Queueable;

    public $status;

    /**
     * Create a new notification instance.
     */
    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Add 'mail' if mail is configured
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->status === 'approved' 
            ? 'Traveller Application Approved' 
            : 'Traveller Application Rejected';
            
        return [
            'status' => $this->status,
            'title' => $title,
            'message' => "Your traveller application has been {$this->status}.",
            'url' => '/traveller-dashboard',
            'type' => 'traveller_status'
        ];
    }
}
