<?php

namespace App\Notifications;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Notifications\Notification;

class ChatMessageReceived extends Notification
{
    public function __construct(
        public ChatConversation $conversation,
        public ChatMessage $message,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'chat_message',
            'title' => 'New chat message',
            'body' => str($this->message->body)->limit(80)->value(),
            'conversation_id' => $this->conversation->id,
            'url' => '/admin/chat?conversation='.$this->conversation->id,
        ];
    }
}
