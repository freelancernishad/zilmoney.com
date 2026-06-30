<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Zilmoney\Card;

class CardController extends Controller
{
    public function index()
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json([]);

        return response()->json($business->cards);
    }

    public function show($id)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $card = $business->cards()->findOrFail($id);

        return response()->json($card);
    }

    public function store(Request $request)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $validated = $request->validate([
            'holder_name' => 'required|string|max:255',
            'type' => 'required|string|in:Virtual,Physical',
            'limit' => 'required|numeric|min:0',
            'limit_type' => 'required|string|in:Daily,Monthly',
            'number' => 'required|string|max:255',
            'expiry' => 'required|string|max:10',
            'status' => 'nullable|string|in:Active,Inactive',
        ]);

        $card = $business->cards()->create($validated);

        return response()->json($card, 201);
    }

    public function update(Request $request, $id)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $card = $business->cards()->findOrFail($id);

        $validated = $request->validate([
            'holder_name' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:Virtual,Physical',
            'limit' => 'nullable|numeric|min:0',
            'limit_type' => 'nullable|string|in:Daily,Monthly',
            'number' => 'nullable|string|max:255',
            'expiry' => 'nullable|string|max:10',
            'status' => 'nullable|string|in:Active,Inactive',
        ]);

        $card->update($validated);

        return response()->json($card);
    }

    public function destroy($id)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $card = $business->cards()->findOrFail($id);
        $card->delete();

        return response()->json(['message' => 'Card deleted successfully']);
    }
}
