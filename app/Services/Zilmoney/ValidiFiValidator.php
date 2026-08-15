<?php

namespace App\Services\Zilmoney;

use Illuminate\Support\Facades\Http;
use App\Models\SystemSetting;

class ValidiFiValidator implements AccountValidatorInterface
{
    public function validate(string $routingNumber, string $accountNumber): array
    {
        $apiKey = SystemSetting::getValue('validifi_api_key');
        $clientId = SystemSetting::getValue('validifi_client_id');
        $apiUrl = SystemSetting::getValue('validifi_api_url') ?? 'https://api.validifi.com';

        // If credentials are not configured, simulate success in sandbox or return warning
        if (empty($apiKey) || empty($clientId)) {
            \Log::info("ValidiFi API credentials not configured. Simulating validation.");
            
            // Basic structural check
            if (strlen($routingNumber) !== 9) {
                return ['success' => false, 'message' => 'Invalid routing number length. Must be 9 digits.'];
            }
            return ['success' => true, 'message' => 'Simulated ValidiFI validation success.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post("{$apiUrl}/v1/validate", [
                'client_id' => $clientId,
                'routing_number' => $routingNumber,
                'account_number' => $accountNumber,
            ]);

            if ($response->successful()) {
                $status = $response->json('status'); // e.g., 'ACTIVE', 'VALID'
                if ($status === 'VALID' || $status === 'ACTIVE') {
                    return ['success' => true, 'message' => 'Account is valid and active.'];
                }
                return ['success' => false, 'message' => $response->json('message') ?? 'Account validation failed.'];
            }

            return ['success' => false, 'message' => 'API response error: ' . ($response->json('error_message') ?? $response->body())];
        } catch (\Exception $e) {
            \Log::error("ValidiFi Validation Exception: " . $e->getMessage());
            return ['success' => false, 'message' => 'Validation request failed: ' . $e->getMessage()];
        }
    }
}
