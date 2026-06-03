<?php

namespace App\Jobs;

use App\Models\NotificationMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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
    }

    public function failed(): void
    {
        $this->notification->update(['status' => 'failed']);
    }
}
