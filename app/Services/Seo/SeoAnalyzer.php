<?php

namespace App\Services\Seo;

use App\Models\SeoMetadata;
use DOMDocument;

/**
 * Analyses content against practical on-page SEO criteria.
 *
 * Every check reports a plain-language recommendation. Nothing here produces a
 * "score" that pretends to predict ranking — the output is a checklist plus a
 * pass/warning/fail summary so the author can act on specific issues.
 *
 * @phpstan-type CheckResult array{id: string, label: string, status: string, message: string}
 */
class SeoAnalyzer
{
    public const STATUS_GOOD = 'good';

    public const STATUS_WARNING = 'warning';

    public const STATUS_BAD = 'bad';

    private const TITLE_MIN = 30;

    private const TITLE_MAX = 60;

    private const DESCRIPTION_MIN = 120;

    private const DESCRIPTION_MAX = 160;

    private const CONTENT_MIN_WORDS = 300;

    private const SLUG_MAX = 75;

    /**
     * @param  array{
     *     title?: string|null,
     *     seo_title?: string|null,
     *     meta_description?: string|null,
     *     focus_keyword?: string|null,
     *     slug?: string|null,
     *     content?: string|null,
     *     canonical_url?: string|null,
     *     featured_image_alt?: string|null,
     *     ignore_seo_id?: int|null,
     * }  $content
     * @return array{
     *     summary: array{good: int, warning: int, bad: int, total: int},
     *     checks: array<int, CheckResult>,
     *     stats: array{words: int, headings: int, internal_links: int, external_links: int, images: int}
     * }
     */
    public function analyze(array $content): array
    {
        $title = $this->clean($content['seo_title'] ?? null)
            ?? $this->clean($content['title'] ?? null)
            ?? '';
        $description = $this->clean($content['meta_description'] ?? null) ?? '';
        $keyword = $this->keyword($content['focus_keyword'] ?? null);
        $slug = $this->clean($content['slug'] ?? null) ?? '';
        $html = (string) ($content['content'] ?? '');
        $plainText = $this->toPlainText($html);
        $wordCount = $this->wordCount($plainText);

        $analysis = $this->extractDocument($html);

        $checks = [
            $this->titlePresent($title),
            $this->titleLength($title),
            $this->descriptionPresent($description),
            $this->descriptionLength($description),
            $this->keywordPresent($keyword),
            $this->keywordInTitle($keyword, $title),
            $this->keywordInDescription($keyword, $description),
            $this->keywordInSlug($keyword, $slug),
            $this->keywordInFirstParagraph($keyword, $plainText),
            $this->keywordInHeadings($keyword, $analysis['headings']),
            $this->contentLength($wordCount),
            $this->imageAlt($analysis['images'], $content['featured_image_alt'] ?? null),
            $this->internalLinks($analysis['internal_links']),
            $this->externalLinks($analysis['external_links']),
            $this->headingHierarchy($analysis['headings'], $analysis['has_h1']),
            $this->slugLength($slug),
            $this->canonical($content['canonical_url'] ?? null),
            $this->duplicateMetadata($title, $description, $content['ignore_seo_id'] ?? null),
        ];

        return [
            'summary' => $this->summarise($checks),
            'checks' => $checks,
            'stats' => [
                'words' => $wordCount,
                'headings' => count($analysis['headings']),
                'internal_links' => count($analysis['internal_links']),
                'external_links' => count($analysis['external_links']),
                'images' => count($analysis['images']),
            ],
        ];
    }

    /**
     * @param  array<int, CheckResult>  $checks
     * @return array{good: int, warning: int, bad: int, total: int}
     */
    private function summarise(array $checks): array
    {
        $good = 0;
        $warning = 0;
        $bad = 0;

        foreach ($checks as $check) {
            match ($check['status']) {
                self::STATUS_GOOD => $good++,
                self::STATUS_WARNING => $warning++,
                self::STATUS_BAD => $bad++,
                default => null,
            };
        }

        return ['good' => $good, 'warning' => $warning, 'bad' => $bad, 'total' => count($checks)];
    }

    /**
     * @return CheckResult
     */
    private function result(string $id, string $label, string $status, string $message): array
    {
        return compact('id', 'label', 'status', 'message');
    }

    /**
     * @return CheckResult
     */
    private function titlePresent(string $title): array
    {
        return $title === ''
            ? $this->result('title_present', 'SEO title', self::STATUS_BAD, 'Add an SEO title so search engines have something specific to display.')
            : $this->result('title_present', 'SEO title', self::STATUS_GOOD, 'SEO title is set.');
    }

