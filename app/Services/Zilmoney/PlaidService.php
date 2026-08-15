<?php

namespace App\Services\Zilmoney;

use Illuminate\Support\Facades\Http;
use App\Models\Zilmoney\PlaidItem;
use App\Models\Zilmoney\Account;
use App\Models\Zilmoney\Payment;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\SystemSetting;

class PlaidService
{
    protected $baseUrl;
    protected $clientId;
    protected $secret;

    public function __construct()
    {
        $this->clientId = SystemSetting::getValue('plaid_client_id') ?? config('services.plaid.client_id');
        $this->secret = SystemSetting::getValue('plaid_secret') ?? config('services.plaid.secret');
        $environment = SystemSetting::getValue('plaid_environment') ?? config('services.plaid.environment', 'sandbox');

        $this->baseUrl = match ($environment) {
            'production' => 'https://production.plaid.com',
            'development' => 'https://development.plaid.com',
            default => 'https://sandbox.plaid.com',
        };
    }

    /**
     * Dynamically set baseUrl based on access/public/link token.
     */
    protected function setEnvironmentForToken($token)
    {
        if (empty($token) || !is_string($token)) {
            return;
        }

        if (strpos($token, 'sandbox') !== false) {
            $this->baseUrl = 'https://sandbox.plaid.com';
        } elseif (strpos($token, 'development') !== false) {
            $this->baseUrl = 'https://development.plaid.com';
        } elseif (strpos($token, 'production') !== false) {
            $this->baseUrl = 'https://production.plaid.com';
        }
    }

    /**
     * Create a Link Token for the frontend.
     */
    public function createLinkToken($userId, $companyId = null, $redirectUri = null, $accessToken = null)
    {
        \Log::info("createLinkToken: Called with User ID: $userId, Company ID: $companyId" . ($accessToken ? ", Access Token: (Provided)" : ""));

        if ($accessToken) {
            $this->setEnvironmentForToken($accessToken);
        }

        $configuredProductsRaw = SystemSetting::getValue('plaid_products');
        $configuredProducts = [];
        if ($configuredProductsRaw) {
            $decoded = json_decode($configuredProductsRaw, true);
            if (is_array($decoded)) {
                $configuredProducts = $decoded;
            } else {
                $configuredProducts = array_filter(array_map('trim', explode(',', $configuredProductsRaw)));
            }
        }
        if (empty($configuredProducts)) {
            $configuredProducts = ['auth'];
        }

        $payload = [
            'client_id' => $this->clientId,
            'secret' => $this->secret,
            'client_name' => 'Goldenmark Money',
            'language' => 'en',
            'country_codes' => ['US'],
            'user' => [
                'client_user_id' => (string) $userId,
                'email_address' => \App\Models\User::find($userId)?->email ?? 'no-email@example.com',
            ],
            'products' => $accessToken ? [] : array_values($configuredProducts), // Products cannot be set in update mode
            'webhook' => ($this->getWebhookUrl()) . '?webhook_user_id=' . $userId . '&webhook_company_id=' . $companyId,
            'hosted_link' => [
                // 'delivery_method' => 'email', // Disabled: Requires Plaid dashboard config. We will redirect manually.
                'completion_redirect_uri' => $redirectUri ?? 'https://zilmoney.com', // fallback required
            ]
        ];

        if ($accessToken) {
            $payload['access_token'] = $accessToken;
             // In update mode, products must be null or matching original. Usually 'products' is omitted if access_token is present.
             unset($payload['products']);
             // unset($payload['country_codes']); // FIX: Plaid requires country_codes even in update mode for some integrations
        }

        // If specific redirect URI provided (e.g., from frontend), use it
        if ($redirectUri) {
            // $payload['redirect_uri'] = $redirectUri; // REMOVED: Triggers OAuth allowlist check. Hosted Link handles OAuth internally.
            $payload['hosted_link']['completion_redirect_uri'] = $redirectUri;
        }

        \Log::info("Plaid createLinkToken Payload: " . json_encode($payload));

        $response = Http::post("{$this->baseUrl}/link/token/create", $payload);

        if ($response->failed()) {
            throw new Exception('Plaid Error: ' . $response->json('error_message'));
        }

        // Return the hosted_link_url specifically
        return $response->json();
    }

