<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>Payment Received - Order #{{ $order->order_number }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            -webkit-text-size-adjust: 100%;
        }
        table {
            border-collapse: collapse;
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
            background-color: #10b981;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        }
        .brand-header {
            padding: 28px 36px 18px 36px;
            text-align: center;
            background-color: #ffffff;
        }
        .brand-logo-link {
            display: inline-block;
            text-decoration: none;
            outline: none;
            border: none;
            color: #f59e0b;
        }
        .status-badge {
            display: inline-block;
            margin-top: 14px;
            padding: 5px 16px;
            background-color: #ecfdf5;
            color: #059669;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
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
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    @php
        $siteUrl = rtrim($frontendUrl ?? config('app.frontend_url', env('FRONTEND_URL', 'https://goldenmark.money')), '/');
        $logoUrl = 'https://goldenmark.money/logo.png';
    @endphp
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 24px 10px; margin: 0; width: 100%;">
        <tr>
            <td align="center">
                <div class="email-container" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); text-align: left;">
                    <!-- Top Accent Bar -->
                    <div class="header-top-bar" style="height: 6px; background-color: #10b981; background: linear-gradient(90deg, #10b981 0%, #059669 100%); line-height: 6px; font-size: 6px;"></div>

                    <!-- Brand Header -->
                    <div class="brand-header" style="padding: 28px 36px 18px 36px; text-align: center; background-color: #ffffff;">
                        <a href="{{ $siteUrl }}" target="_blank" class="brand-logo-link" style="text-decoration: none; display: inline-block; border: none; outline: none; color: #f59e0b;">
                            <img src="{{ $logoUrl }}" alt="GoldenMark Money" width="200" height="63" style="display: block; width: 200px; max-width: 200px; height: auto; margin: 0 auto; border: 0; outline: none; text-decoration: none; color: #f59e0b; font-size: 20px; font-weight: 900;" />
                        </a>
                        <div style="margin-top: 14px;">
                            <span class="status-badge" style="display: inline-block; padding: 5px 16px; background-color: #ecfdf5; color: #059669; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; border-radius: 9999px; border: 1px solid #a7f3d0;">
                                Payment Confirmed &bull; In Production
                            </span>
                        </div>
                    </div>

                    <div class="content" style="padding: 10px 36px 36px 36px;">
                        <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 12px;">Thank you, {{ $order->customer_name }}!</h2>
                        <p style="font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 16px;">
                            We have received your payment of <strong>${{ number_format($order->total_amount, 2) }} USD</strong> for Order <strong>#{{ $order->order_number }}</strong>.
                        </p>
                        <p style="font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px;">
                            Your order has officially moved to the <strong>Processing & Printing</strong> stage. Our production facility is preparing your secure MICR checks. You will receive tracking information once your package has shipped.
                        </p>
                    </div>

                    <div class="footer" style="background-color: #f1f5f9; padding: 24px 36px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; line-height: 1.5;">
                        <p style="margin: 0 0 6px 0;">&copy; {{ date('Y') }} GoldenMark Money Inc. All rights reserved.</p>
                        <p style="margin: 0;">Need assistance or wish to track your order? Reply to this email or visit our help center.</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
