<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatMessage $message) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $conversation = $this->message->conversation;

        return [
            new PrivateChannel('chat.visitor.'.$conversation->token),
            new PrivateChannel('chat.inbox'),
            new PrivateChannel('chat.conversation.'.$conversation->id),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $author = $this->message->user?->name;

        return [
            'conversation_id' => $this->message->chat_conversation_id,
            'conversation_token' => $this->message->conversation->token,
            'message' => [
                'id' => $this->message->id,
                'sender_type' => $this->message->sender_type,
                'body' => $this->message->body,
                'author' => $author ?? $this->message->conversation->visitor_name,
                'created_at' => $this->message->created_at?->toIso8601String(),
                'created_human' => $this->message->created_at?->format('H:i'),
            ],
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message';
    }
}