    /**
     * Exchange public token for access token and save item.
     */
    public function exchangeTokenAndSave($publicToken, $userId, $businessId = null)
    {
        $this->setEnvironmentForToken($publicToken);
        $response = Http::post("{$this->baseUrl}/item/public_token/exchange", [
            'client_id' => $this->clientId,
            'secret' => $this->secret,
            'public_token' => $publicToken,
        ]);

        if ($response->failed()) {
            throw new Exception('Plaid Exchange Error: ' . $response->json('error_message'));
        }

        $data = $response->json();
        $accessToken = $data['access_token'];
        $itemId = $data['item_id'];

        // Save to DB
        $plaidItem = PlaidItem::updateOrCreate(
            ['item_id' => $itemId],
            [
                'user_id' => $userId,
                'access_token' => $accessToken, // In prod, encrypt this!
                'status' => 'active',
            ]
        );

        // Sync initial data (Accounts, etc.)
        $this->syncAccounts($plaidItem, $businessId); // Sync immediately

        return $plaidItem;
    }

    /**
     * Sync accounts from Plaid to local DB.
     */
    public function syncAccounts(PlaidItem $plaidItem, $businessId = null)
    {
        $this->setEnvironmentForToken($plaidItem->access_token);
        // Try to get auth data (numbers) first
        $response = Http::post("{$this->baseUrl}/auth/get", [
            'client_id' => $this->clientId,
            'secret' => $this->secret,
            'access_token' => $plaidItem->access_token,
        ]);

        // Fallback to accounts/get if auth is not supported or fails (e.g. credit cards)
        $isAuth = true;
        if ($response->failed()) {
            $isAuth = false;
            $response = Http::post("{$this->baseUrl}/accounts/get", [
                'client_id' => $this->clientId,
                'secret' => $this->secret,
                'access_token' => $plaidItem->access_token,
            ]);

            if ($response->failed()) {
                \Log::error("Plaid Sync Error: " . $response->body());
                return; 
            }
        }
        
        $data = $response->json();
        $accounts = $data['accounts'];
        $numbers = $data['numbers']['ach'] ?? []; // Array of account numbers

        $validAccountIds = [];

        foreach ($accounts as $accountData) {
            $accountId = $accountData['account_id'];
            
            // Find matching numbers if available
            $accountNumber = null;
            $routingNumber = null;
            $isTokenized = false;
            
            if ($isAuth) {
                foreach ($numbers as $numberObj) {
                    if ($numberObj['account_id'] === $accountId) {
                        $accountNumber = $numberObj['account'];
                        $routingNumber = $numberObj['routing'];
                        $isTokenized = (bool) ($numberObj['is_tokenized_account_number'] ?? false);
                        break;
                    }
                }
            }

            // Define unique constraints for deduplication
            // If we have real numbers, use them to find existing account.
            // Otherwise, fall back to plaid_account_id (which is unique per Item only).
            $matchAttributes = [];
            if ($businessId && $accountNumber && $routingNumber) {
                $matchAttributes = [
                    'company_id' => $businessId,
                    'account_number' => $accountNumber,
                    'routing_number' => $routingNumber,
                ];
            } else {
                 $matchAttributes = [
                    'plaid_account_id' => $accountId,
                ];
            }

            // Find existing account first to preserve manual verification data
            $existingAccount = null;
            if ($accountId) {
                $existingAccount = Account::where('plaid_account_id', $accountId)->first();
            }
            if (!$existingAccount && $businessId && $accountNumber && $routingNumber) {
                $existingAccount = Account::where([
                    'company_id' => $businessId,
                    'account_number' => $accountNumber,
                    'routing_number' => $routingNumber,
                ])->first();
            }

            $updateData = [
                'company_id' => $businessId, // Ensure company_id is set
                'plaid_item_id' => $plaidItem->id,
                'plaid_account_id' => $accountId, // specific to this connection
                'account_holder_name' => $accountData['name'], // Note: Plaid 'name' is usually account name (e.g. "Checking"), not holder name.
                'account_nick_name' => $accountData['name'], // Map name to nick_name as requested
                'official_name' => $accountData['official_name'] ?? null,
                'type' => $accountData['subtype'] ?? $accountData['type'],
                'mask' => $accountData['mask'] ?? null,
                'balance' => $accountData['balances']['available'] ?? $accountData['balances']['current'] ?? 0,
                'status' => 'active', // Ensure active status
            ];

            if ($existingAccount && $existingAccount->verification_status === 'verified') {
                // If already manually verified, keep the verified details and do not overwrite
                $updateData['account_number'] = $existingAccount->account_number;
                $updateData['routing_number'] = $existingAccount->routing_number;
                $updateData['is_tokenized'] = false;
                $updateData['verification_status'] = 'verified';
            } else {
                $updateData['account_number'] = $accountNumber ?? $accountData['mask'] ?? null;
                $updateData['routing_number'] = $routingNumber ?? '000000000';
                $updateData['is_tokenized'] = $isTokenized;
                $updateData['verification_status'] = $isTokenized ? 'pending' : 'verified';
            }

            $account = Account::updateOrCreate(
                $existingAccount ? ['id' => $existingAccount->id] : $matchAttributes,
                $updateData
            );
            
            $validAccountIds[] = $account->id;
        }

        // Remove accounts that are no longer present in the Plaid Item
        // This handles cases where user unselects an account during update mode
        Account::where('plaid_item_id', $plaidItem->id)
            ->whereNotIn('id', $validAccountIds)
            ->delete(); 
    }

