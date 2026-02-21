<?php

namespace App\Http\Requests\Kennel;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dog_id'               => ['required', 'integer', 'exists:dogs,id'],
            'check_in_date'        => ['required', 'date', 'after_or_equal:today'],
            'check_out_date'       => ['required', 'date', 'after:check_in_date'],
            'notes'                => ['nullable', 'string', 'max:2000'],
            'special_requirements' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'check_out_date.after'          => 'Check-out must be at least one day after check-in.',
            'check_in_date.after_or_equal'  => 'Check-in date cannot be in the past.',
        ];
    }
}
