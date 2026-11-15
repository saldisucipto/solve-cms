<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = Str::random(20).'.jpg';

        return [
            'user_id' => User::factory(),
            'disk' => 'public',
            'path' => 'media/'.$filename,
            'filename' => $filename,
            'original_name' => 'image.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => fake()->numberBetween(10_000, 2_000_000),
            'width' => fake()->numberBetween(400, 2000),
            'height' => fake()->numberBetween(300, 1500),
            'title' => fake()->words(3, true),
            'alt_text' => fake()->sentence(4),
            'caption' => null,
        ];
    }

    public function document(): static
    {
        $filename = Str::random(20).'.pdf';

        return $this->state(fn (): array => [
            'path' => 'media/'.$filename,
            'filename' => $filename,
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'width' => null,
            'height' => null,
        ]);
    }
}
