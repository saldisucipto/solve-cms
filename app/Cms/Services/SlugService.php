<?php

namespace App\Cms\Services;

class SlugService
{
    public function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text) ?? '';
        $text = preg_replace('/[\s-]+/', '-', $text) ?? '';

        return trim($text, '-') ?: 'content';
    }
}
