<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('settings.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['logo', 'dashboard_logo', 'favicon'])],
            'file' => [
                'required',
                'file',
                'max:4096',
                'mimes:jpg,jpeg,png,webp,gif,svg,ico',
            ],
        ];
    }
}
