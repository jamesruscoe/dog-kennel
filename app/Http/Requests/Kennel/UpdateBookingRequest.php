<?php

namespace App\Http\Requests\Kennel;

use App\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
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
            'status'            => ['sometimes', 'required', Rule::enum(BookingStatus::class)],
            'rejection_reason'  => ['nullable', 'string', 'max:1000'],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
            'notes'             => ['nullable', 'string', 'max:2000'],
        ];
    }
}
