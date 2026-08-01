<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Zilmoney\PlaidService;

class PlaidController extends Controller
{
    protected $plaidService;

    public function __construct(PlaidService $plaidService)
    {
        $this->plaidService = $plaidService;
    }

    public function createLinkToken(Request $request)
    {
        try {
            $redirectUri = $request->input('redirect_uri');
            $itemId = $request->input('item_id'); // Optional: for update mode
            
            // Assuming user has a business. In real app, might need to select specific business.
            $business = auth()->user()->businessDetails; 
            if (!$business) {
                $business = \App\Models\Zilmoney\BusinessDetail::create([
                    'user_id' => auth()->id(),
                    'legal_business_name' => auth()->user()->name ? (auth()->user()->name . "'s Business") : "My Business",
                    'entity_type' => 'LLC',
                    'country' => 'United States',
                ]);
            }
            $companyId = $business->id;

            $accessToken = null;
            if ($itemId) {
                // Find the Plaid Item belonging to this user
                $plaidItem = \App\Models\Zilmoney\PlaidItem::where('user_id', auth()->id())
                    ->where('id', $itemId)
                    ->first();
                
                if ($plaidItem) {
                    $accessToken = $plaidItem->access_token;
                }
            }

            $data = $this->plaidService->createLinkToken(auth()->id(), $companyId, $redirectUri, $accessToken);
            
            return response()->json([
                'link_token' => $data['link_token'],
                'hosted_link_url' => $data['hosted_link_url'] ?? null,
                'expiration' => $data['expiration'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function exchangePublicToken(Request $request)
    {
        $request->validate([
            'public_token' => 'required|string',
        ]);

        try {
            // Assuming 1 business for now
            $business = auth()->user()->businessDetails;
            if (!$business) {
                $business = \App\Models\Zilmoney\BusinessDetail::create([
                    'user_id' => auth()->id(),
                    'legal_business_name' => auth()->user()->name ? (auth()->user()->name . "'s Business") : "My Business",
                    'entity_type' => 'LLC',
                    'country' => 'United States',
                ]);
            }

            $this->plaidService->exchangeTokenAndSave($request->public_token, auth()->id(), $business->id);
            
            return response()->json(['message' => 'Bank linked successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function resetLogin(Request $request)
    {
        $request->validate(['item_id' => 'required']);
        $plaidItem = \App\Models\Zilmoney\PlaidItem::where('user_id', auth()->id())
            ->where('id', $request->item_id)
            ->firstOrFail();

        try {
            $this->plaidService->resetSandboxLogin($plaidItem->access_token);
            
            // Manually update status for immediate UI feedback (webhook will also come later)
            $plaidItem->update(['status' => 'login_required']);

            return response()->json(['message' => 'Login reset triggered. Item status set to login_required.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function showLinkPage()
    {
        $accounts = collect([]);
        $business = auth()->user()->businessDetails;
        if ($business) {
            $accounts = $business->accounts()->with('plaidItem')->latest()->get();
        }
        
        return view('zilmoney.connect-bank', compact('accounts'));
    }

    public function disconnectItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
        ]);

        try {
            $this->plaidService->disconnectItem($request->item_id, auth()->id());
            return response()->json(['message' => 'Bank account disconnected successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteBankingData(Request $request)
    {
        try {
            $this->plaidService->deleteUserBankingData(auth()->id());
            return response()->json(['message' => 'All banking data and connections have been permanently deleted.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function createSandboxTransaction(Request $request)
    {
        $request->validate([
            'plaid_item_id' => 'required',
            'plaid_account_id' => 'required|string',
            'amount' => 'required|numeric',
            'description' => 'required|string',
        ]);

        try {
            $plaidItem = \App\Models\Zilmoney\PlaidItem::where('user_id', auth()->id())
                ->where('id', $request->plaid_item_id)
                ->firstOrFail();

            $data = $this->plaidService->createSandboxTransaction(
                $plaidItem->access_token,
                $request->plaid_account_id,
                $request->amount,
                $request->description
            );

            // Simulate the webhook locally to trigger account syncing and balance updates
            try {
                $this->plaidService->processWebhook(
                    $plaidItem,
                    'TRANSACTIONS',
                    'DEFAULT_UPDATE',
                    [
                        'webhook_type' => 'TRANSACTIONS',
                        'webhook_code' => 'DEFAULT_UPDATE',
                        'item_id' => $plaidItem->item_id,
                        'new_transactions' => 1
                    ]
                );
            } catch (\Throwable $ex) {
                \Log::error("Plaid Sandbox Local Webhook Simulation Error during creation: " . $ex->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Sandbox transaction created and synced successfully.',
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function fireSandboxWebhook(Request $request)
    {
        $request->validate([
            'plaid_item_id' => 'required',
            'webhook_code' => 'required|string',
        ]);

        try {
            $plaidItem = \App\Models\Zilmoney\PlaidItem::where('user_id', auth()->id())
                ->where('id', $request->plaid_item_id)
                ->firstOrFail();

            $data = $this->plaidService->fireSandboxWebhook(
                $plaidItem->access_token,
                'TRANSACTIONS',
                $request->webhook_code
            );

            // Simulate the webhook locally to trigger account syncing and balance updates
            try {
                $this->plaidService->processWebhook(
                    $plaidItem,
                    'TRANSACTIONS',
                    $request->webhook_code,
                    [
                        'webhook_type' => 'TRANSACTIONS',
                        'webhook_code' => $request->webhook_code,
                        'item_id' => $plaidItem->item_id,
                        'new_transactions' => 1
                    ]
                );
            } catch (\Throwable $ex) {
                \Log::error("Plaid Sandbox Local Webhook Simulation Error during fire: " . $ex->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Sandbox webhook triggered and synced successfully.',
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getTransactions(Request $request)
    {
        $request->validate([
            'plaid_item_id' => 'required',
        ]);

        try {
            $plaidItem = \App\Models\Zilmoney\PlaidItem::where('user_id', auth()->id())
                ->where('id', $request->plaid_item_id)
                ->firstOrFail();

            $transactions = $this->plaidService->getSandboxTransactions($plaidItem->access_token);

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
