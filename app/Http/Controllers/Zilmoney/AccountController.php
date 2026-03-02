<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected $bankingService;

    public function __construct(\App\Services\Zilmoney\BankingService $bankingService)
    {
        $this->bankingService = $bankingService;
    }

    public function index()
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json([]);

        return response()->json($business->accounts);
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
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

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
        $business = auth()->user()->businessDetails;
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
}
