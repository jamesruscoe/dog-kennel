<?php

namespace App\Http\Requests\Kennel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDogRequest extends FormRequest
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
            'name'                  => ['sometimes', 'required', 'string', 'max:100'],
            'breed'                 => ['sometimes', 'required', 'string', 'max:100'],
            'date_of_birth'         => ['nullable', 'date', 'before:today'],
            'sex'                   => ['sometimes', 'required', 'in:male,female'],
            'neutered'              => ['sometimes', 'required', 'boolean'],
            'weight_kg'             => ['nullable', 'numeric', 'min:0', 'max:200'],
            'colour'                => ['nullable', 'string', 'max:100'],
            'microchip_number'      => ['nullable', 'string', 'max:50'],
            'vet_name'              => ['nullable', 'string', 'max:255'],
            'vet_phone'             => ['nullable', 'string', 'max:30'],
            'vaccination_confirmed' => ['sometimes', 'required', 'boolean'],
            'medical_notes'         => ['nullable', 'string', 'max:2000'],
            'dietary_notes'         => ['nullable', 'string', 'max:2000'],
            'behavioural_notes'     => ['nullable', 'string', 'max:2000'],
        ];
    }
}
