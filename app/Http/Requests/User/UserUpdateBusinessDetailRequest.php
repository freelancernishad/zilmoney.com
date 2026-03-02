<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateBusinessDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'legal_business_name' => 'nullable|string|max:255',
            'dba' => 'nullable|string|max:255',
            'entity_type' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'business_in' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'formation_date' => 'nullable|date',
            'verification_photo_id' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'physical_address' => 'nullable|array',
            'legal_registered_address' => 'nullable|array',
            'title' => 'nullable|string|max:255',
            'control_person' => 'nullable|boolean',
            'beneficial_owner' => 'nullable|boolean',
            'percentage_ownership' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
