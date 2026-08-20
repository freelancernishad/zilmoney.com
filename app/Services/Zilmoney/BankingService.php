<?php

namespace App\Services\Zilmoney;

use Illuminate\Support\Facades\Http;
use Exception;

class BankingService
{
    /**
     * Validate a US Routing Number via ABA Checksum algorithm & Plaid Production API.
     * Pure API call logic only. Zero hardcoded data.
     */
    public function validateRoutingNumber($routingNumber)
    {
        // 1. Basic Format Validation (Must be 9 digits)
        if (!preg_match('/^\d{9}$/', $routingNumber)) {
            throw new Exception("Routing number must be exactly 9 digits.");
        }

        // 2. Checksum Validation (Official ABA Routing Check Digit Algorithm)
        if (!$this->isValidABAChecksum($routingNumber)) {
             throw new Exception("Invalid routing number checksum.");
        }

        // 3. Plaid Production / Sandbox API Institution Search
        try {
            $plaidDetails = $this->lookupPlaidInstitution($routingNumber);
            if ($plaidDetails && !empty($plaidDetails['bank_name'])) {
                return [
                    'valid' => true,
                    'bank_name' => $plaidDetails['bank_name'],
                    'location' => '',
                    'address_line1' => '',
                    'city' => '',
                    'state' => '',
                    'postal_code' => '',
                    'message' => 'Bank details fetched dynamically via Plaid API.',
                ];
            }
        } catch (Exception $e) {
            \Log::info("Plaid dynamic routing lookup notice: " . $e->getMessage());
        }

        // 4. Fallback for Valid ABA Checksum
        return [
            'valid' => true,
            'bank_name' => '',
            'location' => '',
            'address_line1' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'message' => 'ABA Routing Number checksum is valid.',
        ];
    }

    /**
     * Dynamically query Plaid API (/institutions/get) with include_auth_metadata for institution matching routing number
     */
    private function lookupPlaidInstitution($routingNumber)
    {
        try {
            $clientId = \App\Models\SystemSetting::getValue('plaid_client_id') ?? config('services.plaid.client_id');
            $secret = \App\Models\SystemSetting::getValue('plaid_secret') ?? config('services.plaid.secret');
            $env = \App\Models\SystemSetting::getValue('plaid_environment') ?? config('services.plaid.environment', 'sandbox');

            if (empty($clientId) || empty($secret)) {
                return null;
            }

            $baseUrl = match ($env) {
                'production' => 'https://production.plaid.com',
                'development' => 'https://development.plaid.com',
                default => 'https://sandbox.plaid.com',
            };

            $offset = 0;
            while ($offset < 500) {
                $response = Http::post($baseUrl . '/institutions/get', [
                    'client_id' => $clientId,
                    'secret' => $secret,
                    'count' => 100,
                    'offset' => $offset,
                    'country_codes' => ['US'],
                    'options' => [
                        'include_auth_metadata' => true,
                    ]
                ]);

                if (!$response->successful()) {
                    \Log::warning("Plaid API Warning: " . $response->body());
                    break;
                }

                $institutions = $response->json('institutions') ?? [];
                if (empty($institutions)) {
                    break;
                }

                foreach ($institutions as $inst) {
                    $routings = $inst['routing_numbers'] ?? [];
                    if (in_array($routingNumber, $routings)) {
                        return [
                            'bank_name' => $inst['name'],
                            'address_line1' => '',
                            'city' => '',
                            'state' => '',
                            'postal_code' => '',
                        ];
                    }
                }

                $offset += 100;
            }
        } catch (Exception $e) {
            \Log::warning("Plaid lookup exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Validate ABA Routing Number Checksum Digit
     * Formula: 3(d1 + d4 + d7) + 7(d2 + d5 + d8) + 1(d3 + d6 + d9) mod 10 = 0
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
