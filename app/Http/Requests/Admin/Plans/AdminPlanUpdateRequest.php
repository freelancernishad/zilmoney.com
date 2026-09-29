<?php

namespace App\Http\Requests\Admin\Plans;

use Illuminate\Foundation\Http\FormRequest;

class AdminPlanUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string',
            'duration' => 'sometimes|required|string',
            'original_price' => 'sometimes|required|numeric',
            'monthly_price' => 'nullable|numeric',
            'discount_percentage' => 'sometimes|required|numeric|min:0|max:100',
            'features' => 'sometimes|required|array',
            'features.*.label' => 'sometimes|required|string',
            'is_active' => 'nullable|boolean',
            'serial' => 'nullable|integer|min:0',
        ];
    }
}
