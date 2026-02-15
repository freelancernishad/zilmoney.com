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
            $companyId = $business ? $business->id : null;

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
            if (!$business) return response()->json(['message' => 'Business setup required'], 400);

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
}
