<?php

namespace App\Listeners;

use App\Events\VisitorMessageReceived;
use App\Models\ChatMessage;
use App\Notifications\ChatConversationStarted;
use App\Notifications\ChatMessageReceived;
use App\Services\Notifications\Notifier;

class NotifyAgentsOfChatMessage
{
    public function __construct(private readonly Notifier $notifier) {}

    public function handle(VisitorMessageReceived $event): void
    {
        $conversation = $event->conversation;

        if ($event->conversation->messages()->where('sender_type', ChatMessage::SENDER_VISITOR)->count() === 1) {
            $this->notifier->toPermission('chat.view', new ChatConversationStarted($conversation));
        }

        $this->notifier->toPermission(
            'chat.view',
            new ChatMessageReceived($conversation, $event->message),
        );
    }
}
