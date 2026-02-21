<?php

namespace App\Http\Requests\Kennel;

use Illuminate\Foundation\Http\FormRequest;

class StoreCareLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'activity_type' => ['required', 'string', 'in:feeding,walking,medication,grooming,play,toilet,health_check,other'],
            'notes'         => ['nullable', 'string', 'max:2000'],
            'occurred_at'   => ['nullable', 'date', 'before_or_equal:now'],
        ];
    }
}
