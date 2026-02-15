<?php

namespace App\Http\Controllers\Zilmoney;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Zilmoney\PlaidService;

class PlaidWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Log the start of the webhook
        \Log::info('--------------------------------------------------');
        \Log::info('Plaid Webhook Received - START');
        \Log::info('Payload:', $request->all());

        $type = $request->input('webhook_type');
        $code = $request->input('webhook_code');
        $itemId = $request->input('item_id');

        \Log::info("Webhook Type: $type, Code: $code, Item ID: $itemId");

        try {
            // Retrieve item by item_id (might be empty for LINK webhooks)
            $plaidItem = null;
            if ($itemId) {
                 $plaidItem = \App\Models\Zilmoney\PlaidItem::where('item_id', $itemId)->first();
                 if ($plaidItem) {
                     \Log::info("Found Plaid Item: ID {$plaidItem->id} (User: {$plaidItem->user_id})");
                 } else {
                     \Log::warning("Plaid Item NOT FOUND for Item ID: $itemId");
                 }
            } else {
                \Log::info("No Item ID provided in webhook (Expected for SESSION_FINISHED)");
            }

            // Only error if we expect an item but don't find one for ITEM/TRANSACTIONS webhooks
            if (!$plaidItem && $type !== 'LINK') {
                \Log::warning("Plaid Webhook: Item not found for ID {$itemId}, Type: {$type}. Aborting.");
                return response()->json(['message' => 'Item not found'], 404);
            }

            // Delegate to Service
            \Log::info("Delegating to PlaidService::processWebhook...");
            $service = new PlaidService();
            $service->processWebhook($plaidItem, $type, $code, $request->all());
            
            \Log::info('Plaid Webhook Processed - SUCCESS');
            \Log::info('--------------------------------------------------');

            return response()->json(['message' => 'Webhook processed']);

        } catch (\Exception $e) {
            \Log::error('Plaid Webhook Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json(['message' => 'Error processing webhook'], 500);
        }
    }
}
