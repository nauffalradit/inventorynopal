<?php

namespace App\Jobs;

use App\Models\NotificationMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendInventoryNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public NotificationMessage $notification)
    {
        //
    }

    public function handle(): void
    {
        Log::info('Inventory notification sent', [
            'channel' => $this->notification->channel,
            'recipient' => $this->notification->recipient,
            'subject' => $this->notification->subject,
        ]);

        $this->notification->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        Cache::forget('dashboard:recentNotifications');
        for ($i = 1; $i <= 20; $i++) {
            Cache::forget("notifications:index:page:$i");
        }
    }

    public function failed(): void
    {
        $this->notification->update(['status' => 'failed']);
        Cache::forget('dashboard:recentNotifications');
        for ($i = 1; $i <= 20; $i++) {
            Cache::forget("notifications:index:page:$i");
        }
    }
}
