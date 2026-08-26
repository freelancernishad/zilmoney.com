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
                \Log::info("Plaid Routing Lookup Success for Routing [{$routingNumber}]", [
                    'bank_name' => $plaidDetails['bank_name'],
                    'has_logo' => !empty($plaidDetails['logo']),
                    'primary_color' => $plaidDetails['primary_color'] ?? null,
                ]);

                return [
                    'valid' => true,
                    'bank_name' => $plaidDetails['bank_name'],
                    'logo' => $plaidDetails['logo'] ?? null,
                    'primary_color' => $plaidDetails['primary_color'] ?? null,
                    'website' => $plaidDetails['website'] ?? null,
                    'location' => '',
                    'address_line1' => '',
                    'city' => '',
                    'state' => '',
                    'postal_code' => '',
                    'message' => 'Bank details fetched dynamically via Plaid API.',
                ];
            }
        } catch (Exception $e) {
            \Log::error("Plaid dynamic routing lookup notice: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        // 4. Fallback for Valid ABA Checksum
        return [
            'valid' => true,
            'bank_name' => '',
            'logo' => null,
            'primary_color' => null,
            'website' => null,
            'location' => '',
            'address_line1' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'message' => 'ABA Routing Number checksum is valid.',
        ];
    }

    /**
     * Dynamically query Plaid API (/institutions/get) with include_auth_metadata & include_optional_metadata for institution matching routing number
     */
    public function lookupPlaidInstitution($routingNumber)
    {
        try {
            $clientId = \App\Models\SystemSetting::getValue('plaid_client_id') ?? config('services.plaid.client_id');
            $secret = \App\Models\SystemSetting::getValue('plaid_secret') ?? config('services.plaid.secret');
            $env = \App\Models\SystemSetting::getValue('plaid_environment') ?? config('services.plaid.environment', 'sandbox');

            if (empty($clientId) || empty($secret)) {
                \Log::warning("Plaid credentials missing in SystemSetting/config.");
                return null;
            }

            $baseUrl = match ($env) {
                'production' => 'https://production.plaid.com',
                'development' => 'https://development.plaid.com',
                default => 'https://sandbox.plaid.com',
            };

            \Log::info("Plaid API Request: Querying /institutions/get for routing [{$routingNumber}]", [
                'environment' => $env,
                'baseUrl' => $baseUrl,
            ]);

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
                        'include_optional_metadata' => true,
                    ]
                ]);

                \Log::info("Plaid API Response Status: " . $response->status(), [
                    'offset' => $offset,
                    'successful' => $response->successful(),
                ]);

                if (!$response->successful()) {
                    \Log::error("Plaid API Error Response for Routing [{$routingNumber}]: " . $response->body());
                    break;
                }

                $institutions = $response->json('institutions') ?? [];
                if (empty($institutions)) {
                    \Log::info("Plaid API returned no institutions at offset {$offset}.");
                    break;
                }

                foreach ($institutions as $inst) {
                    $routings = $inst['routing_numbers'] ?? [];
                    if (in_array($routingNumber, $routings)) {
                        $name = $inst['name'] ?? null;
                        $logoRaw = $inst['logo'] ?? null;
                        $primaryColor = $inst['primary_color'] ?? null;
                        $url = $inst['url'] ?? null;

                        $instId = $inst['institution_id'] ?? null;
                        $logoUrl = PlaidService::resolveFullBankLogo($name, $url, $logoRaw, $instId);

                        \Log::info("Plaid Routing Match Found!", [
                            'routing' => $routingNumber,
                            'institution_id' => $inst['institution_id'] ?? null,
                            'name' => $name,
                            'logo_resolved' => !empty($logoUrl),
                        ]);

                        return [
                            'bank_name' => $name,
                            'logo' => $logoUrl,
                            'primary_color' => $primaryColor,
                            'website' => $url,
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
            \Log::error("Plaid lookup exception: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
