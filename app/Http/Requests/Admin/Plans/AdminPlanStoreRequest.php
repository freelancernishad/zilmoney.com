<?php

namespace App\Http\Requests\Admin\Plans;

use Illuminate\Foundation\Http\FormRequest;

class AdminPlanStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'type' => 'nullable|string|in:top_box,plan,package',
            'duration' => 'required|string',
            'original_price' => 'required|numeric',
            'monthly_price' => 'nullable|numeric',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'features' => 'required|array',
            'features.*.label' => 'sometimes|required|string',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'serial' => 'nullable|integer|min:0',
        ];
    }
}
