<?php

namespace App\Services\Zilmoney;

use Illuminate\Support\Facades\Http;
use Exception;

class BankingService
{
    /**
     * Validate a US Routing Number.
     * Checks format, checksum, and optionally fetches bank details.
     */
    public function validateRoutingNumber($routingNumber)
    {
        // 1. Basic Format Validation
        if (!preg_match('/^\d{9}$/', $routingNumber)) {
            throw new Exception("Routing number must be exactly 9 digits.");
        }

        // 2. Checksum Validation (ABA Routing Number Check Digit)
        if (!$this->isValidABAChecksum($routingNumber)) {
             throw new Exception("Invalid routing number checksum.");
        }

        // 3. External Lookup (Optional - using free open public API)
        // Note: Using routingnumbers.info as an example. 
        // In production, you might want a paid/more robust provider or local DB.
        try {
            $response = Http::get("https://www.routingnumbers.info/api/data.json", [
                'rn' => $routingNumber,
            ]);

            if ($response->successful() && $response->json('code') === 200) {
                return [
                    'valid' => true,
                    'bank_name' => $response->json('customer_name'),
                    'location' => $response->json('address') . ', ' . $response->json('city') . ', ' . $response->json('state'),
                    'message' => 'Valid routing number.',
                ];
            }
        } catch (Exception $e) {
            // Fallback if API fails, but checksum passed
            \Log::warning("Routing number lookup failed: " . $e->getMessage());
        }

        return [
            'valid' => true,
            'bank_name' => 'Unknown Bank (Checksum Valid)',
            'location' => null,
            'message' => 'Routing number is valid, but bank details could not be fetched.',
        ];
    }

    /**
     * Validate ABA Routing Number Checksum
     * Formula: 3(d1 + d4 + d7) + 7(d2 + d5 + d8) + (d3 + d6 + d9) mod 10 = 0
     */
    private function isValidABAChecksum($routingNumber)
    {
        $d = str_split($routingNumber);
        $sum = 
            3 * ($d[0] + $d[3] + $d[6]) +
            7 * ($d[1] + $d[4] + $d[7]) +
            1 * ($d[2] + $d[5] + $d[8]);

        return ($sum % 10) === 0;
    }
}
