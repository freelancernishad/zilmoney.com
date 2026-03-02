<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentLogController extends Controller
{
    public function index(Request $request, $paymentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);

        return response()->json($payment->logs()->latest()->get());
    }
}
