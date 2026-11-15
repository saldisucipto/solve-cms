<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdatePageRequest extends StorePageRequest
{
    public function authorize(): bool
    {
        $page = $this->route('page');

        return $page instanceof Page
            && ($this->user()?->can('update', $page) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->pageRules();
    }
}
