<?php

namespace App\Notifications;

use App\Models\ChatConversation;
use Illuminate\Notifications\Notification;

class ConversationAssigned extends Notification
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
            'type' => 'conversation_assigned',
            'title' => 'Conversation assigned to you',
            'body' => $this->conversation->visitor_name."'s conversation was assigned to you.",
            'conversation_id' => $this->conversation->id,
            'url' => '/admin/chat?conversation='.$this->conversation->id,
        ];
    }
}
