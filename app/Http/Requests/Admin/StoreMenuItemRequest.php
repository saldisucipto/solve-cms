<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMenuItemRequest extends FormRequest
{
    /**
     * @var array<string, class-string>
     */
    public const LINKABLE_TYPES = [
        'post' => Post::class,
        'page' => Page::class,
        'category' => Category::class,
        'tag' => Tag::class,
    ];

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('menus.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in([...array_keys(self::LINKABLE_TYPES), 'custom'])],
            'url' => ['nullable', 'string', 'max:2048', 'required_if:type,custom'],
            'linkable_id' => ['nullable', 'integer', 'required_unless:type,custom'],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * The model class the item should link to, or null for custom links.
     *
     * @return class-string|null
     */
    public function linkableClass(): ?string
    {
        return self::LINKABLE_TYPES[$this->string('type')->value()] ?? null;
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $class = $this->linkableClass();

                if ($class === null || $validator->errors()->has('linkable_id')) {
                    return;
                }

                $id = $this->integer('linkable_id');

                if ($id > 0 && ! $class::whereKey($id)->exists()) {
                    $validator->errors()->add('linkable_id', 'The selected item does not exist.');
                }
            },
        ];
    }
}
