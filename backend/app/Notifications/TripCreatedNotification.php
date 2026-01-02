<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TripCreatedNotification extends Notification
{
    use Queueable;

    public $trip;
    public $travellerName;

    public function __construct($trip, $travellerName)
    {
        $this->trip = $trip;
        $this->travellerName = $travellerName;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'trip_id' => $this->trip->id,
            'title' => 'New Trip Created',
            'message' => "{$this->travellerName} has created a new trip.",
            // Adjust url based on actual admin route for trips
            'url' => "/admin/trips/{$this->trip->id}", 
            'type' => 'admin_new_trip'
        ];
    }
}
