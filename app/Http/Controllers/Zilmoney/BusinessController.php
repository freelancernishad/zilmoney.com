<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zilmoney\BusinessDetail;

class BusinessController extends Controller
{
    public function index()
    {
        // For now, assuming 1 user = 1 business as per TS structure, but returning array for flexibility
        $user = auth()->user();
        return response()->json($user->businessDetails);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'legal_business_name' => 'required|string',
            'email' => 'required|email',
            // Add other validation rules as needed
        ]);

        $business = auth()->user()->businessDetails()->create($validated);

        return response()->json($business, 201);
    }

    public function show($id)
    {
        $business = \App\Models\Zilmoney\BusinessDetail::with(['controllers', 'documents', 'accounts'])->findOrFail($id);
        
        // Ensure user owns this business
        if ($business->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($business);
    }

    public function update(Request $request, $id)
    {
        $business = \App\Models\Zilmoney\BusinessDetail::findOrFail($id);

        if ($business->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $business->update($request->all());

        return response()->json($business);
    }
}
