<?php

namespace App\Services\Zilmoney;

use Illuminate\Support\Facades\Http;
use App\Models\Zilmoney\PlaidItem;
use App\Models\Zilmoney\Account;
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
     * Create a Link Token for the frontend.
     */
    public function createLinkToken($userId, $companyId = null, $redirectUri = null, $accessToken = null)
    {
        \Log::info("createLinkToken: Called with User ID: $userId, Company ID: $companyId" . ($accessToken ? ", Access Token: (Provided)" : ""));

        $payload = [
            'client_id' => $this->clientId,
            'secret' => $this->secret,
            'client_name' => 'Zilmoney',
            'language' => 'en',
            'country_codes' => ['US'],
            'user' => [
                'client_user_id' => (string) $userId,
                'email_address' => \App\Models\User::find($userId)?->email ?? 'no-email@example.com',
            ],
            'products' => $accessToken ? [] : ['transactions', 'auth'], // Products cannot be set in update mode
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
            
            if ($isAuth) {
                foreach ($numbers as $numberObj) {
                    if ($numberObj['account_id'] === $accountId) {
                        $accountNumber = $numberObj['account'];
                        $routingNumber = $numberObj['routing'];
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

            $account = Account::updateOrCreate(
                $matchAttributes,
                [
                    'company_id' => $businessId, // Ensure company_id is set
                    'plaid_item_id' => $plaidItem->id,
                    'plaid_account_id' => $accountId, // specific to this connection
                    'account_holder_name' => $accountData['name'], // Note: Plaid 'name' is usually account name (e.g. "Checking"), not holder name.
                    'account_nick_name' => $accountData['name'], // Map name to nick_name as requested
                    'official_name' => $accountData['official_name'] ?? null,
                    'account_number' => $accountNumber ?? $accountData['mask'] ?? null, // Fallback to mask
                    'routing_number' => $routingNumber ?? '000000000',
                    'type' => $accountData['subtype'] ?? $accountData['type'],
                    'mask' => $accountData['mask'] ?? null,
                    'balance' => $accountData['balances']['current'] ?? 0,
                    'status' => 'active', // Ensure active status
                ]
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
            \Log::info("PlaidService: Handling TRANSACTIONS webhook for Item ID: {$plaidItem->id}");
            if ($code === 'SYNC_UPDATES_AVAILABLE' || $code === 'DEFAULT_UPDATE') {
                \Log::info("Syncing accounts...");
                $this->syncAccounts($plaidItem, $plaidItem->accounts()->first()->company_id ?? null);
                \Log::info("Accounts synced.");
            }
        }
    }

    private function getUserIdFromLinkToken($linkToken)
    {
        \Log::info("Fetching metadata for Link Token: $linkToken");
        try {
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

    private function getWebhookUrl()
    {
        return SystemSetting::getValue('plaid_webhook_url') 
            ?? config('services.plaid.webhook_url') 
            ?? url('/api/zilmoney/plaid/webhook');
    }
}
