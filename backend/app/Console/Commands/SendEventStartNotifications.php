<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Jobs\SendEventNotificationJob;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendEventStartNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:send-start-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for events that start today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        // Find events that start today and have notifications enabled
        $events = Event::where('send_notification', true)
            ->where('is_active', true)
            ->whereDate('start_date', $today)
            ->get();

        if ($events->isEmpty()) {
            $this->info('No events starting today with notifications enabled.');
            return;
        }

        foreach ($events as $event) {
            $this->info("Dispatching notification for event: {$event->name}");
            dispatch(new SendEventNotificationJob($event, 'started'));
        }

        $this->info("Notifications dispatched for {$events->count()} event(s).");
    }
}

