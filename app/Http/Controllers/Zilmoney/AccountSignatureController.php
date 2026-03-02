<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zilmoney\AccountSignature;
use App\Models\Zilmoney\Account;
use App\Http\Requests\Zilmoney\StoreAccountSignatureRequest;

class AccountSignatureController extends Controller
{
    // Fetch signatures for an account
    public function index(Request $request, $accountId)
    {
        $account = Account::with('signatures')->findOrFail($accountId);

        // Ownership check
        if ($account->company->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
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
        if ($account->company->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
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

    // Set a specific signature as primary
    public function setPrimary(Request $request, $id)
    {
        $signature = AccountSignature::findOrFail($id);
        $account = $signature->account;

        // Ownership check
        if ($account->company->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Reset others to false
        $account->signatures()->update(['is_primary' => false]);

        // Mark selected as true
        $signature->is_primary = true;
        $signature->save();

        return response()->json([
            'message' => 'Primary signature updated successfully',
            'data' => $signature
        ]);
    }

    // Delete a signature
    public function destroy(Request $request, $id)
    {
        $signature = AccountSignature::findOrFail($id);
        $account = $signature->account;

        // Ownership check
        if ($account->company->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $signature->delete();

        return response()->json([
            'message' => 'Signature deleted successfully'
        ]);
    }
}
