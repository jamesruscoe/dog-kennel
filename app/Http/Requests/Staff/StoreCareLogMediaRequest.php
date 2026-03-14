<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StoreCareLogMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaffOrAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'images'   => ['required', 'array', 'max:5'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ];
    }
}
