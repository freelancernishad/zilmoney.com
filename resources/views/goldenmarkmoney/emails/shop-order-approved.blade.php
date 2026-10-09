<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>Your Check Order #{{ $order->order_number }} has been Approved</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            -webkit-text-size-adjust: 100%;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }
        .header-top-bar {
            height: 6px;
            background: linear-gradient(90deg, #f59e0b 0%, #ea580c 100%);
        }
        .brand-header {
            padding: 30px 36px 20px 36px;
            text-align: center;
            background: #ffffff;
        }
        .brand-logo-text {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            text-decoration: none;
            display: inline-block;
        }
        .brand-logo-text span {
            color: #f59e0b;
        }
        .status-badge {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 16px;
            background-color: #ecfdf5;
            color: #047857;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 9999px;
            border: 1px solid #a7f3d0;
        }
        .content {
            padding: 10px 36px 36px 36px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .lead-text {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .order-card {
            background-color: #f8fafc;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 20px;
            margin-bottom: 24px;
        }
        .order-card-title {
            font-size: 12px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 14px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .item-name {
            font-weight: 600;
            color: #1e293b;
        }
        .item-meta {
            font-size: 11px;
            color: #64748b;
        }
        .item-price {
            font-weight: 700;
            color: #0f172a;
            text-align: right;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            padding-top: 8px;
        }
        .cta-container {
            text-align: center;
            margin: 32px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff !important;
            font-size: 16px;
            font-weight: 800;
            text-decoration: none;
            padding: 16px 36px;
            border-radius: 12px;
            box-shadow: 0 10px 20px -5px rgba(245, 158, 11, 0.4);
            letter-spacing: 0.3px;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 24px 36px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header-top-bar"></div>
        <div class="brand-header">
            <a href="{{ $frontendUrl ?? 'http://localhost:3000' }}" class="brand-logo-text">
                GoldenMark <span>Money</span>
            </a>
            <br>
            <span class="status-badge">Approved • Awaiting Payment</span>
        </div>

        <div class="content">
            <h2 class="greeting">Hello {{ $order->customer_name }},</h2>
            <p class="lead-text">
                Great news! Our verification team has reviewed and <strong>approved</strong> your custom check design and banking details for Order <strong>#{{ $order->order_number }}</strong>.
            </p>
            <p class="lead-text">
                To move your order into production (MICR laser encoding, verification proofing, and expedited printing), please complete your payment using our secure payment gateway below:
            </p>

            <div class="order-card">
                <div class="order-card-title">Order Summary (#{{ $order->order_number }})</div>

                @if($order->items && count($order->items) > 0)
                    @foreach($order->items as $item)
                        <div class="item-row">
                            <div>
                                <div class="item-name">{{ $item->product_title }}</div>
                                <div class="item-meta">
                                    Qty: {{ $item->quantity }} checks
                                    @if($item->selected_color) • Color: {{ $item->selected_color }} @endif
                                </div>
                            </div>
                            <div class="item-price">
                                ${{ number_format($item->total_price, 2) }}
                            </div>
                        </div>
                    @endforeach
                @endif

                <div class="total-row">
                    <span>Total Due:</span>
                    <span style="color: #d97706;">${{ number_format($order->total_amount, 2) }} USD</span>
                </div>
            </div>

            <div class="cta-container">
                <a href="{{ $paymentLink }}" target="_blank" class="cta-button">
                    Complete Secure Payment (${{ number_format($order->total_amount, 2) }}) &rarr;
                </a>
            </div>

            <p style="font-size: 12px; color: #64748b; text-align: center; margin-top: 10px;">
                Link not opening? Copy and paste this URL into your browser:<br>
                <a href="{{ $paymentLink }}" style="color: #d97706; word-break: break-all;">{{ $paymentLink }}</a>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} GoldenMark Money Inc. All rights reserved.</p>
            <p>Need assistance or wish to modify your order? Reply to this email or visit our help center.</p>
        </div>
    </div>
</body>
</html>
