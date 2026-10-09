<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Received - Order #{{ $order->order_number }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
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
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        }
        .brand-header {
            padding: 30px 36px 20px 36px;
            text-align: center;
        }
        .brand-logo-text {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            text-decoration: none;
        }
        .brand-logo-text span {
            color: #10b981;
        }
        .status-badge {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 16px;
            background-color: #ecfdf5;
            color: #059669;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            border-radius: 9999px;
            border: 1px solid #a7f3d0;
        }
        .content {
            padding: 10px 36px 36px 36px;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 24px 36px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
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
            <span class="status-badge">Payment Confirmed • In Production</span>
        </div>
        <div class="content">
            <h2 style="font-size: 18px; color: #0f172a;">Thank you, {{ $order->customer_name }}!</h2>
            <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                We have received your payment of <strong>${{ number_format($order->total_amount, 2) }} USD</strong> for Order <strong>#{{ $order->order_number }}</strong>.
            </p>
            <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                Your order has officially moved to the <strong>Processing & Printing</strong> stage. Our production facility is preparing your secure MICR checks. You will receive tracking information once your package has shipped.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} GoldenMark Money Inc. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
