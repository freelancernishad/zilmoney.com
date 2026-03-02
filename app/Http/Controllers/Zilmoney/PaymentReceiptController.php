<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentReceiptController extends Controller
{
    public function index(Request $request, $paymentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);

        return response()->json($payment->receipts()->with('user')->get());
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

        $receipt = $payment->receipts()->create([
            'user_id' => auth()->id(),
            'file_path' => $validated['file_path'],
            'original_name' => $validated['original_name']
        ]);

        return response()->json($receipt->load('user'), 201);
    }

    public function destroy(Request $request, $paymentId, $receiptId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);
        $receipt = $payment->receipts()->findOrFail($receiptId);

        $receipt->delete();

        return response()->json(['message' => 'Receipt deleted successfully']);
    }
}