    /**
     * @return CheckResult
     */
    private function titleLength(string $title): array
    {
        $length = mb_strlen($title);

        if ($title === '') {
            return $this->result('title_length', 'Title length', self::STATUS_BAD, 'No title to measure.');
        }

        if ($length < self::TITLE_MIN) {
            return $this->result('title_length', 'Title length', self::STATUS_WARNING, "Title is {$length} characters. Aim for ".self::TITLE_MIN.'–'.self::TITLE_MAX.' so it fills the result without being cut off.');
        }

        if ($length > self::TITLE_MAX) {
            return $this->result('title_length', 'Title length', self::STATUS_WARNING, "Title is {$length} characters and may be truncated. Keep it under ".self::TITLE_MAX.'.');
        }

        return $this->result('title_length', 'Title length', self::STATUS_GOOD, "Title is {$length} characters.");
    }

    /**
     * @return CheckResult
     */
    private function descriptionPresent(string $description): array
    {
        return $description === ''
            ? $this->result('description_present', 'Meta description', self::STATUS_BAD, 'Write a meta description; search engines often use it as the snippet.')
            : $this->result('description_present', 'Meta description', self::STATUS_GOOD, 'Meta description is set.');
    }

    /**
     * @return CheckResult
     */
    private function descriptionLength(string $description): array
    {
        $length = mb_strlen($description);

        if ($description === '') {
            return $this->result('description_length', 'Description length', self::STATUS_BAD, 'No description to measure.');
        }

        if ($length < self::DESCRIPTION_MIN) {
            return $this->result('description_length', 'Description length', self::STATUS_WARNING, "Description is {$length} characters. Aim for ".self::DESCRIPTION_MIN.'–'.self::DESCRIPTION_MAX.' for a fuller snippet.');
        }

        if ($length > self::DESCRIPTION_MAX) {
            return $this->result('description_length', 'Description length', self::STATUS_WARNING, "Description is {$length} characters and may be cut off. Keep it under ".self::DESCRIPTION_MAX.'.');
        }

        return $this->result('description_length', 'Description length', self::STATUS_GOOD, "Description is {$length} characters.");
    }

    /**
     * @return CheckResult
     */
    private function keywordPresent(string $keyword): array
    {
        return $keyword === ''
            ? $this->result('keyword_present', 'Focus keyword', self::STATUS_BAD, 'Set a focus keyword so the remaining checks can run.')
            : $this->result('keyword_present', 'Focus keyword', self::STATUS_GOOD, "Focus keyword: \"{$keyword}\".");
    }

    /**
     * @return CheckResult
     */
    private function keywordInTitle(string $keyword, string $title): array
    {
        if ($keyword === '') {
            return $this->result('keyword_in_title', 'Keyword in title', self::STATUS_WARNING, 'Set a focus keyword first.');
        }

        return $this->contains($title, $keyword)
            ? $this->result('keyword_in_title', 'Keyword in title', self::STATUS_GOOD, 'The focus keyword appears in the title.')
            : $this->result('keyword_in_title', 'Keyword in title', self::STATUS_WARNING, 'Consider including the focus keyword in the title.');
    }

    /**
     * @return CheckResult
     */
    private function keywordInDescription(string $keyword, string $description): array
    {
        if ($keyword === '') {
            return $this->result('keyword_in_description', 'Keyword in description', self::STATUS_WARNING, 'Set a focus keyword first.');
        }

        return $this->contains($description, $keyword)
            ? $this->result('keyword_in_description', 'Keyword in description', self::STATUS_GOOD, 'The focus keyword appears in the meta description.')
            : $this->result('keyword_in_description', 'Keyword in description', self::STATUS_WARNING, 'Consider including the focus keyword in the meta description.');
    }

    /**
     * @return CheckResult
     */
    private function keywordInSlug(string $keyword, string $slug): array
    {
        if ($keyword === '') {
            return $this->result('keyword_in_slug', 'Keyword in URL', self::STATUS_WARNING, 'Set a focus keyword first.');
        }

        if ($slug === '') {
            return $this->result('keyword_in_slug', 'Keyword in URL', self::STATUS_WARNING, 'This entry has no slug yet.');
        }

        return $this->contains(str_replace('-', ' ', $slug), $keyword)
            ? $this->result('keyword_in_slug', 'Keyword in URL', self::STATUS_GOOD, 'The focus keyword appears in the URL slug.')
            : $this->result('keyword_in_slug', 'Keyword in URL', self::STATUS_WARNING, 'Consider adding the focus keyword to the URL slug.');
    }

