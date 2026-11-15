<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\NotIn;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Page::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->pageRules();
    }

    protected function excludeCurrentPage(): NotIn
    {
        $page = $this->route('page');

        return Rule::notIn($page instanceof Page ? [$page->id] : []);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function pageRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'template' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in([Page::STATUS_DRAFT, Page::STATUS_PUBLISHED])],
            'parent_id' => ['nullable', 'integer', Rule::exists('pages', 'id'), $this->excludeCurrentPage()],
            'featured_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'seo' => ['nullable', 'array'],
            'seo.title' => ['nullable', 'string', 'max:255'],
            'seo.description' => ['nullable', 'string', 'max:500'],
            'seo.focus_keyword' => ['nullable', 'string', 'max:120'],
            'seo.canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo.robots' => ['nullable', 'string', 'max:100'],
        ];
    }
}
