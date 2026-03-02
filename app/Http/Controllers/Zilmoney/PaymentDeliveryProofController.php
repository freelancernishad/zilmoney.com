<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentDeliveryProofController extends Controller
{
    public function index(Request $request, $paymentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);

        return response()->json($payment->deliveryProofs()->with('user')->get());
    }

    public function store(Request $request, $paymentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);

        $validated = $request->validate([
            'file_path' => 'required|url',
            'original_name' => 'nullable|string|max:255'
        ]);

        $deliveryProof = $payment->deliveryProofs()->create([
            'user_id' => auth()->id(),
            'file_path' => $validated['file_path'],
            'original_name' => $validated['original_name']
        ]);

        return response()->json($deliveryProof->load('user'), 201);
    }

    public function destroy(Request $request, $paymentId, $deliveryProofId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);
        $deliveryProof = $payment->deliveryProofs()->findOrFail($deliveryProofId);

        $deliveryProof->delete();

        return response()->json(['message' => 'Delivery Proof deleted successfully']);
    }
}
