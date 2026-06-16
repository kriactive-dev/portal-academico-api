<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:trainers,email'],
            'phone' => ['required', 'string', 'max:50'],
            'specialty' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The trainer name is required.',
            'email.required' => 'The email address is required.',
            'email.unique' => 'This email is already in use.',
            'phone.required' => 'The phone number is required.',
            'specialty.required' => 'The specialty is required.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be active or inactive.',
        ];
    }
}
