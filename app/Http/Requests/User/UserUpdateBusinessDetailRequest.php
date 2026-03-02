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
            'business_details' => 'required|array',
            'business_details.first_name' => 'nullable|string|max:255',
            'business_details.last_name' => 'nullable|string|max:255',
            'business_details.legal_business_name' => 'nullable|string|max:255',
            'business_details.dba' => 'nullable|string|max:255',
            'business_details.entity_type' => 'nullable|string|max:255',
            'business_details.country' => 'nullable|string|max:255',
            'business_details.phone_number' => 'nullable|string|max:255',
            'business_details.verification_photo_id' => 'nullable|string|max:255',
            'business_details.business_in' => 'nullable|string|max:255',
            'business_details.industry' => 'nullable|string|max:255',
            'business_details.website' => 'nullable|string|max:255',
            'business_details.description' => 'nullable|string|max:1000',
            'business_details.formation_date' => 'nullable|date',
            'business_details.physical_address' => 'nullable|string|array',
            'business_details.legal_registered_address' => 'nullable|string|array',
            
            // New explicitly defined fields for title/owners
            'business_details.title' => 'nullable|string|max:255',
            'business_details.control_person' => 'nullable|boolean',
            'business_details.beneficial_owner' => 'nullable|boolean',
            'business_details.percentage_owner_ship' => 'nullable|numeric|min:0|max:100',
            
            // Controllers array validation
            'business_details.controllers' => 'nullable|array',
            'business_details.controllers.*.first_name' => 'required_with:business_details.controllers|string|max:255',
            'business_details.controllers.*.last_name' => 'required_with:business_details.controllers|string|max:255',
            'business_details.controllers.*.job_title' => 'nullable|string|max:255',
            'business_details.controllers.*.email_address' => 'nullable|email|max:255',
            'business_details.controllers.*.is_individual_owner' => 'nullable|boolean',
            'business_details.controllers.*.percentage_owner_ship' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
