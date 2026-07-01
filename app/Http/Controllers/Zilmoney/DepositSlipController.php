<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zilmoney\DepositSlip;
use Barryvdh\DomPDF\Facade\Pdf;

class DepositSlipController extends Controller
{
    public function index(Request $request)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json([]);

        $query = DepositSlip::where('company_id', $business->id)->with('account');

        if ($request->filled('ref_id')) {
            $query->where('ref_id', 'like', '%' . $request->ref_id . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $allowedSorts = ['id', 'date', 'ref_id', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->input('per_page', 20);
        $depositSlips = $query->paginate($perPage);

        return response()->json($depositSlips);
    }

    public function store(Request $request)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'deposit_from' => 'nullable|string|max:255',
            'date' => 'required|date',
            'ref_id' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            'blank_deposit_slip' => 'nullable|boolean',
            'cash_items' => 'nullable|array',
            'cash_items.*.amount' => 'required|numeric|min:0',
            'cash_items.*.cashier_clerk' => 'nullable|string',
            'cash_items.*.note' => 'nullable|string',
            'check_items' => 'nullable|array',
            'check_items.*.amount' => 'required|numeric|min:0',
            'check_items.*.from' => 'nullable|string',
            'check_items.*.check_number' => 'nullable|string',
            'check_items.*.cashier_clerk' => 'nullable|string',
            'check_items.*.note' => 'nullable|string',
        ]);

        if (!$business->accounts()->where('id', $validated['account_id'])->exists()) {
            return response()->json(['message' => 'Invalid account'], 403);
        }

        $validated['company_id'] = $business->id;
        $validated['blank_deposit_slip'] = $request->input('blank_deposit_slip', false);

        $depositSlip = DepositSlip::create($validated);

        return response()->json($depositSlip, 201);
    }

    public function show($id)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $depositSlip = DepositSlip::where('company_id', $business->id)->with('account')->findOrFail($id);

        return response()->json($depositSlip);
    }

    public function destroy($id)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $depositSlip = DepositSlip::where('company_id', $business->id)->findOrFail($id);
        $depositSlip->delete();

        return response()->json(['message' => 'Deposit slip deleted successfully']);
    }

    public function downloadPdf($id)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $depositSlip = DepositSlip::where('company_id', $business->id)->with(['account', 'businessDetail'])->findOrFail($id);

        // Calculate totals
        $cashTotal = 0;
        if ($depositSlip->cash_items) {
            foreach ($depositSlip->cash_items as $item) {
                $cashTotal += (float) ($item['amount'] ?? 0);
            }
        }

        $checkTotal = 0;
        if ($depositSlip->check_items) {
            foreach ($depositSlip->check_items as $item) {
                $checkTotal += (float) ($item['amount'] ?? 0);
            }
        }

        $totalAmount = $cashTotal + $checkTotal;

        $pdf = Pdf::loadView('zilmoney.pdf.deposit-template', [
            'deposit' => $depositSlip,
            'cashTotal' => $cashTotal,
            'checkTotal' => $checkTotal,
            'totalAmount' => $totalAmount,
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream("deposit_slip_{$depositSlip->ref_id}.pdf");
    }
}
