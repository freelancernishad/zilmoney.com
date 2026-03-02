<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreDocumentRequest extends FormRequest
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
            'documents' => 'required|array',
            'documents.formation_document' => 'nullable|string',
            'documents.ownership_document' => 'nullable|string',
            'documents.principal_officer_id' => 'nullable|string',
            'documents.supporting_documents' => 'nullable|array',
            'documents.supporting_documents.*.type' => 'required_with:documents.supporting_documents|string',
            'documents.supporting_documents.*.file' => 'required_with:documents.supporting_documents|string',
        ];
    }
}
