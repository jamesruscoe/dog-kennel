<?php

namespace App\Http\Requests\Kennel;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreDogRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Staff can create for any owner; owners can create for themselves
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Owners submit without owner_id; the controller derives it from auth.
        $ownerIdRules = $this->user()?->role === UserRole::Staff
            ? ['required', 'integer', 'exists:owners,id']
            : ['nullable', 'integer', 'exists:owners,id'];

        return [
            'owner_id'              => $ownerIdRules,
            'name'                  => ['required', 'string', 'max:100'],
            'breed'                 => ['required', 'string', 'max:100'],
            'date_of_birth'         => ['nullable', 'date', 'before:today'],
            'sex'                   => ['required', 'in:male,female'],
            'neutered'              => ['required', 'boolean'],
            'weight_kg'             => ['nullable', 'numeric', 'min:0', 'max:200'],
            'colour'                => ['nullable', 'string', 'max:100'],
            'microchip_number'      => ['nullable', 'string', 'max:50'],
            'vet_name'              => ['nullable', 'string', 'max:255'],
            'vet_phone'             => ['nullable', 'string', 'max:30'],
            'vaccination_confirmed' => ['required', 'boolean'],
            'medical_notes'         => ['nullable', 'string', 'max:2000'],
            'dietary_notes'         => ['nullable', 'string', 'max:2000'],
            'behavioural_notes'     => ['nullable', 'string', 'max:2000'],
        ];
    }
}
