<?php

namespace App\Http\Controllers\Zilmoney\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\Order;
use App\Models\Shop\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Get paginated orders list with search and status filtering.
     */
    public function index(Request $request)
    {
        $query = Order::with('items');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('order_status', $request->input('status'));
        }

        $perPage = (int) ($request->input('per_page') ?? $request->input('perPage') ?? 15);
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * Get details for a single order.
     */
    public function show($id)
    {
        $order = Order::with('items')->where('id', $id)->orWhere('order_number', $id)->firstOrFail();
        return response()->json($order);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'shipping_address' => 'nullable|array',
            'billing_address' => 'nullable|array',
            'total_amount' => 'required|numeric',
            'payment_status' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'custom_check_details' => 'nullable|array',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.product_title' => 'required|string',
            'items.*.item_code' => 'nullable|string',
            'items.*.selected_color' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
            'items.*.total_price' => 'required|numeric',
        ]);

        $primaryCustomDetails = !empty($validated['items'][0]['customCheckDetails']) && is_array($validated['items'][0]['customCheckDetails'])
            ? $validated['items'][0]['customCheckDetails']
            : [];

        $customCheckDetails = array_merge(
            $primaryCustomDetails,
            $validated['custom_check_details'] ?? [],
            ['items_raw' => $validated['items']]
        );

        $order = Order::create([
            'order_number' => $orderNumber,
            'user_id' => auth()->id() ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
            'billing_address' => $validated['billing_address'] ?? null,
            'total_amount' => $validated['total_amount'],
            'payment_status' => $validated['payment_status'] ?? 'paid',
            'payment_method' => $validated['payment_method'] ?? 'Stripe Credit Card',
            'order_status' => 'processing',
            'custom_check_details' => $customCheckDetails,
        ]);

        foreach ($validated['items'] as $item) {
            $itemCustomDetails = $item['customCheckDetails'] ?? $item['custom_check_details'] ?? null;
            $order->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'product_title' => $item['product_title'],
                'item_code' => $item['item_code'] ?? null,
                'selected_color' => $item['selected_color'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
                'custom_check_details' => $itemCustomDetails,
            ]);
        }

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order->load('items')
        ], 201);
    }

    /**
     * Update order status and tracking number.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'order_status' => 'required|string|in:processing,printed_shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        $order->order_status = $validated['order_status'];
        if ($request->has('tracking_number')) {
            $order->tracking_number = $validated['tracking_number'];
        }
        $order->save();

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order->load('items')
        ]);
    }

    /**
     * Update order custom check design details and layout configuration.
     */
    public function updateCheckDetails(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'custom_check_details' => 'required|array',
        ]);

        $existingDetails = is_array($order->custom_check_details) ? $order->custom_check_details : [];
        $mergedDetails = array_merge($existingDetails, $validated['custom_check_details']);

        $order->custom_check_details = $mergedDetails;
        $order->save();

        // Also update individual shop_order_items if items_raw is updated
        if (isset($mergedDetails['items_raw']) && is_array($mergedDetails['items_raw'])) {
            $orderItems = $order->items()->orderBy('id', 'asc')->get();
            foreach ($mergedDetails['items_raw'] as $idx => $rawItem) {
                if (isset($orderItems[$idx])) {
                    $itemCustom = $rawItem['customCheckDetails'] ?? $rawItem['custom_check_details'] ?? null;
                    if ($itemCustom) {
                        $orderItems[$idx]->custom_check_details = $itemCustom;
                        $orderItems[$idx]->save();
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Order check design configuration updated successfully',
            'order' => $order->load('items')
        ]);
    }

    /**
     * Delete an order.
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}
