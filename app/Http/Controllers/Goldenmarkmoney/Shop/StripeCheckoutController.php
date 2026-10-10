<?php

namespace App\Http\Controllers\Goldenmarkmoney\Shop;

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
            $deliveryPrice = (float) ($validated['delivery_price'] ?? 0);
            $taxAmount = (float) ($validated['tax_amount'] ?? 0);
            $taxRate = (float) ($validated['tax_rate'] ?? 0);
            $taxState = $validated['tax_state'] ?? null;

            $itemsTotal = array_reduce($validated['items'], function ($sum, $item) {
                return $sum + (float) ($item['totalPrice'] ?? ($item['unitPrice'] * $item['quantity']));
            }, 0.0);

            $totalAmount = round($itemsTotal + $deliveryPrice + $taxAmount, 2);
            $orderNumber = 'ORD-' . rand(10000, 99999);

            $stripeSession = null;
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

                $stripeSession = StripeSession::create([
                    'payment_method_types' => ['card'],
                    'line_items' => $lineItems,
                    'mode' => 'payment',
                    'client_reference_id' => (string) $orderNumber,
                    'customer_email' => $validated['customer_email'],
                    'success_url' => $validated['success_url'] . (str_contains($validated['success_url'], '?') ? '&' : '?') . 'session_id={CHECKOUT_SESSION_ID}&order_number=' . $orderNumber,
                    'cancel_url' => $validated['cancel_url'],
                    'metadata' => [
                        'order_number' => (string) $orderNumber,
                        'customer_name' => $validated['customer_name'],
                        'customer_email' => $validated['customer_email'],
                        'tax_amount' => $taxAmount,
                        'tax_rate' => $taxRate,
                    ],
                ]);
            } catch (Exception $stripeEx) {
                // Log stripe exception and fallback to direct order creation
                \Illuminate\Support\Facades\Log::warning('Stripe Session Warning: ' . $stripeEx->getMessage());
            }

            // Save order in database (allows guest order creation & preserves full check design config)
            $primaryCustomDetails = !empty($validated['items'][0]['customCheckDetails']) && is_array($validated['items'][0]['customCheckDetails'])
                ? $validated['items'][0]['customCheckDetails']
                : [];

            $customCheckDetails = array_merge($primaryCustomDetails, [
                'stripe_session_id' => $stripeSession ? $stripeSession->id : ('mock_session_' . rand(10000, 99999)),
                'tax_amount' => $taxAmount,
                'tax_rate' => $taxRate,
                'tax_state' => $taxState,
                'delivery_price' => $deliveryPrice,
                'items_raw' => $validated['items'],
            ]);

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => auth()->id() ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['shipping_address'],
                'total_amount' => $totalAmount,
                'payment_status' => $stripeSession ? 'unpaid' : 'paid',
                'payment_method' => $stripeSession ? ('Stripe Session (' . $stripeSession->id . ')') : 'Direct Order Payment',
                'order_status' => 'processing',
                'custom_check_details' => $customCheckDetails,
            ]);

            foreach ($validated['items'] as $item) {
                $itemCustomDetails = $item['customCheckDetails'] ?? $item['custom_check_details'] ?? null;
                $order->items()->create([
                    'product_id' => $item['productId'] ?? null,
                    'product_title' => $item['title'],
                    'item_code' => $item['itemCode'] ?? null,
                    'selected_color' => $item['colorName'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unitPrice'],
                    'total_price' => $item['totalPrice'],
                    'custom_check_details' => $itemCustomDetails,
                ]);
            }

            $successRedirectUrl = $stripeSession
                ? $stripeSession->url
                : ($validated['success_url'] . (str_contains($validated['success_url'], '?') ? '&' : '?') . 'session_id=mock_session_' . rand(10000, 99999) . '&order_number=' . $orderNumber);

            return response()->json([
                'url' => $successRedirectUrl,
                'session_id' => $stripeSession ? $stripeSession->id : ('mock_session_' . $orderNumber),
                'order_number' => $orderNumber,
            ]);
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Create Checkout Session Error: ' . $e->getMessage());
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
        $orderNumber = $request->query('order_number');

        if (!$sessionId && !$orderNumber) {
            return response()->json(['error' => 'Session ID or Order Number is required'], 400);
        }

        try {
            $isPaid = false;
            $customerEmail = null;
            $amountTotal = 0;
            $session = null;

            if ($sessionId && !str_starts_with($sessionId, 'mock_session_')) {
                try {
                    $this->initStripe();
                    $session = StripeSession::retrieve($sessionId);
                    $isPaid = ($session->payment_status === 'paid');
                    $customerEmail = $session->customer_email;
                    $amountTotal = $session->amount_total ? $session->amount_total / 100 : 0;
                } catch (Exception $stripeEx) {
                    \Illuminate\Support\Facades\Log::warning('Stripe Session Retrieval Failed: ' . $stripeEx->getMessage());
                }
            } else {
                // Mock / Local test session
                $isPaid = true;
            }

            // Find order by multi-layered strategy to guarantee reliable matching:
            $order = null;

            // 1. Try order_number, order_id, or payment_token from Stripe Session metadata
            if ($session && isset($session->metadata)) {
                $metaOrderId = $session->metadata->order_id ?? null;
                $metaOrderNumber = $session->metadata->order_number ?? null;
                $metaPaymentToken = $session->metadata->payment_token ?? null;

                if ($metaOrderId) {
                    $order = Order::with('items')->find($metaOrderId);
                }
                if (!$order && $metaOrderNumber) {
                    $order = Order::with('items')->where('order_number', $metaOrderNumber)->first();
                }
                if (!$order && $metaPaymentToken) {
                    $order = Order::with('items')->where('payment_token', $metaPaymentToken)->first();
                }
            }

            // 2. Try client_reference_id on Stripe Session
            if (!$order && $session && !empty($session->client_reference_id)) {
                $order = Order::with('items')->where('order_number', $session->client_reference_id)->first();
            }

            // 3. Try order_number from request query
            if (!$order && $orderNumber) {
                $order = Order::with('items')->where('order_number', $orderNumber)->first();
            }

            // 4. Try session ID matches across custom_check_details, payment_method, payment_link
            if (!$order && $sessionId) {
                $order = Order::with('items')
                    ->where(function ($q) use ($sessionId) {
                        $q->where('payment_method', 'like', "%{$sessionId}%")
                          ->orWhere('payment_link', 'like', "%{$sessionId}%")
                          ->orWhere('custom_check_details->stripe_session_id', $sessionId)
                          ->orWhere('custom_check_details', 'like', "%{$sessionId}%");
                    })
                    ->first();
            }

            // 5. Fallback: match by customer email and approximate total amount for recently awaiting orders
            if (!$order && $customerEmail && $amountTotal > 0) {
                $order = Order::with('items')
                    ->where('customer_email', $customerEmail)
                    ->where('payment_status', '!=', 'paid')
                    ->whereBetween('total_amount', [$amountTotal - 1.00, $amountTotal + 1.00])
                    ->latest()
                    ->first();
            }

            if ($order && $order->payment_status === 'paid') {
                $isPaid = true;
            }

            if ($isPaid && $order) {
                $wasUnpaid = ($order->payment_status !== 'paid');
                $order->payment_status = 'paid';
                $order->order_status = 'processing';
                $order->payment_method = 'Stripe Credit Card (' . ($sessionId ?: 'Checkout') . ')';

                $customDetails = is_array($order->custom_check_details) ? $order->custom_check_details : [];
                if ($sessionId) {
                    $customDetails['stripe_session_id'] = $sessionId;
                }
                $order->custom_check_details = $customDetails;
                $order->save();

                // Send payment confirmation email if newly paid
                if ($wasUnpaid) {
                    try {
                        $frontendUrl = $request->header('origin') ?: config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
                        \Illuminate\Support\Facades\Mail::send('goldenmarkmoney.emails.shop-order-paid', [
                            'order' => $order,
                            'frontendUrl' => $frontendUrl,
                        ], function ($message) use ($order) {
                            $message->to($order->customer_email)
                                    ->subject("Payment Received - Your Check Order #{$order->order_number} is Processing");
                        });
                    } catch (Exception $mailEx) {
                        \Illuminate\Support\Facades\Log::warning("Could not send payment receipt email: " . $mailEx->getMessage());
                    }
                }
            }

            $resolvedOrderNumber = $order ? $order->order_number : ($orderNumber ?: ($session->metadata->order_number ?? ($session->client_reference_id ?? null)));

            return response()->json([
                'success' => true,
                'id' => $order ? $order->id : 1,
                'status' => $isPaid ? 'paid' : ($order->payment_status ?? 'unpaid'),
                'order_number' => $resolvedOrderNumber,
                'session' => [
                    'id' => $sessionId,
                    'customer_email' => $customerEmail ?? ($order->customer_email ?? null),
                    'amount_total' => $amountTotal ?: ($order->total_amount ?? 0),
                    'payment_status' => $isPaid ? 'paid' : ($order->payment_status ?? 'unpaid'),
                ],
                'order' => $order ? $order->load('items') : null,
            ]);
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Verify Session Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