    /**
     * Process incoming webhook events.
     */
    public function processWebhook(?PlaidItem $plaidItem = null, $type, $code, $payload)
    {
        \Log::info("PlaidService: Processing Webhook Type=$type, Code=$code");

        if (isset($payload['environment'])) {
            $environment = $payload['environment'];
            $this->baseUrl = match ($environment) {
                'production' => 'https://production.plaid.com',
                'development' => 'https://development.plaid.com',
                default => 'https://sandbox.plaid.com',
            };
        } elseif ($plaidItem) {
            $this->setEnvironmentForToken($plaidItem->access_token);
        }

        // Handle Hosted Link 'SESSION_FINISHED' (No PlaidItem yet)
        if ($type === 'LINK' && $code === 'SESSION_FINISHED') {
            \Log::info("PlaidService: Handling SESSION_FINISHED");
            
            $status = strtoupper($payload['status'] ?? '');
            $publicToken = $payload['public_token'] ?? ($payload['public_tokens'][0] ?? null);

            if ($status === 'SUCCESS' && $publicToken) {
                $linkToken = $payload['link_token']; 
                
                \Log::info("Session Finished. Public Token found.");
                \Log::info("Link Token: $linkToken");
                
                // Try to get User ID from the webhook query parameters (merged into payload by Laravel)
                $userId = $payload['webhook_user_id'] ?? null;
                $companyId = $payload['webhook_company_id'] ?? null;
                
                // Fallback: Try to get from metadata (though logs show this usually fails)
                if (!$userId) {
                     $userId = $this->getUserIdFromLinkToken($linkToken);
                }

                \Log::info("User ID resolved: " . ($userId ?? 'NULL') . ", Company ID resolved: " . ($companyId ?? 'NULL'));
                
                if ($userId) {
                     \Log::info("Exchanging token for user {$userId}...");
                     $item = $this->exchangeTokenAndSave($publicToken, $userId, $companyId);
                     \Log::info("Exchanged token and saved item successfully. Item ID: {$item->id}");
                } else {
                    \Log::error("Could not determine user for link_token: {$linkToken}. Cannot save item.");
                }
            } else {
                \Log::warning("SESSION_FINISHED but status is not SUCCESS or public_token missing. Status: $status");
                \Log::warning("Payload keys: " . implode(',', array_keys($payload)));
            }
            return;
        }

        if (!$plaidItem) {
            \Log::info("PlaidService: No PlaidItem provided, and not a SESSION_FINISHED event. Skipping.");
            return;
        }

        // Handle specific webhook types for existing Items
        if ($type === 'ITEM') {
            if ($code === 'ERROR') {
                $error = $payload['error'] ?? null;
                if ($error) {
                    \Log::error("Plaid Item Error for User {$plaidItem->user_id}: " . json_encode($error));
                    
                    if ($error['error_code'] === 'ITEM_LOGIN_REQUIRED') {
                        $plaidItem->update(['status' => 'login_required']);
                        \Log::info("Marked Item {$plaidItem->id} as login_required.");
                    }
                }
            } elseif ($code === 'PENDING_EXPIRATION') {
                 $plaidItem->update(['status' => 'login_required']);
                 \Log::info("Marked Item {$plaidItem->id} as login_required (PENDING_EXPIRATION).");
            } elseif ($code === 'WEBHOOK_UPDATE_ACKNOWLEDGED') {
                // Good to know
            }
        } elseif ($type === 'TRANSACTIONS') {
            \Log::info("PlaidService: Handling TRANSACTIONS webhook for Item ID: {$plaidItem->id}, Code: {$code}");
            \Log::info("Syncing accounts...");
            $this->syncAccounts($plaidItem, $plaidItem->accounts()->first()->company_id ?? null);
            \Log::info("Accounts synced.");

            // Match and process checks/payments using transactions
            try {
                \Log::info("Fetching transactions to match with pending payments...");
                $transactions = $this->getSandboxTransactions($plaidItem->access_token);
                \Log::info("Fetched " . count($transactions) . " transactions from Plaid.");

                foreach ($transactions as $tx) {
                    $txAmount = abs((float) ($tx['amount'] ?? 0)); // Plaid amounts are positive for debits
                    $txName = $tx['name'] ?? $tx['original_description'] ?? '';
                    
                    \Log::info("Processing transaction: '{$txName}', Amount: {$txAmount}");

                    // Attempt to extract numeric reference ID or check number from transaction description
                    // e.g., "Cashed Check #12345" -> 12345, "Check 71", "Payment 71"
                    $matchedNumber = null;
                    if (preg_match('/#(\d+)/', $txName, $matches)) {
                        $matchedNumber = $matches[1];
                    } elseif (preg_match('/(?:check|ref|reference|payment)\s*#?\s*(\d+)/i', $txName, $matches)) {
                        $matchedNumber = $matches[1];
                    }

                    $payment = null;

                    if ($matchedNumber) {
                        \Log::info("Extracted check/reference number: {$matchedNumber}");
                        
                        // Find active check/payment matching the check number or reference/unique ID
                        $payment = Payment::whereNotIn('status', ['paid', 'void', 'voided', 'failed'])
                            ->where(function($q) use ($matchedNumber) {
                                $q->where('check_number', $matchedNumber)
                                  ->orWhere('unique_check_id', 'like', "%{$matchedNumber}%")
                                  ->orWhere('id', $matchedNumber);
                            })
                            ->first();
                    }

                    // Fallback matching by exact amount if check number wasn't found
                    if (!$payment && $txAmount > 0) {
                        $payment = Payment::whereNotIn('status', ['paid', 'void', 'voided', 'failed'])
                            ->where('amount', $txAmount)
                            ->first();
                        if ($payment) {
                            \Log::info("Matched payment ID {$payment->id} by amount: {$txAmount}");
                        }
                    }

                    if ($payment) {
                        \Log::info("Found matching payment ID: {$payment->id}, current status: {$payment->status}");
                        $payment->update(['status' => 'paid']);
                        $payment->logs()->create([
                            'status' => 'paid',
                            'note' => "Payment processed automatically via Plaid webhook. Match: '{$txName}'",
                            'device_info' => 'Plaid Webhook'
                        ]);
                        \Log::info("Payment ID {$payment->id} status updated to 'paid'.");
                    } else {
                        if ($matchedNumber) {
                            \Log::warning("No active payment found matching number/ID: {$matchedNumber}.");
                        } else {
                            \Log::info("Could not match transaction: '{$txName}' (Amount: {$txAmount}) with any pending payment.");
                        }
                    }
                }
            } catch (\Throwable $txEx) {
                \Log::error("Error matching payments during webhook sync: " . $txEx->getMessage());
            }
        }
    }

