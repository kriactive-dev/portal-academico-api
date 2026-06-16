<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DenyRequestEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'The denial reason is required.',
            'reason.max' => 'The denial reason must not exceed 1000 characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['denial_reason' => $this->reason]);
    }
}
