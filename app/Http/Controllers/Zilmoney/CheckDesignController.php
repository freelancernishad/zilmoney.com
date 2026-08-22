<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zilmoney\CheckDesign;
use App\Models\Zilmoney\Account;

class CheckDesignController extends Controller
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

    // List all designs for an account
    public function index(Request $request, $accountId)
    {
        $account = Account::find($accountId);
        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        if (!$this->checkOwnership($account, $request->user())) {
            return response()->json(['message' => 'Unauthorized account access'], 403);
        }

        $designs = $account->checkDesigns()->orderBy('id', 'desc')->get();

        return response()->json([
            'data' => $designs
        ]);
    }

    // Store a new check design
    public function store(Request $request, $accountId)
    {
        $account = Account::find($accountId);
        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        if (!$this->checkOwnership($account, $request->user())) {
            return response()->json(['message' => 'Unauthorized account access'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'custom_bg_url' => 'nullable|string',
            'positions' => 'required|array',
            'is_active' => 'nullable|boolean',
        ]);

        $isActive = filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN);

        // If marked active, or if this is the first design, make it active
        if ($isActive || $account->checkDesigns()->count() === 0) {
            $account->checkDesigns()->update(['is_active' => false]);
            $isActive = true;
        }

        $design = $account->checkDesigns()->create([
            'name' => $request->name,
            'custom_bg_url' => $request->custom_bg_url,
            'positions' => $request->positions,
            'is_active' => $isActive,
        ]);

        return response()->json([
            'message' => 'Check design created successfully',
            'data' => $design
        ], 201);
    }

    // Update an existing design
    public function update(Request $request, $accountId, $designId)
    {
        $account = Account::find($accountId);
        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        if (!$this->checkOwnership($account, $request->user())) {
            return response()->json(['message' => 'Unauthorized account access'], 403);
        }

        $design = CheckDesign::where('account_id', $accountId)->find($designId);
        if (!$design) {
            return response()->json(['message' => 'Check design not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'custom_bg_url' => 'nullable|string',
            'positions' => 'sometimes|required|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->has('name')) {
            $design->name = $request->name;
        }
        if ($request->has('custom_bg_url')) {
            $design->custom_bg_url = $request->custom_bg_url;
        }
        if ($request->has('positions')) {
            $design->positions = $request->positions;
        }
        if ($request->has('is_active')) {
            $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            if ($isActive) {
                $account->checkDesigns()->update(['is_active' => false]);
            }
            $design->is_active = $isActive;
        }

        $design->save();

        return response()->json([
            'message' => 'Check design updated successfully',
            'data' => $design
        ]);
    }

    // Toggle active state
    public function setActive(Request $request, $accountId, $designId)
    {
        $account = Account::find($accountId);
        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        if (!$this->checkOwnership($account, $request->user())) {
            return response()->json(['message' => 'Unauthorized account access'], 403);
        }

        $design = CheckDesign::where('account_id', $accountId)->find($designId);
        if (!$design) {
            return response()->json(['message' => 'Check design not found'], 404);
        }

        // Deactivate all others
        $account->checkDesigns()->update(['is_active' => false]);

        $design->is_active = true;
        $design->save();

        return response()->json([
            'message' => 'Check design set as active',
            'data' => $design
        ]);
    }

    // Delete a design
    public function destroy(Request $request, $accountId, $designId)
    {
        $account = Account::find($accountId);
        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        if (!$this->checkOwnership($account, $request->user())) {
            return response()->json(['message' => 'Unauthorized account access'], 403);
        }

        $design = CheckDesign::where('account_id', $accountId)->find($designId);
        if (!$design) {
            return response()->json(['message' => 'Check design not found'], 404);
        }

        $wasActive = $design->is_active;
        $design->delete();

        // If the active design was deleted, make the first available design active
        if ($wasActive) {
            $next = $account->checkDesigns()->first();
            if ($next) {
                $next->is_active = true;
                $next->save();
            }
        }

        return response()->json([
            'message' => 'Check design deleted successfully'
        ]);
    }
}
