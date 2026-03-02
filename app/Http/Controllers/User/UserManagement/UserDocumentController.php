<?php

namespace App\Http\Controllers\User\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\User\UserStoreDocumentRequest;
use App\Models\Zilmoney\Document;

class UserDocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $businessDetail = $user->businessDetails;

        if (!$businessDetail) {
            return response()->json([
                'message' => 'Business profile not found',
                'data' => [
                    'documents' => [
                        'formation_document' => null,
                        'ownership_document' => null,
                        'principal_officer_id' => null,
                        'supporting_documents' => []
                    ]
                ]
            ]);
        }

        $document = $businessDetail->documents()->first();

        $formattedDocuments = [
            'formation_document' => $document->formation_document ?? null,
            'ownership_document' => $document->ownership_document ?? null,
            'principal_officer_id' => $document->principal_officer_id ?? null,
            'supporting_documents' => $document->supporting_documents ?? []
        ];

        return response()->json([
            'message' => 'Documents retrieved successfully',
            'data' => [
                'documents' => $formattedDocuments
            ]
        ]);
    }

    public function store(UserStoreDocumentRequest $request)
    {
        $user = $request->user();
        $businessDetail = $user->businessDetails;

        if (!$businessDetail) {
            return response()->json([
                'message' => 'You must complete your business profile before uploading documents.',
            ], 400);
        }

        $data = $request->validated()['documents'];

        $documentData = [
            'formation_document' => $data['formation_document'] ?? null,
            'ownership_document' => $data['ownership_document'] ?? null,
            'principal_officer_id' => $data['principal_officer_id'] ?? null,
            'supporting_documents' => $data['supporting_documents'] ?? []
        ];

        $document = $businessDetail->documents()->updateOrCreate(
            ['company_id' => $businessDetail->id],
            $documentData
        );

        return response()->json([
            'message' => 'Documents updated successfully'
        ], 200);
    }
}
