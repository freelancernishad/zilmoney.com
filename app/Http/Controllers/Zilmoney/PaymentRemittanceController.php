<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentRemittanceController extends Controller
{
    public function index(Request $request, $paymentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);

        return response()->json($payment->remittances()->with('user')->get());
    }

    public function store(Request $request, $paymentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);

        $validated = $request->validate([
            'invoice_number' => 'nullable|string|max:255',
            'item' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'quantity' => 'nullable|integer',
            'unit_cost' => 'nullable|numeric',
            'total' => 'nullable|numeric'
        ]);

        $remittance = $payment->remittances()->create([
            'user_id' => auth()->id(),
            'invoice_number' => $validated['invoice_number'] ?? null,
            'item' => $validated['item'] ?? null,
            'description' => $validated['description'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'unit_cost' => $validated['unit_cost'] ?? null,
            'total' => $validated['total'] ?? null,
        ]);

        return response()->json($remittance->load('user'), 201);
    }

    public function destroy(Request $request, $paymentId, $remittanceId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);
        $remittance = $payment->remittances()->findOrFail($remittanceId);

        $remittance->delete();

        return response()->json(['message' => 'Remittance deleted successfully']);
    }
}
