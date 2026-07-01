<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zilmoney\Payment;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(\App\Services\Zilmoney\PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json([]);

        $query = $business->payments()->with(['payee', 'account', 'logs.initiator']);

        // Tab filters (Check, ACH, Wire, Virtual Card, Recurring)
        if ($request->filled('tab')) {
            $tab = strtolower($request->tab);
            if ($tab === 'check') {
                $query->where('pay_as', 'Check');
            } elseif ($tab === 'ach') {
                $query->where('pay_as', 'like', '%ach%');
            } elseif ($tab === 'wire') {
                $query->where('pay_as', 'Wire');
            } elseif ($tab === 'virtual card') {
                $query->where('pay_as', 'Virtual Card');
            }
        }

        // Granular filters
        if ($request->filled('payee_id')) {
            $query->where('payee_id', $request->payee_id);
        }
        if ($request->filled('amount')) {
            $query->where('amount', 'like', '%' . $request->amount . '%');
        }
        if ($request->filled('memo')) {
            $query->where('memo', 'like', '%' . $request->memo . '%');
        }
        if ($request->filled('check_number')) {
            $query->where('check_number', 'like', '%' . $request->check_number . '%');
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // General search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('memo', 'like', "%{$search}%")
                  ->orWhere('check_number', 'like', "%{$search}%")
                  ->orWhereHas('payee', function($qp) use ($search) {
                      $qp->where('payee_name', 'like', "%{$search}%")
                        ->orWhere('nick_name', 'like', "%{$search}%");
                  });
            });
        }

        // Date filters
        if ($request->filled('created_at')) {
            $query->whereDate('created_at', $request->created_at);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $allowedSorts = ['id', 'amount', 'check_number', 'issue_date', 'created_at', 'updated_at', 'status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->input('per_page', 20);
        $payments = $query->paginate($perPage);

        return response()->json($payments);
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

        if (!$business->accounts()->where('id', $validated['account_id'])->exists()) {
            return response()->json(['message' => 'Invalid account'], 403);
        }

        try {
            $payment = $this->paymentService->createPayment($validated, $business);

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

        $payment = $business->payments()->with(['payee', 'account', 'logs.initiator', 'comments.user', 'attachments'])->findOrFail($id);

        return response()->json($payment);
    }

    public function update(Request $request, $id)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($id);

        $validated = $request->validate([
            'account_id' => 'nullable|exists:accounts,id',
            'payee_id' => 'nullable|exists:payees,id',
            'amount' => 'nullable|numeric|min:0.01',
            'issue_date' => 'nullable|date',
            'check_number' => 'nullable|integer',
            'memo' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
        ]);

        try {
            \DB::transaction(function () use ($payment, $validated) {
                $account = $payment->account;

                if (isset($validated['amount']) && $validated['amount'] != $payment->amount) {
                    $account->increment('balance', $payment->amount);
                    if ($account->fresh()->balance < $validated['amount']) {
                        throw new \Exception("Insufficient funds on account.");
                    }
                    $account->decrement('balance', $validated['amount']);
                }

                if (isset($validated['status']) && in_array(strtolower($validated['status']), ['void', 'failed']) && !in_array(strtolower($payment->status), ['void', 'failed'])) {
                    $account->increment('balance', $payment->amount);
                } elseif (isset($validated['status']) && !in_array(strtolower($validated['status']), ['void', 'failed']) && in_array(strtolower($payment->status), ['void', 'failed'])) {
                    if ($account->balance < $payment->amount) {
                        throw new \Exception("Insufficient funds to reactivate payment.");
                    }
                    $account->decrement('balance', $payment->amount);
                }

                $payment->update($validated);
            });

            return response()->json($payment);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($id);

        \DB::transaction(function () use ($payment) {
            if (!in_array(strtolower($payment->status), ['void', 'failed'])) {
                $account = $payment->account;
                if ($account) {
                    $account->increment('balance', $payment->amount);
                }
            }
            $payment->delete();
        });

        return response()->json(['message' => 'Payment deleted successfully']);
    }

    public function bulkAction(Request $request)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:company_payments,id',
            'action' => 'required|string|in:delete,void,print',
        ]);

        $payments = $business->payments()->whereIn('id', $validated['ids'])->get();

        if ($validated['action'] === 'delete') {
            \DB::transaction(function () use ($payments) {
                foreach ($payments as $payment) {
                    if (!in_array(strtolower($payment->status), ['void', 'failed'])) {
                        $account = $payment->account;
                        if ($account) {
                            $account->increment('balance', $payment->amount);
                        }
                    }
                    $payment->delete();
                }
            });
            return response()->json(['message' => 'Payments deleted successfully']);
        } elseif ($validated['action'] === 'void') {
            \DB::transaction(function () use ($payments) {
                foreach ($payments as $payment) {
                    if (!in_array(strtolower($payment->status), ['void', 'failed'])) {
                        $account = $payment->account;
                        if ($account) {
                            $account->increment('balance', $payment->amount);
                        }
                        $payment->update(['status' => 'Void']);
                    }
                }
            });
            return response()->json(['message' => 'Payments voided successfully']);
        } elseif ($validated['action'] === 'print') {
            return response()->json(['message' => 'Printing processed']);
        }

        return response()->json(['message' => 'Invalid bulk action'], 400);
    }

    public function downloadPdf($id, \App\Services\Zilmoney\CheckService $checkService)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->with(['business', 'payee', 'account'])->findOrFail($id);

        $pdf = $checkService->generateCheckPdf($payment);

        return $pdf->stream("check_{$payment->check_number}.pdf");
    }
}
