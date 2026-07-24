<?php

namespace App\Services\Zilmoney;

use App\Models\Zilmoney\Account;
use App\Models\Zilmoney\Payment;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    /**
     * Process a new payment (Check/ACH) with validation.
     */
    public function createPayment(array $data, $business)
    {
        return DB::transaction(function () use ($data, $business) {
            $account = Account::lockForUpdate()->find($data['account_id']);

            if (!$account) {
                throw new Exception("Account not found.");
            }

            // 1. Balance Validation
            if ($account->balance < $data['amount']) {
                throw new Exception("Insufficient funds. Available: {$account->balance}");
            }

            // 2. Check Number Logic
            $checkNumber = $data['check_number'] ?? $this->getNextCheckNumber($account);
            
            // Validate Check Number Uniqueness if provided manually
            if (Payment::where('account_id', $account->id)->where('check_number', $checkNumber)->exists()) {
                throw new Exception("Check number {$checkNumber} already exists.");
            }

            // 3. Capture current active signature, company info, and bank account snapshot
            $activeSig = $account->activeSignature;
            $sigImage = $activeSig ? $activeSig->path : null;
            $sigImageUrl = $activeSig ? $activeSig->image_url : null;

            $addr = $business->physical_address;
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

            // 4. Create Payment with full snapshot details
            $payment = Payment::create([
                'company_id' => $business->id,
                'account_id' => $account->id,
                'payee_id' => $data['payee_id'],
                'amount' => $data['amount'],
                'check_number' => $checkNumber,
                'status' => 'pending', // Pending approval/processing
                'issue_date' => $data['issue_date'],
                'memo' => $data['memo'] ?? null,
                'signature_image' => $sigImage,
                'signature_image_url' => $sigImageUrl,
                'company_name' => $business->legal_business_name ?? $business->dba,
                'company_address' => $addrStr,
                'company_logo_url' => get_file_url($business->verification_photo_id),
                'bank_name' => $account->bank_name,
                'bank_routing_number' => $account->routing_number,
                'bank_account_number' => $account->account_number,
            ]);

            // 4. Update Balance (Deduct immediately or on processing? Assuming immediate for now)
            // In a real system, might be 'reserved' balance.
            $account->decrement('balance', $data['amount']);

            return $payment;
        });
    }

    private function getNextCheckNumber(Account $account)
    {
        $lastPayment = Payment::where('account_id', $account->id)
            ->whereNotNull('check_number')
            ->orderByRaw('CAST(check_number AS UNSIGNED) DESC') // Handle string check numbers
            ->first();

        return $lastPayment ? ($lastPayment->check_number + 1) : 1001; // Start at 1001
    }
}