    /**
     * @return CheckResult
     */
    private function keywordInFirstParagraph(string $keyword, string $text): array
    {
        if ($keyword === '') {
            return $this->result('keyword_in_first_paragraph', 'Keyword in first paragraph', self::STATUS_WARNING, 'Set a focus keyword first.');
        }

        $firstParagraph = mb_substr($text, 0, 200);

        return $this->contains($firstParagraph, $keyword)
            ? $this->result('keyword_in_first_paragraph', 'Keyword in first paragraph', self::STATUS_GOOD, 'The focus keyword appears early in the content.')
            : $this->result('keyword_in_first_paragraph', 'Keyword in first paragraph', self::STATUS_WARNING, 'Try to mention the focus keyword in the opening paragraph.');
    }

    /**
     * @param  array<int, array{level: int, text: string}>  $headings
     * @return CheckResult
     */
    private function keywordInHeadings(string $keyword, array $headings): array
    {
        if ($keyword === '') {
            return $this->result('keyword_in_headings', 'Keyword in headings', self::STATUS_WARNING, 'Set a focus keyword first.');
        }

        if ($headings === []) {
            return $this->result('keyword_in_headings', 'Keyword in headings', self::STATUS_WARNING, 'Add subheadings to structure the content.');
        }

        foreach ($headings as $heading) {
            if ($this->contains($heading['text'], $keyword)) {
                return $this->result('keyword_in_headings', 'Keyword in headings', self::STATUS_GOOD, 'The focus keyword appears in a subheading.');
            }
        }

        return $this->result('keyword_in_headings', 'Keyword in headings', self::STATUS_WARNING, 'Consider using the focus keyword in at least one subheading.');
    }

    /**
     * @return CheckResult
     */
    private function contentLength(int $words): array
    {
        if ($words === 0) {
            return $this->result('content_length', 'Content length', self::STATUS_BAD, 'There is no body content yet.');
        }

        if ($words < self::CONTENT_MIN_WORDS) {
            return $this->result('content_length', 'Content length', self::STATUS_WARNING, "{$words} words. Longer content (".self::CONTENT_MIN_WORDS.'+) tends to cover a topic more completely.');
        }

        return $this->result('content_length', 'Content length', self::STATUS_GOOD, "{$words} words.");
    }

    /**
     * @param  array<int, array{src: string, alt: string}>  $images
     * @return CheckResult
     */
    private function imageAlt(array $images, ?string $featuredAlt): array
    {
        if ($images === [] && $this->clean($featuredAlt) === null) {
            return $this->result('image_alt', 'Image alt text', self::STATUS_WARNING, 'No images found. Images with descriptive alt text can help.');
        }

        $missing = array_filter($images, fn (array $image): bool => $image['alt'] === '');

        if ($missing !== []) {
            return $this->result('image_alt', 'Image alt text', self::STATUS_BAD, count($missing).' image(s) are missing alt text.');
        }

        return $this->result('image_alt', 'Image alt text', self::STATUS_GOOD, 'All images have alt text.');
    }

    /**
     * @param  array<int, string>  $links
     * @return CheckResult
     */
    private function internalLinks(array $links): array
    {
        return $links === []
            ? $this->result('internal_links', 'Internal links', self::STATUS_WARNING, 'Link to related content on your own site to help discoverability.')
            : $this->result('internal_links', 'Internal links', self::STATUS_GOOD, count($links).' internal link(s).');
    }

    /**
     * @param  array<int, string>  $links
     * @return CheckResult
     */
    private function externalLinks(array $links): array
    {
        return $links === []
            ? $this->result('external_links', 'External links', self::STATUS_WARNING, 'Citing an external source can add credibility.')
            : $this->result('external_links', 'External links', self::STATUS_GOOD, count($links).' external link(s).');
    }

    /**
     * @param  array<int, array{level: int, text: string}>  $headings
     * @return CheckResult
     */
    private function headingHierarchy(array $headings, bool $hasH1): array
    {
        if ($hasH1) {
            return $this->result('heading_hierarchy', 'Heading hierarchy', self::STATUS_WARNING, 'Avoid <h1> inside the body; the page already provides one. Use <h2> onwards.');
        }

        if ($headings === []) {
            return $this->result('heading_hierarchy', 'Heading hierarchy', self::STATUS_WARNING, 'No subheadings found. Break long content into sections.');
        }

        $previous = 2;

        foreach ($headings as $heading) {
            if ($heading['level'] > $previous + 1) {
                return $this->result('heading_hierarchy', 'Heading hierarchy', self::STATUS_WARNING, 'Heading levels skip a step (for example h2 to h4). Keep the order sequential.');
            }

            $previous = $heading['level'];
        }

        return $this->result('heading_hierarchy', 'Heading hierarchy', self::STATUS_GOOD, 'Headings follow a logical order.');
    }

