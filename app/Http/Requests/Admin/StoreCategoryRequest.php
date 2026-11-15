<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\NotIn;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('categories.manage') ?? false;
    }

    protected function excludeCurrentCategory(): NotIn
    {
        $category = $this->route('category');

        return Rule::notIn($category instanceof Category ? [$category->id] : []);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii'],
            'description' => ['nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id'), $this->excludeCurrentCategory()],
            'order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
