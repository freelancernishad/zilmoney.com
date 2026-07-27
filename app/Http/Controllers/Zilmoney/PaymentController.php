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
            'payee_id' => 'nullable|exists:payees,id',
            'pay_from' => 'required|string|in:Bank Account,Credit Card,Wallet,Cloud Bank',
            'pay_as' => 'required|string|in:Check,ACH / Direct Deposit,Wire,Virtual Card,Real Time Instant Payment,Same Day ACH,International Payment',
            'amount' => 'required|numeric|min:0',
            'issue_date' => 'nullable|date',
            'check_number' => 'nullable|integer',
            'invoice_number' => 'nullable|string|max:255',
            'payee_id_account_number' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:company_payment_categories,id',
            'memo' => 'nullable|string|max:255',
            'include_signature' => 'nullable|boolean',
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

        $payment = $business->payments()->with(['payee', 'account', 'logs.initiator', 'comments.user', 'attachments', 'business'])->findOrFail($id);

        // Auto-sync & backfill missing snapshot fields for legacy checks using raw DB columns
        $dirty = false;

        $rawSigUrl = $payment->getRawOriginal('signature_image_url');
        $rawSigPath = $payment->getRawOriginal('signature_image');

        if ($rawSigUrl !== 'NO_SIGNATURE' && $rawSigPath !== 'NO_SIGNATURE' && empty($rawSigUrl) && empty($rawSigPath) && $payment->account) {
            $activeSig = $payment->account->activeSignature;
            if ($activeSig) {
                $payment->signature_image = $activeSig->path;
                $payment->signature_image_url = $activeSig->image_url;
                $dirty = true;
            }
        }

        if (empty($payment->getRawOriginal('company_name')) && $payment->business) {
            $payment->company_name = $payment->business->legal_business_name ?? $payment->business->dba;
            $dirty = true;
        }

        if (empty($payment->getRawOriginal('company_address')) && $payment->business) {
            $addr = $payment->business->physical_address;
            $addrStr = null;
            if (is_array($addr)) {
                $parts = array_filter([
                    $addr['address1'] ?? '',
                    $addr['city'] ?? '',
                    isset($addr['state']) ? $addr['state'] . " " . ($addr['zip'] ?? '') : ''
                ]);
                $addrStr = implode(', ', $parts);
            } elseif (is_string($addr)) {
                $addrStr = $addr;
            }
            $payment->company_address = $addrStr;
            $dirty = true;
        }

        if (empty($payment->getRawOriginal('company_logo_url')) && $payment->business) {
            $payment->company_logo_url = get_file_url($payment->business->verification_photo_id);
            $dirty = true;
        }

        if (empty($payment->getRawOriginal('bank_name')) && $payment->account) {
            $payment->bank_name = $payment->account->bank_name;
            $payment->bank_routing_number = $payment->account->routing_number;
            $payment->bank_account_number = $payment->account->account_number;
            $dirty = true;
        }

        if ($dirty) {
            $payment->save();
        }

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

    public function downloadPdf(Request $request, $id, \App\Services\Zilmoney\CheckService $checkService)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->with(['business', 'payee', 'account'])->findOrFail($id);

        // Audit Log for check printing compliance
        $payment->logs()->create([
            'status' => $payment->status,
            'initiated_by' => auth()->id(),
            'note' => 'Check PDF printed / downloaded',
            'device_info' => request()->ip()
        ]);

        if ($payment->status === 'pending') {
            $payment->update(['status' => 'printed']);
        }

        $pdf = $checkService->generateCheckPdf($payment, $request->all());

        return $pdf->stream("check_{$payment->check_number}.pdf");
    }

    public function storeBlankChecks(Request $request)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'number_of_checks' => 'required|integer|min:1|max:100',
            'category_id' => 'nullable|exists:company_payment_categories,id',
            'include_signature' => 'nullable|string|in:Yes,No',
            'memo' => 'nullable|string|max:255',
        ]);

        $account = \App\Models\Zilmoney\Account::find($validated['account_id']);
        if (!$business->accounts()->where('id', $account->id)->exists()) {
            return response()->json(['message' => 'Invalid account'], 403);
        }

        $createdPayments = [];
        $includeSignature = ($request->input('include_signature', 'Yes') === 'Yes');

        \DB::transaction(function () use ($validated, $account, $business, $includeSignature, &$createdPayments) {
            $startCheckNo = $this->getNextCheckNumber($account);

            for ($i = 0; $i < $validated['number_of_checks']; $i++) {
                $checkNo = $startCheckNo + $i;

                $payment = Payment::create([
                    'company_id' => $business->id,
                    'account_id' => $account->id,
                    'payee_id' => null,
                    'pay_from' => 'Bank Account',
                    'pay_as' => 'Check',
                    'amount' => 0.00,
                    'status' => 'Blank',
                    'issue_date' => now()->format('Y-m-d'),
                    'check_number' => $checkNo,
                    'category_id' => $validated['category_id'] ?? null,
                    'memo' => $validated['memo'] ?? 'Blank Check',
                    'delivery_proof' => $includeSignature ? ['include_signature' => true] : ['include_signature' => false],
                ]);

                $payment->logs()->create([
                    'status' => 'Blank',
                    'initiated_by' => auth()->id(),
                    'note' => 'Blank check created',
                    'device_info' => request()->ip()
                ]);

                $createdPayments[] = $payment;
            }
        });

        return response()->json($createdPayments, 201);
    }

    public function bulkStore(Request $request)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $validated = $request->validate([
            'payments' => 'required|array',
            'payments.*.account_id' => 'required|exists:accounts,id',
            'payments.*.payee_id' => 'nullable|exists:payees,id',
            'payments.*.pay_from' => 'required|string',
            'payments.*.pay_as' => 'required|string',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.issue_date' => 'required|date',
            'payments.*.check_number' => 'nullable|integer',
            'payments.*.invoice_number' => 'nullable|string|max:255',
            'payments.*.payee_id_account_number' => 'nullable|string|max:255',
            'payments.*.category_id' => 'nullable|exists:company_payment_categories,id',
            'payments.*.memo' => 'nullable|string|max:255',
        ]);

        $createdPayments = [];

        \DB::transaction(function () use ($validated, $business, &$createdPayments) {
            foreach ($validated['payments'] as $paymentData) {
                $account = \App\Models\Zilmoney\Account::find($paymentData['account_id']);
                
                $checkNumber = $paymentData['check_number'] ?? null;
                if (empty($checkNumber) && strtolower($paymentData['pay_as']) === 'check') {
                    $checkNumber = $this->getNextCheckNumber($account);
                }

                $payment = Payment::create([
                    'company_id' => $business->id,
                    'account_id' => $account->id,
                    'payee_id' => $paymentData['payee_id'] ?? null,
                    'pay_from' => $paymentData['pay_from'],
                    'pay_as' => $paymentData['pay_as'],
                    'amount' => $paymentData['amount'],
                    'status' => 'pending',
                    'issue_date' => $paymentData['issue_date'],
                    'check_number' => $checkNumber,
                    'invoice_number' => $paymentData['invoice_number'] ?? null,
                    'payee_id_account_number' => $paymentData['payee_id_account_number'] ?? null,
                    'category_id' => $paymentData['category_id'] ?? null,
                    'memo' => $paymentData['memo'] ?? null,
                ]);

                if ($paymentData['amount'] > 0) {
                    $account->decrement('balance', $paymentData['amount']);
                }

                $payment->logs()->create([
                    'status' => 'pending',
                    'initiated_by' => auth()->id(),
                    'note' => 'Payment created via bulk store',
                    'device_info' => request()->ip()
                ]);

                $createdPayments[] = $payment;
            }
        });

        return response()->json($createdPayments, 201);
    }

    private function getNextCheckNumber(\App\Models\Zilmoney\Account $account)
    {
        $lastPayment = Payment::where('account_id', $account->id)
            ->whereNotNull('check_number')
            ->orderByRaw('CAST(check_number AS UNSIGNED) DESC')
            ->first();

        return $lastPayment ? ($lastPayment->check_number + 1) : 1001;
    }


    public function voidPayment($id)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = Payment::where('company_id', $business->id)->findOrFail($id);
        $payment->update(['status' => 'voided']);

        $payment->logs()->create([
            'status' => 'voided',
            'initiated_by' => auth()->id(),
            'note' => 'Payment check marked as VOID',
            'device_info' => request()->ip()
        ]);

        return response()->json([
            'message' => 'Check marked as VOID successfully.',
            'payment' => $payment
        ]);
    }

    public function sendEmail(Request $request, $id)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->with(['payee', 'account', 'comments', 'business'])->findOrFail($id);

        $payeeEmail = $request->input('email') 
            ?? $payment->payee->email 
            ?? $payment->payee->email_address;

        $ownerEmail = auth()->user()->email;
        $ownerName = auth()->user()->name ?? 'Account Owner';

        // Fallback if payee has no email
        $recipientPayeeEmail = $payeeEmail ?: $ownerEmail;

        $payeeName = $payment->payee->payee_name ?? $payment->payee->nick_name ?? 'Valued Customer';
        $payorName = $payment->company_name ?? $business->legal_business_name ?? $business->dba ?? 'Demo Bank Account 1';
        $amount = $payment->amount;
        $checkNumber = $payment->check_number;
        $memo = $payment->memo ?? '';
        $dateProcessed = $payment->issue_date 
            ? \Carbon\Carbon::parse($payment->issue_date)->format('F j, Y') 
            : \Carbon\Carbon::parse($payment->created_at)->format('F j, Y');

        $latestComment = $payment->comments->last()?->comment ?? '';

        $printUrl = url("/dashboard/payments/{$payment->id}/print");
        $trackUrl = url("/dashboard/payments");
        $loginUrl = url("/login");

        $subjectPayee = "Zil Money: {$payorName} has sent you an E-check";
        $subjectOwner = "Zil Money: Payment Confirmation - E-check sent to {$payeeName}";

        try {
            // 1. Send Primary E-check email to Payee
            \Illuminate\Support\Facades\Mail::send('zilmoney.emails.echeck', [
                'payeeName' => $payeeName,
                'payorName' => $payorName,
                'amount' => $amount,
                'checkNumber' => $checkNumber,
                'memo' => $memo,
                'dateProcessed' => $dateProcessed,
                'comment' => $latestComment,
                'printUrl' => $printUrl,
                'trackUrl' => $trackUrl,
                'loginUrl' => $loginUrl,
            ], function ($message) use ($recipientPayeeEmail, $subjectPayee) {
                $message->to($recipientPayeeEmail)
                        ->subject($subjectPayee);
            });

            // 2. Send Payment Confirmation Receipt to Owner (if owner email is available)
            if ($ownerEmail) {
                try {
                    \Illuminate\Support\Facades\Mail::send('zilmoney.emails.owner-receipt', [
                        'ownerName' => $ownerName,
                        'payeeName' => $payeeName,
                        'payeeEmail' => $recipientPayeeEmail,
                        'payorName' => $payorName,
                        'amount' => $amount,
                        'checkNumber' => $checkNumber,
                        'memo' => $memo,
                        'dateProcessed' => $dateProcessed,
                        'trackUrl' => $trackUrl,
                    ], function ($message) use ($ownerEmail, $subjectOwner) {
                        $message->to($ownerEmail)
                                ->subject($subjectOwner);
                    });
                } catch (\Exception $ownerMailEx) {
                    // Ignore minor secondary email issues
                }
            }

            // Update payment status to sent in DB so it shows under Mailed filter
            $payment->update(['status' => 'sent']);

            // Log activity
            $payment->logs()->create([
                'status' => 'sent',
                'initiated_by' => auth()->id(),
                'note' => "E-check email sent to Payee ({$recipientPayeeEmail}) and Confirmation Receipt to Owner ({$ownerEmail})",
                'device_info' => $request->userAgent()
            ]);

            return response()->json([
                'message' => "E-check sent to {$recipientPayeeEmail} & Receipt sent to {$ownerEmail}!",
                'payee_email' => $recipientPayeeEmail,
                'owner_email' => $ownerEmail
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "E-check sent to {$recipientPayeeEmail} successfully!",
                'payee_email' => $recipientPayeeEmail,
                'owner_email' => $ownerEmail
            ], 200);
        }
    }
}
