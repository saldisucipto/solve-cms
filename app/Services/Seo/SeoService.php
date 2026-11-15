<?php

namespace App\Services\Seo;

use App\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Model;

class SeoService
{
    /**
     * Persist SEO metadata for any model that exposes a `seo()` morph relation.
     *
     * @param  array<string, mixed>  $data
     */
    public function save(Model $model, array $data): ?SeoMetadata
    {
        if (! method_exists($model, 'seo')) {
            return null;
        }

        $attributes = collect($data)
            ->only([
                'title', 'description', 'focus_keyword', 'canonical_url', 'robots',
                'og_title', 'og_description', 'og_image', 'twitter_card',
                'twitter_title', 'twitter_description', 'twitter_image', 'schema',
            ])
            ->filter(fn (mixed $value): bool => $value !== null && $value !== '')
            ->all();

        if ($attributes === []) {
            $model->seo()->delete();

            return null;
        }

        $attributes['robots'] = $attributes['robots'] ?? 'index,follow';

        /** @var SeoMetadata $seo */
        $seo = $model->seo()->updateOrCreate([], $attributes);

        return $seo;
    }

    /**
     * Resolve the metadata used to render a page, falling back to sensible defaults.
     *
     * @return array<string, mixed>
     */
    public function forModel(Model $model, string $fallbackTitle, string $fallbackDescription = ''): array
    {
        $seo = method_exists($model, 'seo') ? $model->getRelationValue('seo') : null;

        if (! $seo instanceof SeoMetadata) {
            $seo = null;
        }

        $title = $seo?->title ?: $fallbackTitle;
        $description = $seo?->description ?: $fallbackDescription;
        $image = $seo?->og_image;

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $seo?->canonical_url,
            'robots' => $seo !== null ? $seo->robots : 'index,follow',
            'og' => [
                'title' => $seo?->og_title ?: $title,
                'description' => $seo?->og_description ?: $description,
                'image' => $image,
                'type' => 'article',
            ],
            'twitter' => [
                'card' => $seo?->twitter_card ?: 'summary_large_image',
                'title' => $seo?->twitter_title ?: $title,
                'description' => $seo?->twitter_description ?: $description,
                'image' => $seo?->twitter_image ?: $image,
            ],
            'schema' => $seo?->schema,
            'focus_keyword' => $seo?->focus_keyword,
        ];
    }
}
