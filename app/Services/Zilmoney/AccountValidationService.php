<?php

namespace App\Services\Zilmoney;

use App\Models\SystemSetting;

class AccountValidationService
{
    /**
     * Resolve the active validator driver.
     *
     * @return AccountValidatorInterface|null
     */
    public function getDriver(): ?AccountValidatorInterface
    {
        $provider = SystemSetting::getValue('account_validation_provider') ?? 'manual';

        return match (strtolower($provider)) {
            'validifi' => new ValidiFiValidator(),
            'giact' => new GiactValidator(),
            default => null, // 'manual' or 'none' resolves to null
        };
    }

    /**
     * Validate a bank account using the active provider if configured.
     *
     * @param string $routingNumber
     * @param string $accountNumber
     * @return array ['success' => bool, 'message' => string]
     */
    public function validate(string $routingNumber, string $accountNumber): array
    {
        // 1. Basic structural checksum validation (Nacha-compliant ABA routing checksum)
        if (!$this->validateRoutingChecksum($routingNumber)) {
            return [
                'success' => false,
                'message' => 'Invalid routing number format. Checksum failed.'
            ];
        }

        // 2. Resolve provider driver
        $driver = $this->getDriver();
        if (!$driver) {
            return [
                'success' => true,
                'message' => 'Structural validation passed. (Manual mode)'
            ];
        }

        // 3. Call driver validation
        return $driver->validate($routingNumber, $accountNumber);
    }

    /**
     * Check if a routing number is valid using the standard ABA checksum formula.
     * Formula: 3(d1 + d4 + d7) + 7(d2 + d5 + d8) + (d3 + d6 + d9) must be a multiple of 10.
     *
     * @param string $routingNumber
     * @return bool
     */
    public function validateRoutingChecksum(string $routingNumber): bool
    {
        $routingNumber = preg_replace('/\D/', '', $routingNumber);

        if (strlen($routingNumber) !== 9) {
            return false;
        }

        $checksum = 0;
        $weights = [3, 7, 1, 3, 7, 1, 3, 7, 1];

        for ($i = 0; $i < 9; $i++) {
            $checksum += (int)$routingNumber[$i] * $weights[$i];
        }

        return ($checksum % 10) === 0;
    }
}
