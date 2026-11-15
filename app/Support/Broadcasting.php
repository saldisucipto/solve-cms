<?php

namespace App\Support;

use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches broadcastable events without letting a missing or unreachable
 * real-time server break the request. Live delivery is best-effort; the
 * durable state (database rows) is written before broadcasting.
 */
class Broadcasting
{
    public static function safely(object $event): void
    {
        try {
            event($event);
        } catch (BroadcastException $exception) {
            Log::warning('Broadcast failed; real-time delivery skipped.', [
                'event' => $event::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
