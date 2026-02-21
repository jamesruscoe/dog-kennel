<?php

namespace App\Http\Requests\Kennel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOwnerRequest extends FormRequest
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
        $ownerId = $this->route('owner')?->user_id;

        return [
            'name'                    => ['sometimes', 'required', 'string', 'max:255'],
            'email'                   => ['sometimes', 'required', 'email', 'max:255', "unique:users,email,{$ownerId}"],
            'phone'                   => ['sometimes', 'required', 'string', 'max:30'],
            'address'                 => ['nullable', 'string', 'max:500'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'notes'                   => ['nullable', 'string', 'max:2000'],
        ];
    }
}
