<?php

namespace Database\Factories;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_conversation_id' => ChatConversation::factory(),
            'sender_type' => ChatMessage::SENDER_VISITOR,
            'user_id' => null,
            'body' => fake()->sentence(),
            'read_at' => null,
        ];
    }

    public function fromAgent(User $user): static
    {
        return $this->state(fn (): array => [
            'sender_type' => ChatMessage::SENDER_AGENT,
            'user_id' => $user->id,
        ]);
    }
}
