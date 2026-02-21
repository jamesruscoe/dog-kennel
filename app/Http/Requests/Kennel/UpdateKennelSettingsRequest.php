<?php

namespace App\Http\Requests\Kennel;

use App\Enums\DayOfWeek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKennelSettingsRequest extends FormRequest
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
            'max_capacity'        => ['sometimes', 'required', 'integer', 'min:1', 'max:500'],
            'nightly_rate_pence'  => ['sometimes', 'required', 'integer', 'min:0'],
            'operating_days'      => ['sometimes', 'required', 'array', 'min:1'],
            'operating_days.*'    => ['integer', Rule::enum(DayOfWeek::class)],
            'check_in_time'       => ['sometimes', 'required', 'date_format:H:i'],
            'check_out_time'      => ['sometimes', 'required', 'date_format:H:i'],
            'booking_lead_days'   => ['sometimes', 'required', 'integer', 'min:0'],
            'terms_and_conditions' => ['nullable', 'string'],
        ];
    }
}