    /**
     * @return CheckResult
     */
    private function slugLength(string $slug): array
    {
        if ($slug === '') {
            return $this->result('slug_length', 'URL length', self::STATUS_WARNING, 'No slug set yet.');
        }

        $length = mb_strlen($slug);

        return $length > self::SLUG_MAX
            ? $this->result('slug_length', 'URL length', self::STATUS_WARNING, "Slug is {$length} characters. Shorter URLs are easier to read and share.")
            : $this->result('slug_length', 'URL length', self::STATUS_GOOD, "Slug is {$length} characters.");
    }

    /**
     * @return CheckResult
     */
    private function canonical(?string $canonical): array
    {
        return $this->clean($canonical) === null
            ? $this->result('canonical', 'Canonical URL', self::STATUS_GOOD, 'No custom canonical set — the site will use this entry\'s own URL.')
            : $this->result('canonical', 'Canonical URL', self::STATUS_GOOD, 'A custom canonical URL is set.');
    }

    /**
     * @return CheckResult
     */
    private function duplicateMetadata(string $title, string $description, ?int $ignoreId): array
    {
        if ($title === '' && $description === '') {
            return $this->result('duplicate_metadata', 'Duplicate metadata', self::STATUS_WARNING, 'Nothing to compare yet.');
        }

        $query = SeoMetadata::query();

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        $duplicate = $query->where(function ($inner) use ($title, $description): void {
            if ($title !== '') {
                $inner->orWhere('title', $title);
            }

            if ($description !== '') {
                $inner->orWhere('description', $description);
            }
        })->exists();

        return $duplicate
            ? $this->result('duplicate_metadata', 'Duplicate metadata', self::STATUS_BAD, 'Another entry uses the same SEO title or description. Make it unique to avoid competing with yourself.')
            : $this->result('duplicate_metadata', 'Duplicate metadata', self::STATUS_GOOD, 'No other entry shares this metadata.');
    }

    /**
     * @return array{
     *     headings: array<int, array{level: int, text: string}>,
     *     images: array<int, array{src: string, alt: string}>,
     *     internal_links: array<int, string>,
     *     external_links: array<int, string>,
     *     has_h1: bool
     * }
     */
    private function extractDocument(string $html): array
    {
        $result = [
            'headings' => [],
            'images' => [],
            'internal_links' => [],
            'external_links' => [],
            'has_h1' => false,
        ];

        if (trim($html) === '') {
            return $result;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach ($document->getElementsByTagName('*') as $element) {
            $tag = strtolower($element->tagName);

            if (preg_match('/^h([1-6])$/', $tag, $matches) === 1) {
                $level = (int) $matches[1];

                if ($level === 1) {
                    $result['has_h1'] = true;

                    continue;
                }

                $result['headings'][] = ['level' => $level, 'text' => $this->clean($element->textContent) ?? ''];
            }

            if ($tag === 'img') {
                $result['images'][] = [
                    'src' => $element->getAttribute('src'),
                    'alt' => trim($element->getAttribute('alt')),
                ];
            }

            if ($tag === 'a') {
                $href = trim($element->getAttribute('href'));

                if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                    continue;
                }

                if (str_starts_with($href, 'http')) {
                    $host = parse_url($href, PHP_URL_HOST);
                    $siteHost = parse_url(config('app.url'), PHP_URL_HOST);

                    if ($host !== null && $host !== $siteHost) {
                        $result['external_links'][] = $href;

                        continue;
                    }
                }

                $result['internal_links'][] = $href;
            }
        }

        return $result;
    }

    private function contains(string $haystack, string $needle): bool
    {
        if ($haystack === '' || $needle === '') {
            return false;
        }

        return mb_stripos($haystack, $needle) !== false;
    }

    private function keyword(?string $value): string
    {
        return $this->clean($value) ?? '';
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function toPlainText(string $html): string
    {
        $text = preg_replace('/<(script|style)[^>]*>.*?<\/\1>/is', ' ', $html) ?? $html;
        $text = preg_replace('/<[^>]+>/', ' ', $text) ?? $text;
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    private function wordCount(string $text): int
    {
        if ($text === '') {
            return 0;
        }

        return count(preg_split('/\s+/', $text) ?: []);
    }
}
