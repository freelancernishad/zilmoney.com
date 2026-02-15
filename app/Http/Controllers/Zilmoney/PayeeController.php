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
            'payee_name' => 'required|string',
            'email' => 'nullable|email',
            'type' => 'required|string', // vendor/customer/employee
        ]);

        $payee = $business->payees()->create($validated);

        return response()->json($payee, 201);
    }
}
