<?php

namespace App\Services\Content;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Whitelist-based HTML sanitizer for editor content.
 *
 * Content is stored as HTML and later rendered on the public website, so every
 * stored fragment passes through here first. Disallowed tags are unwrapped
 * (their text is kept); dangerous tags are removed entirely together with their
 * contents; all attributes outside the allow-list are stripped.
 */
class HtmlSanitizer
{
    /**
     * @var list<string>
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr', 'span', 'div',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'mark',
        'ul', 'ol', 'li', 'blockquote', 'code', 'pre',
        'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption',
    ];

    /**
     * Tags removed entirely, including their children.
     *
     * @var list<string>
     */
    private const DROP_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'link', 'meta'];

    /**
     * @var list<string>
     */
    private const GLOBAL_ATTRIBUTES = ['class', 'title', 'id'];

    /**
     * @var array<string, list<string>>
     */
    private const TAG_ATTRIBUTES = [
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height', 'loading', 'style'],
        'figure' => ['style'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan', 'scope'],
    ];

    /**
     * Inline styles are only permitted on media, and only for the properties an
     * editor needs to size an image or wrap text around it.
     *
     * @var list<string>
     */
    private const ALLOWED_STYLE_PROPERTIES = [
        'width', 'height', 'max-width', 'min-width',
        'float', 'clear',
        'display', 'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
        'border-radius', 'object-fit', 'aspect-ratio',
    ];

    public function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        $document = new DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="sanitize-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('sanitize-root');

        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->sanitizeChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return $output;
    }

    private function sanitizeChildren(DOMNode $node): void
    {
        for ($child = $node->firstChild; $child !== null; $child = $next) {
            $next = $child->nextSibling;

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_TAGS, true)) {
                $node->removeChild($child);

                continue;
            }

            $this->sanitizeChildren($child);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                $this->unwrap($child);

                continue;
            }

            $this->sanitizeAttributes($child, $tag);
        }
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowed = array_merge(
            self::GLOBAL_ATTRIBUTES,
            self::TAG_ATTRIBUTES[$tag] ?? [],
        );

        for ($i = $element->attributes->length - 1; $i >= 0; $i--) {
            $attribute = $element->attributes->item($i);

            if ($attribute === null) {
                continue;
            }

            $name = strtolower($attribute->name);

            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->name);

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && $this->isUnsafeUrl($attribute->value)) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($tag === 'a') {
            if ($element->getAttribute('target') === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            }
        }

        if ($element->hasAttribute('style')) {
            $style = $this->sanitizeStyle($element->getAttribute('style'));

            if ($style === '') {
                $element->removeAttribute('style');
            } else {
                $element->setAttribute('style', $style);
            }
        }

        if ($tag === 'img' && ! $element->getAttribute('src')) {
            $element->parentNode?->removeChild($element);
        }
    }

    /**
     * Keep only allow-listed declarations with harmless values.
     */
    private function sanitizeStyle(string $style): string
    {
        $safe = [];

        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = explode(':', $declaration, 2);

            $property = strtolower(trim($property));
            $value = trim($value);

            if (! in_array($property, self::ALLOWED_STYLE_PROPERTIES, true)) {
                continue;
            }

            if ($value === '' || mb_strlen($value) > 120) {
                continue;
            }

            if (preg_match('/url\s*\(|expression\s*\(|javascript:|vbscript:|@import|[<>\\\\"]/i', $value) === 1) {
                continue;
            }

            $safe[] = $property.': '.$value;
        }

        return implode('; ', $safe);
    }

    private function isUnsafeUrl(string $url): bool
    {
        $normalized = strtolower(preg_replace('/\s+/', '', $url) ?? '');

        return str_starts_with($normalized, 'javascript:')
            || str_starts_with($normalized, 'vbscript:')
            || str_starts_with($normalized, 'data:');
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if ($parent === null) {
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
