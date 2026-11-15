<?php

namespace App\Http\Requests\Admin;

use App\Models\Media;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Media::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:20480',
                'mimes:jpg,jpeg,png,webp,gif,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,mp4,webm,mp3,wav',
                // The client filename must use an allowed extension too, so a text
                // file cannot be stored as e.g. `payload.exe`.
                'extensions:jpg,jpeg,png,webp,gif,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,mp4,webm,mp3,wav',
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
