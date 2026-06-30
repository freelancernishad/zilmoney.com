<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Zilmoney\Bill;

class BillController extends Controller
{
    public function index()
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json([]);

        return response()->json($business->bills);
    }

    public function show($id)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $bill = $business->bills()->findOrFail($id);

        return response()->json($bill);
    }

    public function store(Request $request)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $validated = $request->validate([
            'payee_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'nullable|string|max:255',
            'due_date' => 'required|date',
            'status' => 'nullable|string|in:Unpaid,Overdue,Paid',
        ]);

        $bill = $business->bills()->create($validated);

        return response()->json($bill, 201);
    }

    public function update(Request $request, $id)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $bill = $business->bills()->findOrFail($id);

        $validated = $request->validate([
            'payee_name' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0.01',
            'category' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|in:Unpaid,Overdue,Paid',
        ]);

        $bill->update($validated);

        return response()->json($bill);
    }

    public function destroy($id)
    {
        $business = Auth::user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $bill = $business->bills()->findOrFail($id);
        $bill->delete();

        return response()->json(['message' => 'Bill deleted successfully']);
    }
}
