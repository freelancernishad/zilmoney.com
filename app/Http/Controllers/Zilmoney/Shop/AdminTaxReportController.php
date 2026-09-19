<?php

namespace App\Http\Controllers\Zilmoney\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminTaxReportController extends Controller
{
    /**
     * Get detailed Sales Tax & Revenue Report with filters.
     */
    public function index(Request $request)
    {
        $query = Order::with('items');

        // 1. Date Range Filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Always query ONLY paid orders for tax reports
        $query->where('payment_status', 'paid');

        // 3. Order Status Filter
        if ($request->filled('order_status') && strtolower($request->order_status) !== 'all') {
            $query->where('order_status', strtolower($request->order_status));
        }

        // 4. Search Filter (Order #, Name, Email, Phone, City, State)
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('shipping_address->stateVal', 'like', "%{$search}%")
                    ->orWhere('shipping_address->city', 'like', "%{$search}%");
            });
        }

        $allOrders = $query->orderBy('created_at', 'desc')->get();

        // 5. State Filter
        $selectedState = $request->input('state');
        if ($selectedState && strtoupper($selectedState) !== 'ALL') {
            $allOrders = $allOrders->filter(function ($ord) use ($selectedState) {
                $st = strtoupper($selectedState);
                $stateVal = strtoupper($ord->custom_check_details['tax_state'] ?? $ord->shipping_address['stateVal'] ?? '');
                return str_contains($stateVal, $st);
            });
        }

        // Calculate Summary Metrics
        $totalOrdersCount = $allOrders->count();
        $totalTaxCollected = 0.00;
        $totalTaxableSales = 0.00;
        $totalGrossRevenue = 0.00;
        $totalShippingFees = 0.00;
        $taxOrdersCount = 0;

        $stateBreakdownMap = [];

        $formattedOrders = $allOrders->map(function ($ord) use (
            &$totalTaxCollected,
            &$totalTaxableSales,
            &$totalGrossRevenue,
            &$totalShippingFees,
            &$taxOrdersCount,
            &$stateBreakdownMap
        ) {
            $details = $ord->custom_check_details ?? [];
            $taxAmount = (float) ($details['tax_amount'] ?? 0);
            $taxRate = (float) ($details['tax_rate'] ?? 0);
            $taxState = $details['tax_state'] ?? ($ord->shipping_address['stateVal'] ?? 'N/A');
            $itemsSubtotal = (float) ($details['items_subtotal'] ?? $ord->items->sum('total_price'));

            $deliveryPrice = (float) ($details['delivery_price'] ?? 0);
            if ($deliveryPrice == 0 && $ord->total_amount > ($itemsSubtotal + $taxAmount)) {
                $deliveryPrice = round($ord->total_amount - $itemsSubtotal - $taxAmount, 2);
            }

            $totalTaxCollected += $taxAmount;
            $totalGrossRevenue += $ord->total_amount;
            $totalShippingFees += $deliveryPrice;

            if ($taxAmount > 0) {
                $totalTaxableSales += $itemsSubtotal;
                $taxOrdersCount++;
            }

            // Group State Breakdown
            $stateKey = strtoupper(explode('-', $taxState)[0] ?? 'OTHER');
            $stateKey = trim($stateKey);
            if (!isset($stateBreakdownMap[$stateKey])) {
                $stateBreakdownMap[$stateKey] = [
                    'state' => $stateKey,
                    'full_state' => $taxState,
                    'tax_rate' => $taxRate,
                    'orders_count' => 0,
                    'taxable_sales' => 0.00,
                    'tax_collected' => 0.00,
                    'gross_revenue' => 0.00,
                ];
            }
            $stateBreakdownMap[$stateKey]['orders_count']++;
            $stateBreakdownMap[$stateKey]['taxable_sales'] += $itemsSubtotal;
            $stateBreakdownMap[$stateKey]['tax_collected'] += $taxAmount;
            $stateBreakdownMap[$stateKey]['gross_revenue'] += $ord->total_amount;

            return [
                'id' => $ord->id,
                'order_number' => $ord->order_number,
                'customer_name' => $ord->customer_name,
                'customer_email' => $ord->customer_email,
                'customer_phone' => $ord->customer_phone,
                'shipping_address' => $ord->shipping_address,
                'subtotal' => round($itemsSubtotal, 2),
                'delivery_price' => round($deliveryPrice, 2),
                'tax_amount' => round($taxAmount, 2),
                'tax_rate' => $taxRate,
                'tax_state' => $taxState,
                'total_amount' => round($ord->total_amount, 2),
                'payment_status' => $ord->payment_status,
                'order_status' => $ord->order_status,
                'created_at' => $ord->created_at->toIso8601String(),
                'items' => $ord->items,
            ];
        });

        $avgTaxPerOrder = $totalOrdersCount > 0 ? round($totalTaxCollected / $totalOrdersCount, 2) : 0.00;

        return response()->json([
            'status' => 'success',
            'summary' => [
                'total_tax_collected' => round($totalTaxCollected, 2),
                'total_taxable_sales' => round($totalTaxableSales, 2),
                'total_gross_revenue' => round($totalGrossRevenue, 2),
                'total_shipping_fees' => round($totalShippingFees, 2),
                'total_orders_count' => $totalOrdersCount,
                'tax_paying_orders_count' => $taxOrdersCount,
                'average_tax_per_order' => $avgTaxPerOrder,
            ],
            'state_breakdown' => array_values($stateBreakdownMap),
            'orders' => $formattedOrders->values(),
        ]);
    }

    /**
     * Export Tax Reports as downloadable CSV file.
     */
    public function exportCsv(Request $request)
    {
        $response = $this->index($request);
        $data = json_decode($response->getContent(), true);

        $orders = $data['orders'] ?? [];

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales_tax_report_' . date('Y-m-d_H-i') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Order Number',
                'Date',
                'Customer Name',
                'Customer Email',
                'State / Region',
                'City',
                'ZIP Code',
                'Subtotal ($)',
                'Shipping Fee ($)',
                'Tax Rate (%)',
                'Tax Collected ($)',
                'Grand Total ($)',
                'Payment Status',
                'Order Status',
            ]);

            foreach ($orders as $ord) {
                fputcsv($file, [
                    $ord['order_number'],
                    date('Y-m-d H:i', strtotime($ord['created_at'])),
                    $ord['customer_name'],
                    $ord['customer_email'],
                    $ord['tax_state'],
                    $ord['shipping_address']['city'] ?? '',
                    $ord['shipping_address']['zipCode'] ?? '',
                    number_format($ord['subtotal'], 2),
                    number_format($ord['delivery_price'], 2),
                    $ord['tax_rate'] . '%',
                    number_format($ord['tax_amount'], 2),
                    number_format($ord['total_amount'], 2),
                    strtoupper($ord['payment_status']),
                    strtoupper($ord['order_status']),
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
