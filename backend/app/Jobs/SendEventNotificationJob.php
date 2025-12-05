<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEventNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $event;
    protected $type; // 'created' or 'started'

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event, string $type = 'created')
    {
        $this->event = $event;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get all users
            $users = User::whereNotNull('email')->get();

            foreach ($users as $user) {
                try {
                    Mail::send('emails.event-notification', [
                        'event' => $this->event,
                        'user' => $user,
                        'type' => $this->type,
                    ], function ($message) use ($user) {
                        $message->to($user->email, $user->name)
                            ->subject($this->type === 'started' 
                                ? "🎉 {$this->event->name} Has Started!" 
                                : "📢 New Event: {$this->event->name}");
                    });
                } catch (\Exception $e) {
                    Log::error("Failed to send event notification to {$user->email}: " . $e->getMessage());
                }
            }

            Log::info("Event notification sent for event: {$this->event->name} (Type: {$this->type})");
        } catch (\Exception $e) {
            Log::error("Error sending event notifications: " . $e->getMessage());
        }
    }
}

