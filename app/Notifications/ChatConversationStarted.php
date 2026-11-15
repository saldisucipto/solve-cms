<?php

namespace App\Notifications;

use App\Models\ChatConversation;
use Illuminate\Notifications\Notification;

class ChatConversationStarted extends Notification
{
    public function __construct(public ChatConversation $conversation) {}

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
            'type' => 'chat_started',
            'title' => 'New chat conversation',
            'body' => $this->conversation->visitor_name.' started a conversation.',
            'conversation_id' => $this->conversation->id,
            'url' => '/admin/chat?conversation='.$this->conversation->id,
        ];
    }
}
