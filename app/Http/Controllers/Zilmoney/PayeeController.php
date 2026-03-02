<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
            'payee_id_account_number' => 'required|integer',
            'entity_type' => 'required|in:individual,business',
            'company_name' => 'nullable|string',
            'address_line1' => 'nullable|string',
            'address_line2' => 'nullable|string',
            'country' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'bank_account' => 'nullable|array',
            'bank_account.account_holder_name' => 'nullable|string',
            'bank_account.routing_number' => 'nullable|string',
            'bank_account.account_number' => 'nullable|string',
            'bank_account.account_type' => 'nullable|string',
        ]);

        // Map TS nested bank_account object to flat table columns
        if (isset($validated['bank_account'])) {
            $validated['bank_account_holder_name'] = $validated['bank_account']['account_holder_name'] ?? null;
            $validated['bank_routing_number'] = $validated['bank_account']['routing_number'] ?? null;
            $validated['bank_account_number'] = $validated['bank_account']['account_number'] ?? null;
            $validated['bank_account_type'] = $validated['bank_account']['account_type'] ?? null;
            unset($validated['bank_account']);
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
            'payee_id_account_number' => 'nullable|integer',
            'entity_type' => 'nullable|in:individual,business',
            'company_name' => 'nullable|string',
            'address_line1' => 'nullable|string',
            'address_line2' => 'nullable|string',
            'country' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'bank_account' => 'nullable|array',
            'bank_account.account_holder_name' => 'nullable|string',
            'bank_account.routing_number' => 'nullable|string',
            'bank_account.account_number' => 'nullable|string',
            'bank_account.account_type' => 'nullable|string',
        ]);

        if (isset($validated['bank_account'])) {
            $validated['bank_account_holder_name'] = $validated['bank_account']['account_holder_name'] ?? null;
            $validated['bank_routing_number'] = $validated['bank_account']['routing_number'] ?? null;
            $validated['bank_account_number'] = $validated['bank_account']['account_number'] ?? null;
            $validated['bank_account_type'] = $validated['bank_account']['account_type'] ?? null;
            unset($validated['bank_account']);
        }

        $payee->update($validated);

        return response()->json($payee);
    }
}
