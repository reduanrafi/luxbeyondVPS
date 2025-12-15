<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;

class NewTravellerApplication extends Notification
{
    use Queueable;

    public $applicant;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $applicant)
    {
        $this->applicant = $applicant;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'applicant_id' => $this->applicant->id,
            'title' => 'New Traveller Application',
            'message' => "{$this->applicant->name} has applied to be a traveller.",
            'url' => "/admin/travellers/{$this->applicant->id}",
            'type' => 'admin_traveller_application'
        ];
    }
}
