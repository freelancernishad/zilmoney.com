<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zilmoney\AccountSignature;
use App\Models\Zilmoney\Account;
use App\Http\Requests\Zilmoney\StoreAccountSignatureRequest;

class AccountSignatureController extends Controller
{
    private function checkOwnership($account, $user)
    {
        if (!$account || !$user) return false;

        // Match user's active businessDetails company_id
        if ($user->businessDetails && (int)$account->company_id === (int)$user->businessDetails->id) {
            return true;
        }

        // Match company's user_id
        if ($account->company && (int)$account->company->user_id === (int)$user->id) {
            return true;
        }

        return false;
    }

    // Fetch signatures for an account
    public function index(Request $request, $accountId)
    {
        $account = Account::with('signatures')->find($accountId);
        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        // Ownership check
        if (!$this->checkOwnership($account, $request->user())) {
            return response()->json(['message' => 'Unauthorized account access'], 403);
        }

        return response()->json([
            'data' => $account->signatures
        ]);
    }

    // Store a new signature
    public function store(StoreAccountSignatureRequest $request)
    {
        $data = $request->validated();

        $account = Account::findOrFail($data['account_id']);

        // Ownership check
        if (!$this->checkOwnership($account, $request->user())) {
            return response()->json(['message' => 'Unauthorized account access'], 403);
        }

        // Handle Base64 signature image upload via FileUploadService
        if (preg_match('/^data:(.*?);base64,(.*)$/', $data['path'], $matches)) {
            $base64Data = base64_decode($matches[2]);
            $filename = "signatures/account_" . $account->id . "_" . time() . ".png";
            try {
                $fileService = app(\App\Services\FileSystem\FileUploadService::class);
                $data['path'] = $fileService->uploadContentToS3($base64Data, $filename);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $base64Data);
                $data['path'] = asset("storage/{$filename}");
            }
        }

        // Default to primary if first signature or requested
        if (!isset($data['is_primary'])) {
            $data['is_primary'] = $account->signatures()->count() === 0;
        }

        // If this signature is marked as primary, reset others
        if (!empty($data['is_primary'])) {
            $account->signatures()->update(['is_primary' => false]);
        }

        $signature = $account->signatures()->create($data);

        return response()->json([
            'message' => 'Signature added successfully',
            'data' => $signature
        ], 201);
    }

    // Set or unset a signature as primary / active
    public function setPrimary(Request $request, $id)
    {
        $signature = AccountSignature::findOrFail($id);
        $account = $signature->account;

        // Ownership check
        if (!$this->checkOwnership($account, $request->user())) {
            return response()->json(['message' => 'Unauthorized account access'], 403);
        }

        // Check if explicit is_primary boolean is passed or toggle if already primary
        if ($request->has('is_primary')) {
            $newPrimary = filter_var($request->input('is_primary'), FILTER_VALIDATE_BOOLEAN);
        } else {
            $newPrimary = !$signature->is_primary;
        }

        // Reset others to false
        $account->signatures()->update(['is_primary' => false]);

        // Save selected primary state
        $signature->is_primary = $newPrimary;
        $signature->save();

        return response()->json([
            'message' => $newPrimary ? 'Set as primary signature' : 'Primary signature deactivated',
            'data' => $signature
        ]);
    }

    // Delete a signature
    public function destroy(Request $request, $id)
    {
        $signature = AccountSignature::findOrFail($id);
        $account = $signature->account;

        // Ownership check
        if (!$this->checkOwnership($account, $request->user())) {
            return response()->json(['message' => 'Unauthorized account access'], 403);
        }

        $signature->delete();

        return response()->json([
            'message' => 'Signature deleted successfully'
        ]);
    }
}
