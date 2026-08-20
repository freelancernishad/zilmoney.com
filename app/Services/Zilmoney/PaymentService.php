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

            // 1. Check Number Logic (Auto-increment to next unique check number)
            $checkNumber = (!empty($data['check_number']) && is_numeric($data['check_number']))
                ? (int)$data['check_number']
                : $this->getNextCheckNumber($account);
            
            // If check number already exists, automatically auto-increment to the next unique check number
            if (Payment::where('account_id', $account->id)->where('check_number', $checkNumber)->exists()) {
                $checkNumber = $this->getNextCheckNumber($account);
            }

            // 3. Capture current active signature, company info, and bank account snapshot
            $includeSig = array_key_exists('include_signature', $data) ? (bool)$data['include_signature'] : true;
            if (!$includeSig) {
                $sigImage = 'NO_SIGNATURE';
                $sigImageUrl = 'NO_SIGNATURE';
            } else {
                $activeSig = $account->activeSignature;
                $sigImage = $activeSig ? $activeSig->path : null;
                $sigImageUrl = $activeSig ? $activeSig->image_url : null;
            }

            $issueDate = array_key_exists('issue_date', $data) ? $data['issue_date'] : null;

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

            // Derive Company/Payer Name, Address & Logo: Account info first, Business info as fallback
            $accountHolderName = trim($account->account_holder_name ?? '');
            $companyName = !empty($accountHolderName) ? $accountHolderName : ($business->legal_business_name ?? $business->dba);

            $accountAddrParts = array_filter([
                $account->address_line1 ?? '',
                $account->address_line2 ?? '',
                $account->city ?? '',
                isset($account->state) ? $account->state . " " . ($account->postal_code ?? '') : ($account->postal_code ?? ''),
                $account->country ?? ''
            ]);
            $accountAddrStr = !empty($accountAddrParts) ? implode(', ', $accountAddrParts) : null;
            $companyAddress = !empty($accountAddrStr) ? $accountAddrStr : $addrStr;

            $accountLogo = !empty($account->company_logo_url) ? $account->company_logo_url : null;
            $businessLogo = !empty($business->company_logo_url) 
                ? $business->company_logo_url 
                : get_file_url($business->verification_photo_id);
            $companyLogoUrl = !empty($accountLogo) ? $accountLogo : $businessLogo;

            $accountWebsite = !empty($account->website) ? $account->website : null;
            $businessWebsite = !empty($business->website) ? $business->website : ($data['website'] ?? null);
            $companyWebsite = !empty($accountWebsite) ? $accountWebsite : $businessWebsite;

            $processWithoutData = $data['process_without'] ?? ($data['delivery_proof']['process_without'] ?? []);

            $deliveryProof = array_merge([
                'include_signature' => $includeSig,
                'without_amount' => !empty($processWithoutData['amount']) || (array_key_exists('amount', $data) && $data['amount'] == 0),
                'without_sign' => !$includeSig || !empty($processWithoutData['sign']),
                'without_date' => empty($data['issue_date']) || !empty($processWithoutData['date']),
                'without_payee' => empty($data['payee_id']) || !empty($processWithoutData['payee']),
                'process_without' => [
                    'amount' => !empty($processWithoutData['amount']),
                    'sign' => !$includeSig || !empty($processWithoutData['sign']),
                    'date' => empty($data['issue_date']) || !empty($processWithoutData['date']),
                    'payee' => empty($data['payee_id']) || !empty($processWithoutData['payee']),
                ]
            ], is_array($data['delivery_proof'] ?? null) ? $data['delivery_proof'] : []);

            // 4. Create Payment with full snapshot details
            $payment = Payment::create([
                'unique_check_id' => Payment::generateUniqueCheckId(),
                'company_id' => $business->id,


                'account_id' => $account->id,
                'payee_id' => $data['payee_id'] ?? null,
                'amount' => $data['amount'] ?? 0,
                'check_number' => $checkNumber,
                'pay_as' => $data['pay_as'] ?? 'Check',
                'status' => $data['status'] ?? 'pending', // Pending or sent
                'issue_date' => $issueDate,
                'memo' => $data['memo'] ?? null,
                'signature_image' => $sigImage,
                'signature_image_url' => $sigImageUrl,
                'company_name' => $companyName,
                'company_address' => $companyAddress,
                'company_logo_url' => $companyLogoUrl,
                'business_website' => $companyWebsite,
                'bank_name' => $account->bank_name,
                'bank_routing_number' => $account->routing_number,
                'bank_account_number' => $account->account_number,
                'delivery_proof' => $deliveryProof,
                'process_without' => [
                    'amount' => !empty($processWithoutData['amount']) || (array_key_exists('amount', $data) && $data['amount'] == 0),
                    'sign' => !$includeSig || !empty($processWithoutData['sign']),
                    'date' => empty($data['issue_date']) || !empty($processWithoutData['date']),
                    'payee' => empty($data['payee_id']) || !empty($processWithoutData['payee']),
                ],
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