    private function getUserIdFromLinkToken($linkToken)
    {
        \Log::info("Fetching metadata for Link Token: $linkToken");
        try {
            $this->setEnvironmentForToken($linkToken);
            $response = Http::post("{$this->baseUrl}/link/token/get", [
                'client_id' => $this->clientId,
                'secret' => $this->secret,
                'link_token' => $linkToken,
            ]);
            
            if ($response->successful()) {
                \Log::info("Link Token Get Response: " . $response->body());
                $clientUserId = $response->json('user.client_user_id');
                \Log::info("Metadata retrieved. client_user_id: $clientUserId");
                return $clientUserId;
            } else {
                 \Log::error("Failed to get link token metadata: " . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error("Error fetching link token metadata: " . $e->getMessage());
        }
        return null;
    }
    public function resetSandboxLogin($accessToken)
    {
        $this->setEnvironmentForToken($accessToken);
        $response = Http::post("{$this->baseUrl}/sandbox/item/reset_login", [
            'client_id' => $this->clientId,
            'secret' => $this->secret,
            'access_token' => $accessToken,
        ]);

        if ($response->failed()) {
            throw new Exception('Plaid Reset Login Error: ' . $response->json('error_message'));
        }

        return $response->json();
    }

    /**
     * Remove an Item from Plaid (Disconnect bank account).
     */
    public function removeItem($accessToken)
    {
        $this->setEnvironmentForToken($accessToken);
        $response = Http::post("{$this->baseUrl}/item/remove", [
            'client_id' => $this->clientId,
            'secret' => $this->secret,
            'access_token' => $accessToken,
        ]);

        if ($response->failed()) {
            \Log::error('Plaid Item Remove Error: ' . $response->body());
            throw new Exception('Plaid Item Remove Error: ' . ($response->json('error_message') ?? 'Failed to remove Plaid item'));
        }

        return $response->json();
    }

    /**
     * Disconnect a specific Plaid Item and clean up database accounts.
     */
    public function disconnectItem($itemId, $userId)
    {
        $plaidItem = PlaidItem::where('user_id', $userId)
            ->where(function ($q) use ($itemId) {
                $q->where('id', $itemId)->orWhere('item_id', $itemId);
            })
            ->first();

        if (!$plaidItem) {
            throw new Exception('Plaid bank connection item not found.');
        }

        try {
            $this->removeItem($plaidItem->access_token);
        } catch (\Exception $e) {
            \Log::warning("Plaid API item/remove failed (might already be removed): " . $e->getMessage());
        }

        // Delete associated local accounts
        Account::where('plaid_item_id', $plaidItem->id)->delete();
        $plaidItem->delete();

        return true;
    }

    /**
     * Delete all banking data & disconnect all Plaid items for a user (Data Privacy / Compliance).
     */
    public function deleteUserBankingData($userId)
    {
        $plaidItems = PlaidItem::where('user_id', $userId)->get();

        foreach ($plaidItems as $item) {
            try {
                $this->removeItem($item->access_token);
            } catch (\Exception $e) {
                \Log::warning("Plaid item/remove failed for user {$userId}, item {$item->id}: " . $e->getMessage());
            }
            Account::where('plaid_item_id', $item->id)->delete();
            $item->delete();
        }

        return true;
    }

    /**
     * Create a mock transaction in the Sandbox environment.
     * Note: Only works for Items created with the 'user_transactions_dynamic' username.
     */
    public function createSandboxTransaction($accessToken, $accountId, $amount, $description, $date = null)
    {
        $this->setEnvironmentForToken($accessToken);
        \Log::info("PlaidService: Creating Sandbox Transaction...", [
            'account_id' => $accountId,
            'amount' => $amount,
            'description' => $description
        ]);

        $response = Http::post("{$this->baseUrl}/sandbox/transactions/create", [
            'client_id' => $this->clientId,
            'secret' => $this->secret,
            'access_token' => $accessToken,
            'transactions' => [
                [
                    'account_id' => $accountId,
                    'amount' => (float) $amount,
                    'description' => $description,
                    'date_posted' => $date ?? date('Y-m-d'),
                    'date_transacted' => $date ?? date('Y-m-d'),
                ]
            ]
        ]);

        if ($response->failed()) {
            \Log::error("Plaid Create Sandbox Transaction Error: " . $response->body());
            throw new Exception('Plaid Create Sandbox Transaction Error: ' . ($response->json('error_message') ?? $response->body()));
        }

        \Log::info("Plaid Create Sandbox Transaction Success: " . $response->body());

        return $response->json();
    }

    /**
     * Retrieve transactions for a specific Item from Plaid API.
     */
    public function getSandboxTransactions($accessToken, $startDate = null, $endDate = null)
    {
        $this->setEnvironmentForToken($accessToken);
        // 1. Use /transactions/get as the primary method because it is date-bound and doesn't require cursors
        try {
            $response = Http::post("{$this->baseUrl}/transactions/get", [
                'client_id' => $this->clientId,
                'secret' => $this->secret,
                'access_token' => $accessToken,
                'start_date' => $startDate ?? date('Y-m-d', strtotime('-30 days')),
                'end_date' => $endDate ?? date('Y-m-d', strtotime('+3 days')),
            ]);

            if ($response->successful()) {
                return $response->json('transactions') ?? [];
            }
        } catch (\Throwable $e) {
            \Log::warning("Plaid transactions/get failed, trying transactions/sync fallback: " . $e->getMessage());
        }

        // 2. Fallback to /transactions/sync
        $response = Http::post("{$this->baseUrl}/transactions/sync", [
            'client_id' => $this->clientId,
            'secret' => $this->secret,
            'access_token' => $accessToken,
        ]);

        if ($response->failed()) {
            throw new Exception('Plaid Get Transactions Error: ' . ($response->json('error_message') ?? $response->body()));
        }

        return $response->json('added') ?? [];
    }

    /**
     * Force fire a webhook in the Sandbox environment.
     */
    public function fireSandboxWebhook($accessToken, $webhookType = 'TRANSACTIONS', $webhookCode = 'DEFAULT_UPDATE')
    {
        $this->setEnvironmentForToken($accessToken);
        $response = Http::post("{$this->baseUrl}/sandbox/item/fire_webhook", [
            'client_id' => $this->clientId,
            'secret' => $this->secret,
            'access_token' => $accessToken,
            'webhook_type' => $webhookType,
            'webhook_code' => $webhookCode,
        ]);

        if ($response->failed()) {
            throw new Exception('Plaid Fire Sandbox Webhook Error: ' . ($response->json('error_message') ?? $response->body()));
        }

        return $response->json();
    }

    private function getWebhookUrl()
    {
        return SystemSetting::getValue('plaid_webhook_url') 
            ?? config('services.plaid.webhook_url') 
            ?? url('/api/zilmoney/plaid/webhook');
    }
}
