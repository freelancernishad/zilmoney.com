<?php

namespace App\Services\Zilmoney;

interface AccountValidatorInterface
{
    /**
     * Validate the given routing number and account number.
     *
     * @param string $routingNumber
     * @param string $accountNumber
     * @return array ['success' => bool, 'message' => string]
     */
    public function validate(string $routingNumber, string $accountNumber): array;
}
