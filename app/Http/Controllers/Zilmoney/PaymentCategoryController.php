<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentCategoryController extends Controller
{
    public function index()
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json([]);

        $categories = \App\Models\Zilmoney\Category::where('company_id', $business->id)->latest()->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:Income,Expense',
        ]);

        $category = \App\Models\Zilmoney\Category::create([
            'company_id' => $business->id,
            'name' => $validated['name'],
            'type' => $validated['type'] ?? null,
        ]);

        return response()->json($category, 201);
    }

    public function destroy($id)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $category = \App\Models\Zilmoney\Category::where('company_id', $business->id)->findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }
}
