<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    protected $bankingService;

    public function __construct(\App\Services\Zilmoney\BankingService $bankingService)
    {
        $this->bankingService = $bankingService;
    }

    public function index()
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json([]);

        return response()->json($business->accounts);
    }

    public function show($id)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $account = $business->accounts()->findOrFail($id);

        return response()->json($account);
    }

    public function validateRouting(Request $request)
    {
        $request->validate([
            'routing_number' => 'required|string|size:9',
        ]);

        try {
            $result = $this->bankingService->validateRoutingNumber($request->routing_number);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['valid' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        // Check Bank Account Creation Limit from User Active Plan
        $user = Auth::user();
        $activeSub = $user->planSubscriptions()->where('status', 'active')->latest('start_date')->first();
        $activePlan = $activeSub ? $activeSub->plan : \App\Models\Plan\Plan::find(1);

        $maxAllowed = 1;
        if ($activePlan && is_array($activePlan->features)) {
            foreach ($activePlan->features as $feature) {
                if (($feature['label'] ?? '') === 'Bank Accounts Allowed') {
                    $maxAllowed = (int) ($feature['value'] ?? 1);
                    break;
                }
            }
        }

        $currentAccountsCount = $business->accounts()->count();
        if ($currentAccountsCount >= $maxAllowed) {
            $planName = $activePlan->name ?? 'Current Plan';
            return response()->json([
                'message' => "Bank account creation limit reached ({$maxAllowed} allowed on your {$planName}). Please upgrade your plan to add more bank accounts."
            ], 403);
        }

        $validated = $request->validate([
            'account_holder_name' => 'required|string',
            'account_nick_name' => 'nullable|string',
            'account_number' => 'required|string',
            'routing_number' => 'required|string',
            'type' => 'required|string', // checking/savings
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'address_line1' => 'nullable|string',
            'address_line2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
            'next_check_starting_number' => 'nullable|integer',
            'ach_auth_form' => 'nullable|array',
        ]);

        $account = $business->accounts()->create($validated);

        return response()->json($account, 201);
    }

    public function update(Request $request, $id)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $account = $business->accounts()->findOrFail($id);

        $validated = $request->validate([
            'account_holder_name' => 'nullable|string',
            'account_nick_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'routing_number' => 'nullable|string',
            'type' => 'nullable|string',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'address_line1' => 'nullable|string',
            'address_line2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
            'next_check_starting_number' => 'nullable|integer',
            'ach_auth_form' => 'nullable|array',
        ]);

        $account->update($validated);

        return response()->json($account);
    }

    public function destroy($id)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $account = $business->accounts()->findOrFail($id);
        $account->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }

    public function syncBalance($id)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $account = $business->accounts()->findOrFail($id);

        if ($account->plaid_item_id) {
            $plaidItem = \App\Models\Zilmoney\PlaidItem::find($account->plaid_item_id);
            if ($plaidItem) {
                $plaidService = new \App\Services\Zilmoney\PlaidService();
                $plaidService->syncAccounts($plaidItem, $business->id);
                $account = $account->fresh();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Account balance synced with Plaid successfully.',
            'account' => $account
        ]);
    }
}
