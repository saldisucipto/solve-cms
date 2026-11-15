<?php

namespace Database\Factories;

use App\Models\ChatConversation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChatConversation>
 */
class ChatConversationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => (string) Str::uuid(),
            'visitor_name' => fake()->firstName(),
            'visitor_email' => fake()->safeEmail(),
            'visitor_token' => (string) Str::uuid(),
            'subject' => null,
            'status' => ChatConversation::STATUS_OPEN,
            'assigned_to' => null,
            'unread_for_agents' => 0,
            'unread_for_visitor' => 0,
            'visitor_ip' => fake()->ipv4(),
            'visitor_last_seen_at' => now(),
            'last_message_at' => now(),
            'closed_at' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (): array => [
            'status' => ChatConversation::STATUS_CLOSED,
            'closed_at' => now(),
        ]);
    }
}
