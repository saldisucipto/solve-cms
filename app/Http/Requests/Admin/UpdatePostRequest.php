<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdatePostRequest extends StorePostRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post
            && ($this->user()?->can('edit', $post) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->postRules();
    }
}
