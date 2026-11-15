<?php

namespace App\Services\Chat;

use App\Events\ChatConversationUpdated;
use App\Events\ChatMessageSent;
use App\Events\VisitorMessageReceived;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\ConversationAssigned;
use App\Services\Notifications\Notifier;
use App\Support\Broadcasting;
use Illuminate\Support\Str;

class ChatService
{
    public function __construct(private readonly Notifier $notifier) {}

    /**
     * Find the visitor's current conversation, if any.
     */
    public function conversationForVisitor(string $visitorToken): ?ChatConversation
    {
        return ChatConversation::query()
            ->where('visitor_token', $visitorToken)
            ->where('status', '!=', ChatConversation::STATUS_CLOSED)
            ->latest('last_message_at')
            ->first();
    }

    /**
     * Start (or resume) a conversation for an anonymous visitor.
     */
    public function start(string $visitorToken, string $name, ?string $email, ?string $ip): ChatConversation
    {
        $existing = $this->conversationForVisitor($visitorToken);

        if ($existing !== null) {
            $existing->update([
                'visitor_name' => $name,
                'visitor_email' => $email ?: $existing->visitor_email,
                'visitor_last_seen_at' => now(),
            ]);

            return $existing;
        }

        return ChatConversation::create([
            'token' => (string) Str::uuid(),
            'visitor_name' => $name,
            'visitor_email' => $email ?: null,
            'visitor_token' => $visitorToken,
            'status' => ChatConversation::STATUS_OPEN,
            'visitor_ip' => $ip,
            'visitor_last_seen_at' => now(),
            'last_message_at' => now(),
        ]);
    }

    public function addVisitorMessage(ChatConversation $conversation, string $body): ChatMessage
    {
        $message = $this->storeMessage($conversation, ChatMessage::SENDER_VISITOR, null, $body);

        $conversation->update([
            'unread_for_agents' => $conversation->unread_for_agents + 1,
            'visitor_last_seen_at' => now(),
        ]);

        if ($conversation->status === ChatConversation::STATUS_CLOSED) {
            $conversation->update(['status' => ChatConversation::STATUS_OPEN, 'closed_at' => null]);
        }

        event(new VisitorMessageReceived($conversation, $message));

        return $message;
    }

    public function addAgentMessage(ChatConversation $conversation, User $agent, string $body): ChatMessage
    {
        $message = $this->storeMessage($conversation, ChatMessage::SENDER_AGENT, $agent->id, $body);

        $conversation->update([
            'unread_for_agents' => 0,
            'unread_for_visitor' => $conversation->unread_for_visitor + 1,
        ]);

        if ($conversation->assigned_to === null) {
            $conversation->update([
                'assigned_to' => $agent->id,
                'status' => ChatConversation::STATUS_ASSIGNED,
            ]);
        }

        return $message;
    }

    public function markReadForAgent(ChatConversation $conversation): void
    {
        $conversation->update(['unread_for_agents' => 0]);
        $conversation->messages()->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function markReadForVisitor(ChatConversation $conversation): void
    {
        $conversation->update(['unread_for_visitor' => 0]);
    }

    public function assign(ChatConversation $conversation, ?User $agent): ChatConversation
    {
        $conversation->update([
            'assigned_to' => $agent?->id,
            'status' => $agent === null ? ChatConversation::STATUS_OPEN : ChatConversation::STATUS_ASSIGNED,
        ]);

        $conversation->refresh()->load('agent');
        Broadcasting::safely(new ChatConversationUpdated($conversation));

        if ($agent !== null) {
            $this->notifier->toUser($agent, new ConversationAssigned($conversation));
        }

        return $conversation;
    }

    public function setStatus(ChatConversation $conversation, string $status): ChatConversation
    {
        $conversation->update([
            'status' => $status,
            'closed_at' => $status === ChatConversation::STATUS_CLOSED ? now() : null,
        ]);

        $conversation->refresh()->load('agent');
        Broadcasting::safely(new ChatConversationUpdated($conversation));

        return $conversation;
    }

    private function storeMessage(ChatConversation $conversation, string $senderType, ?int $userId, string $body): ChatMessage
    {
        $message = $conversation->messages()->create([
            'sender_type' => $senderType,
            'user_id' => $userId,
            'body' => $body,
        ]);

        $conversation->update(['last_message_at' => $message->created_at]);

        $message->load('user');

        Broadcasting::safely(new ChatMessageSent($message));

        return $message;
    }
}
