<?php

namespace App\Services\Zilmoney;

use Illuminate\Support\Facades\Http;
use App\Models\SystemSetting;

class GiactValidator implements AccountValidatorInterface
{
    public function validate(string $routingNumber, string $accountNumber): array
    {
        $username = SystemSetting::getValue('giact_username');
        $password = SystemSetting::getValue('giact_password');
        $apiUrl = SystemSetting::getValue('giact_api_url') ?? 'https://api.giact.com';

        // If credentials are not configured, simulate success in sandbox
        if (empty($username) || empty($password)) {
            \Log::info("Giact API credentials not configured. Simulating validation.");
            
            if (strlen($routingNumber) !== 9) {
                return ['success' => false, 'message' => 'Invalid routing number length. Must be 9 digits.'];
            }
            return ['success' => true, 'message' => 'Simulated GIACT validation success.'];
        }

        try {
            $response = Http::withBasicAuth($username, $password)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$apiUrl}/v1/verify", [
                    'RoutingNumber' => $routingNumber,
                    'AccountNumber' => $accountNumber,
                ]);

            if ($response->successful()) {
                $status = $response->json('ItemVerificationStatus'); // e.g., 'Approved', 'Pass'
                if ($status === 'Approved' || $status === 'Pass') {
                    return ['success' => true, 'message' => 'Account is approved and valid.'];
                }
                return ['success' => false, 'message' => $response->json('DeclineReason') ?? 'Account verification declined.'];
            }

            return ['success' => false, 'message' => 'GIACT API response error: ' . ($response->json('ErrorMessage') ?? $response->body())];
        } catch (\Exception $e) {
            \Log::error("Giact Validation Exception: " . $e->getMessage());
            return ['success' => false, 'message' => 'GIACT verification failed: ' . $e->getMessage()];
        }
    }
}
