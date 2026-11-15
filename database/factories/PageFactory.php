<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->sentence(4), '.');

        return [
            'parent_id' => null,
            'user_id' => User::factory(),
            'featured_media_id' => null,
            'title' => Str::title($title),
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'excerpt' => fake()->sentence(),
            'content' => '<p>'.fake()->paragraph().' '.fake()->paragraph().'</p>',
            'template' => 'default',
            'status' => Page::STATUS_DRAFT,
            'order' => 0,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);
    }
}
