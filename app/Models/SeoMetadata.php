<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $focus_keyword
 * @property string|null $canonical_url
 * @property string $robots
 * @property string|null $og_title
 * @property string|null $og_description
 * @property string|null $og_image
 * @property string $twitter_card
 * @property string|null $twitter_title
 * @property string|null $twitter_description
 * @property string|null $twitter_image
 * @property array<string, mixed>|null $schema
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'title', 'description', 'focus_keyword', 'canonical_url', 'robots',
    'og_title', 'og_description', 'og_image', 'twitter_card',
    'twitter_title', 'twitter_description', 'twitter_image', 'schema',
])]
class SeoMetadata extends Model
{
    protected $table = 'seo_metadata';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schema' => 'array',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function isIndexable(): bool
    {
        return ! str_contains($this->robots, 'noindex');
    }
}
