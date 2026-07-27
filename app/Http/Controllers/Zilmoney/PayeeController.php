<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FileSystem\FileUploadService;
use Illuminate\Support\Facades\Log;

class PayeeController extends Controller
{
    public function index()
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json([]);

        return response()->json($business->payees);
    }

    public function store(Request $request)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $validated = $request->validate([
            'type' => 'required|in:customer,vendor,employee',
            'payee_name' => 'required|string',
            'nick_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'payee_id_account_number' => 'nullable|numeric',
            'entity_type' => 'nullable|in:individual,business',
            'company_name' => 'nullable|string',
            'request_bank_account' => 'nullable|boolean',
            'address_line1' => 'nullable|string',
            'address_line2' => 'nullable|string',
            'country' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'bank_account_holder_name' => 'nullable|string',
            'bank_routing_number' => 'nullable|string',
            'bank_account_number' => 'nullable|string',
            'bank_account_type' => 'nullable|string',
            'routing_number' => 'nullable|string',
            'account_number' => 'nullable|string',
            'account_type' => 'nullable|string',
            'swift_code' => 'nullable|string',
            'iban' => 'nullable|string',
            'intl_bank_country' => 'nullable|string',
            'intl_bank_address' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'notes' => 'nullable|string',
            'contacts' => 'nullable|array',
            'todos' => 'nullable|array',
            'comments' => 'nullable|array',
            'attachments' => 'nullable|array',
            'audit_trials' => 'nullable|array',
            'bank_account' => 'nullable|array',
            'bank_account.account_holder_name' => 'nullable|string',
            'bank_account.bank_name' => 'nullable|string',
            'bank_account.routing_number' => 'nullable|string',
            'bank_account.account_number' => 'nullable|string',
            'bank_account.account_type' => 'nullable|string',
        ]);

        // Map TS nested bank_account object to flat table columns
        if (isset($validated['bank_account'])) {
            $validated['bank_account_holder_name'] = $validated['bank_account']['account_holder_name'] ?? $validated['bank_account_holder_name'] ?? null;
            $validated['bank_name'] = $validated['bank_account']['bank_name'] ?? $validated['bank_name'] ?? null;
            $validated['bank_routing_number'] = $validated['bank_account']['routing_number'] ?? $validated['bank_routing_number'] ?? null;
            $validated['bank_account_number'] = $validated['bank_account']['account_number'] ?? $validated['bank_account_number'] ?? null;
            $validated['bank_account_type'] = $validated['bank_account']['account_type'] ?? $validated['bank_account_type'] ?? null;
            unset($validated['bank_account']);
        }

        if (!empty($validated['routing_number'])) {
            $validated['bank_routing_number'] = $validated['routing_number'];
            unset($validated['routing_number']);
        }
        if (!empty($validated['account_number'])) {
            $validated['bank_account_number'] = $validated['account_number'];
            unset($validated['account_number']);
        }
        if (!empty($validated['account_type'])) {
            $validated['bank_account_type'] = $validated['account_type'];
            unset($validated['account_type']);
        }

        $payee = $business->payees()->create($validated);

        return response()->json($payee, 201);
    }

    public function update(Request $request, $id)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payee = $business->payees()->findOrFail($id);

        $validated = $request->validate([
            'type' => 'nullable|in:customer,vendor,employee',
            'payee_name' => 'nullable|string',
            'nick_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'payee_id_account_number' => 'nullable|numeric',
            'entity_type' => 'nullable|in:individual,business',
            'company_name' => 'nullable|string',
            'request_bank_account' => 'nullable|boolean',
            'address_line1' => 'nullable|string',
            'address_line2' => 'nullable|string',
            'country' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'bank_account_holder_name' => 'nullable|string',
            'bank_routing_number' => 'nullable|string',
            'bank_account_number' => 'nullable|string',
            'bank_account_type' => 'nullable|string',
            'routing_number' => 'nullable|string',
            'account_number' => 'nullable|string',
            'account_type' => 'nullable|string',
            'swift_code' => 'nullable|string',
            'iban' => 'nullable|string',
            'intl_bank_country' => 'nullable|string',
            'intl_bank_address' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'notes' => 'nullable|string',
            'contacts' => 'nullable|array',
            'todos' => 'nullable|array',
            'comments' => 'nullable|array',
            'attachments' => 'nullable|array',
            'audit_trials' => 'nullable|array',
            'bank_account' => 'nullable|array',
            'bank_account.account_holder_name' => 'nullable|string',
            'bank_account.bank_name' => 'nullable|string',
            'bank_account.routing_number' => 'nullable|string',
            'bank_account.account_number' => 'nullable|string',
            'bank_account.account_type' => 'nullable|string',
        ]);

        if (isset($validated['bank_account'])) {
            $validated['bank_account_holder_name'] = $validated['bank_account']['account_holder_name'] ?? $validated['bank_account_holder_name'] ?? null;
            $validated['bank_name'] = $validated['bank_account']['bank_name'] ?? $validated['bank_name'] ?? null;
            $validated['bank_routing_number'] = $validated['bank_account']['routing_number'] ?? $validated['bank_routing_number'] ?? null;
            $validated['bank_account_number'] = $validated['bank_account']['account_number'] ?? $validated['bank_account_number'] ?? null;
            $validated['bank_account_type'] = $validated['bank_account']['account_type'] ?? $validated['bank_account_type'] ?? null;
            unset($validated['bank_account']);
        }

        if (!empty($validated['routing_number'])) {
            $validated['bank_routing_number'] = $validated['routing_number'];
            unset($validated['routing_number']);
        }
        if (!empty($validated['account_number'])) {
            $validated['bank_account_number'] = $validated['account_number'];
            unset($validated['account_number']);
        }
        if (!empty($validated['account_type'])) {
            $validated['bank_account_type'] = $validated['account_type'];
            unset($validated['account_type']);
        }

        $payee->update($validated);

        return response()->json($payee);
    }

    public function destroy($id)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payee = $business->payees()->findOrFail($id);
        $payee->delete();

        return response()->json(['message' => 'Payee deleted successfully']);
    }

    public function uploadFile(Request $request, FileUploadService $fileUploadService)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');

        try {
            // Primary: S3 upload via FileUploadService
            $url = $fileUploadService->uploadFileToS3($file, 'uploads/payees');
        } catch (\Exception $e) {
            Log::info('S3 upload fallback to protected storage: ' . $e->getMessage());
            $path = $fileUploadService->uploadFileToProtected($file, 'uploads/payees');
            $url = asset('storage/' . $path);
        }

        return response()->json([
            'url' => $url,
            'file_url' => $url,
            'name' => $file->getClientOriginalName(),
            'size' => round($file->getSize() / (1024 * 1024), 2) . ' MB'
        ]);
    }

    public function viewFile(string $filename, FileUploadService $fileUploadService)
    {
        try {
            return $fileUploadService->readFileFromProtected($filename, 'uploads/payees');
        } catch (\Exception $e) {
            return response()->json(['message' => 'File not found on protected storage'], 404);
        }
    }
}
