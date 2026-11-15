<?php

namespace App\Events;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Domain event fired when a visitor sends a message. Kept separate from the
 * broadcast event so notification delivery never depends on a live server.
 */
class VisitorMessageReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ChatConversation $conversation,
        public ChatMessage $message,
    ) {}
}
