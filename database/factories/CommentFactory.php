<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'parent_id' => null,
            'user_id' => null,
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'author_website' => null,
            'content' => fake()->paragraph(),
            'status' => Comment::STATUS_PENDING,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'approved_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => Comment::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
    }

    public function spam(): static
    {
        return $this->state(fn (): array => ['status' => Comment::STATUS_SPAM]);
    }

    public function replyTo(Comment $parent): static
    {
        return $this->state(fn (): array => [
            'post_id' => $parent->post_id,
            'parent_id' => $parent->id,
        ]);
    }
}
