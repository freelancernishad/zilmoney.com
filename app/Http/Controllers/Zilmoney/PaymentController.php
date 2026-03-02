<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zilmoney\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json([]);

        $payments = $business->payments()->with(['payee', 'account', 'logs'])->latest()->paginate(20);
        return response()->json($payments);
    }

    protected $paymentService;

    public function __construct(\App\Services\Zilmoney\PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function store(Request $request)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'payee_id' => 'required|exists:payees,id',
            'pay_from' => 'required|string|in:Bank Account,Credit Card,Wallet,Cloud Bank',
            'pay_as' => 'required|string|in:Check,ACH / Direct Deposit,Wire,Virtual Card,Real Time Instant Payment,Same Day ACH,International Payment',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'check_number' => 'nullable|integer',
            'invoice_number' => 'nullable|string|max:255',
            'payee_id_account_number' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:company_payment_categories,id',
            'memo' => 'nullable|string|max:255',
        ]);

        // Ensure account belongs to business
        if (!$business->accounts()->where('id', $validated['account_id'])->exists()) {
            return response()->json(['message' => 'Invalid account'], 403);
        }

        try {
            $payment = $this->paymentService->createPayment($validated, $business);

            // Log creation
            $payment->logs()->create([
                'status' => 'pending',
                'initiated_by' => auth()->id(),
                'note' => 'Payment created via API',
                'device_info' => $request->userAgent()
            ]);

            return response()->json($payment, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->with(['payee', 'account', 'logs', 'comments', 'attachments'])->findOrFail($id);

        return response()->json($payment);
    }

    public function downloadPdf($id, \App\Services\Zilmoney\CheckService $checkService)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        // Ensure payment belongs to user's business
        $payment = $business->payments()->with(['business', 'payee', 'account', 'bank'])->findOrFail($id);

        $pdf = $checkService->generateCheckPdf($payment);

        return $pdf->stream("check_{$payment->check_number}.pdf");
    }
}
