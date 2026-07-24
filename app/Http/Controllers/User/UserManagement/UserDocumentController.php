<?php

namespace App\Http\Controllers\User\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
        $companyId = $businessDetail->id;

        // Existing document record
        $existingDoc = $businessDetail->documents()->first();

        $formationDoc = isset($data['formation_document']) 
            ? $this->processDocumentUpload($data['formation_document'], $companyId, 'formation_document')
            : ($existingDoc->formation_document ?? null);

        $ownershipDoc = isset($data['ownership_document']) 
            ? $this->processDocumentUpload($data['ownership_document'], $companyId, 'ownership_document')
            : ($existingDoc->ownership_document ?? null);

        $principalOfficerId = isset($data['principal_officer_id']) 
            ? $this->processDocumentUpload($data['principal_officer_id'], $companyId, 'principal_officer_id')
            : ($existingDoc->principal_officer_id ?? null);

        $supportingDocs = [];
        if (isset($data['supporting_documents']) && is_array($data['supporting_documents'])) {
            foreach ($data['supporting_documents'] as $idx => $sDoc) {
                $fileUrl = $this->processDocumentUpload($sDoc['file'] ?? null, $companyId, "supporting_doc_{$idx}");
                if ($fileUrl) {
                    $supportingDocs[] = [
                        'type' => $sDoc['type'] ?? 'Supporting Document',
                        'name' => $sDoc['name'] ?? "Supporting_Doc_" . ($idx + 1),
                        'file' => $fileUrl
                    ];
                }
            }
        } elseif ($existingDoc) {
            $supportingDocs = $existingDoc->supporting_documents ?? [];
        }

        $documentData = [
            'formation_document' => $formationDoc,
            'ownership_document' => $ownershipDoc,
            'principal_officer_id' => $principalOfficerId,
            'supporting_documents' => $supportingDocs
        ];

        $document = $businessDetail->documents()->updateOrCreate(
            ['company_id' => $businessDetail->id],
            $documentData
        );

        return response()->json([
            'message' => 'Documents uploaded and saved to AWS S3 successfully',
            'data' => [
                'documents' => $document
            ]
        ], 200);
    }

    /**
     * Upload document file directly to AWS S3 Storage
     */
    private function processDocumentUpload($content, $companyId, $docType)
    {
        if (empty($content)) {
            return null;
        }

        // If it's already an HTTP URL (S3 URL or external URL), return as is
        if (str_starts_with($content, 'http://') || str_starts_with($content, 'https://')) {
            return $content;
        }

        // Check if it's a Base64 string from frontend upload
        if (preg_match('/^data:(.*?);base64,(.*)$/', $content, $matches)) {
            $mimeType = $matches[1];
            $base64Data = base64_decode($matches[2]);

            $extension = 'pdf';
            if (str_contains($mimeType, 'png')) $extension = 'png';
            elseif (str_contains($mimeType, 'jpg') || str_contains($mimeType, 'jpeg')) $extension = 'jpg';

            $filename = "documents/{$companyId}/{$docType}_" . time() . ".{$extension}";

            try {
                // If AWS S3 disk is active or configured in .env
                if (config('filesystems.default') === 's3' || env('FILESYSTEM_DISK') === 's3') {
                    Storage::disk('s3')->put($filename, $base64Data, 'public');
                    return Storage::disk('s3')->url($filename);
                }

                // Fallback to local public disk storage
                Storage::disk('public')->put($filename, $base64Data);
                return asset("storage/{$filename}");
            } catch (\Exception $e) {
                Log::error("AWS S3 Document Upload Error: " . $e->getMessage());
                // Fallback to local storage
                Storage::disk('public')->put($filename, $base64Data);
                return asset("storage/{$filename}");
            }
        }

        return $content;
    }
}
