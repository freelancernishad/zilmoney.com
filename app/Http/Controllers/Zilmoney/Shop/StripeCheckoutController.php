<?php

namespace App\Http\Controllers\Zilmoney\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\Order;
use App\Models\Shop\OrderItem;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Exception;

class StripeCheckoutController extends Controller
{
    private function initStripe()
    {
        $secretKey = SystemSetting::getValue('STRIPE_SECRET', config('services.stripe.secret'));
        if (!$secretKey) {
            throw new Exception('Stripe API key is not configured in System Settings.');
        }
        Stripe::setApiKey($secretKey);
    }

    /**
     * Create a Stripe Hosted Checkout Session.
     */
    public function createSession(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'shipping_address' => 'required|array',
            'delivery_price' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'tax_rate' => 'nullable|numeric',
            'tax_state' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.productId' => 'nullable',
            'items.*.title' => 'required|string',
            'items.*.itemCode' => 'nullable|string',
            'items.*.colorName' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unitPrice' => 'required|numeric',
            'items.*.totalPrice' => 'required|numeric',
            'items.*.customCheckDetails' => 'nullable|array',
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ]);

        try {
            $this->initStripe();

            $lineItems = [];
            foreach ($validated['items'] as $item) {
                $unitAmount = (int) round(($item['totalPrice'] / $item['quantity']) * 100);
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $item['title'] . (!empty($item['colorName']) ? ' (' . $item['colorName'] . ')' : ''),
                            'description' => 'Personalized Check Order (' . $item['quantity'] . ' checks)',
                        ],
                        'unit_amount' => max(1, $unitAmount),
                    ],
                    'quantity' => (int) $item['quantity'],
                ];
            }

            // Add shipping line item if present
            $deliveryPrice = (float) ($validated['delivery_price'] ?? 0);
            if ($deliveryPrice > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Shipping & Printing Expedite',
                        ],
                        'unit_amount' => (int) round($deliveryPrice * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            // Add estimated sales tax line item if present
            $taxAmount = (float) ($validated['tax_amount'] ?? 0);
            $taxRate = (float) ($validated['tax_rate'] ?? 0);
            $taxState = $validated['tax_state'] ?? null;
            if ($taxAmount > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Estimated Sales Tax' . ($taxRate > 0 ? " ({$taxRate}%)" : ''),
                            'description' => $taxState ? "Sales tax calculated for {$taxState}" : 'State Sales Tax',
                        ],
                        'unit_amount' => (int) round($taxAmount * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'customer_email' => $validated['customer_email'],
                'success_url' => $validated['success_url'] . (str_contains($validated['success_url'], '?') ? '&' : '?') . 'session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $validated['cancel_url'],
                'metadata' => [
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'tax_amount' => $taxAmount,
                    'tax_rate' => $taxRate,
                ],
            ]);

            // Save order in database (allows guest order creation)
            $orderNumber = 'ORD-' . rand(10000, 99999);
            $totalAmount = array_reduce($validated['items'], fn($sum, $i) => $sum + $i['totalPrice'], 0) + $deliveryPrice + $taxAmount;

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => auth()->id() ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['shipping_address'],
                'total_amount' => $totalAmount,
                'payment_status' => 'unpaid',
                'payment_method' => 'Stripe Session (' . $session->id . ')',
                'order_status' => 'processing',
                'custom_check_details' => [
                    'stripe_session_id' => $session->id,
                    'tax_amount' => $taxAmount,
                    'tax_rate' => $taxRate,
                    'tax_state' => $taxState,
                    'items_raw' => $validated['items'],
                ],
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['productId'] ?? null,
                    'product_title' => $item['title'],
                    'item_code' => $item['itemCode'] ?? null,
                    'selected_color' => $item['colorName'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unitPrice'],
                    'total_price' => $item['totalPrice'],
                ]);
            }

            return response()->json([
                'url' => $session->url,
                'session_id' => $session->id,
                'order_number' => $orderNumber,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify completed Stripe Checkout Session & confirm Order payment.
     */
    public function verifySession(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return response()->json(['error' => 'Session ID is required'], 400);
        }

        try {
            $this->initStripe();
            $session = StripeSession::retrieve($sessionId);

            $order = Order::with('items')
                ->where('payment_method', 'like', "%{$sessionId}%")
                ->orWhere('custom_check_details->stripe_session_id', $sessionId)
                ->first();

            if ($session->payment_status === 'paid' && $order) {
                $order->payment_status = 'paid';
                $order->order_status = 'processing';
                $order->save();
            }

            return response()->json([
                'id' => $order ? $order->id : 1,
                'status' => $session->payment_status,
                'session' => [
                    'id' => $session->id,
                    'customer_email' => $session->customer_email,
                    'amount_total' => $session->amount_total ? $session->amount_total / 100 : 0,
                    'payment_status' => $session->payment_status,
                ],
                'order' => $order,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
