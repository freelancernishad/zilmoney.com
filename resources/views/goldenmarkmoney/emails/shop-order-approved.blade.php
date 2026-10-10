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
            background-color: #f59e0b;
            background: linear-gradient(90deg, #f59e0b 0%, #ea580c 100%);
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
            background-color: #fef3c7;
            color: #b45309;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-radius: 9999px;
            border: 1px solid #fde68a;
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
            background-color: #f59e0b;
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
    @php
        $siteUrl = rtrim($frontendUrl ?? config('app.frontend_url', env('FRONTEND_URL', 'https://goldenmark.money')), '/');
        $logoUrl = 'https://goldenmark.money/logo.png';
    @endphp
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 24px 10px; margin: 0; width: 100%;">
        <tr>
            <td align="center">
                <div class="email-container" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); text-align: left;">
                    <!-- Top Accent Bar -->
                    <div class="header-top-bar" style="height: 6px; background-color: #f59e0b; background: linear-gradient(90deg, #f59e0b 0%, #ea580c 100%); line-height: 6px; font-size: 6px;"></div>

                    <!-- Brand Header -->
                    <div class="brand-header" style="padding: 28px 36px 18px 36px; text-align: center; background-color: #ffffff;">
                        <a href="{{ $siteUrl }}" target="_blank" class="brand-logo-link" style="text-decoration: none; display: inline-block; border: none; outline: none; color: #f59e0b;">
                            <img src="{{ $logoUrl }}" alt="GoldenMark Money" width="200" height="63" style="display: block; width: 200px; max-width: 200px; height: auto; margin: 0 auto; border: 0; outline: none; text-decoration: none; color: #f59e0b; font-size: 20px; font-weight: 900;" />
                        </a>
                        <div style="margin-top: 14px;">
                            <span class="status-badge" style="display: inline-block; padding: 5px 16px; background-color: #fef3c7; color: #b45309; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; border-radius: 9999px; border: 1px solid #fde68a;">
                                Approved &bull; Awaiting Payment
                            </span>
                        </div>
                    </div>

                    <div class="content" style="padding: 10px 36px 36px 36px;">
                        <h2 class="greeting" style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 12px;">Hello {{ $order->customer_name }},</h2>
                        <p class="lead-text" style="font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px;">
                            Great news! Our verification team has reviewed and <strong>approved</strong> your custom check design and banking details for Order <strong>#{{ $order->order_number }}</strong>.
                        </p>
                        <p class="lead-text" style="font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px;">
                            To move your order into production (MICR laser encoding, verification proofing, and expedited printing), please complete your payment using our secure payment gateway below:
                        </p>

                        @php
                            $customDetails = is_array($order->custom_check_details) ? $order->custom_check_details : [];
                            $deliveryPrice = (float) ($customDetails['delivery_price'] ?? 0);
                            $taxAmount = (float) ($customDetails['tax_amount'] ?? 0);
                        @endphp

                        <div class="order-card" style="background-color: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0; padding: 20px; margin-bottom: 24px;">
                            <div class="order-card-title" style="font-size: 12px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Order Summary (#{{ $order->order_number }})</div>

                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100%; border-collapse: collapse;">
                                @if($order->items && count($order->items) > 0)
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td align="left" valign="top" style="padding: 8px 0; border-bottom: 1px dashed #e2e8f0;">
                                                <div style="font-weight: 600; color: #1e293b; font-size: 13px; line-height: 1.4;">{{ $item->product_title }}</div>
                                                <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                                    Qty: {{ $item->quantity }} checks
                                                    @if($item->selected_color) &bull; Color: {{ $item->selected_color }} @endif
                                                </div>
                                            </td>
                                            <td align="right" valign="top" style="padding: 8px 0 8px 16px; font-weight: 700; color: #0f172a; font-size: 13px; white-space: nowrap; border-bottom: 1px dashed #e2e8f0;">
                                                ${{ number_format($item->total_price, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                @if($deliveryPrice > 0)
                                    <tr>
                                        <td align="left" valign="middle" style="padding: 8px 0; font-size: 12px; color: #64748b; border-bottom: 1px dashed #e2e8f0;">
                                            Shipping & Expedited Printing:
                                        </td>
                                        <td align="right" valign="middle" style="padding: 8px 0 8px 16px; font-size: 12px; font-weight: 600; color: #1e293b; white-space: nowrap; border-bottom: 1px dashed #e2e8f0;">
                                            ${{ number_format($deliveryPrice, 2) }}
                                        </td>
                                    </tr>
                                @endif

                                @if($taxAmount > 0)
                                    <tr>
                                        <td align="left" valign="middle" style="padding: 8px 0; font-size: 12px; color: #64748b; border-bottom: 1px dashed #e2e8f0;">
                                            Estimated Sales Tax:
                                        </td>
                                        <td align="right" valign="middle" style="padding: 8px 0 8px 16px; font-size: 12px; font-weight: 600; color: #1e293b; white-space: nowrap; border-bottom: 1px dashed #e2e8f0;">
                                            ${{ number_format($taxAmount, 2) }}
                                        </td>
                                    </tr>
                                @endif

                                <tr>
                                    <td align="left" valign="middle" style="padding-top: 14px; font-size: 15px; font-weight: 800; color: #0f172a;">
                                        Total Due:
                                    </td>
                                    <td align="right" valign="middle" style="padding-top: 14px; font-size: 16px; font-weight: 800; color: #d97706; white-space: nowrap;">
                                        ${{ number_format($order->total_amount, 2) }} USD
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="cta-container" style="text-align: center; margin: 32px 0;">
                            <a href="{{ $paymentLink }}" target="_blank" class="cta-button" style="display: inline-block; background-color: #f59e0b; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff !important; font-size: 16px; font-weight: 800; text-decoration: none; padding: 16px 36px; border-radius: 12px; box-shadow: 0 10px 20px -5px rgba(245, 158, 11, 0.4); letter-spacing: 0.3px;">
                                Complete Secure Payment (${{ number_format($order->total_amount, 2) }}) &rarr;
                            </a>
                        </div>

                        <p style="font-size: 12px; color: #64748b; text-align: center; margin-top: 10px; line-height: 1.5;">
                            Link not opening? Copy and paste this URL into your browser:<br>
                            <a href="{{ $paymentLink }}" style="color: #d97706; word-break: break-all; text-decoration: underline;">{{ $paymentLink }}</a>
                        </p>
                    </div>

                    <div class="footer" style="background-color: #f1f5f9; padding: 24px 36px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; line-height: 1.5;">
                        <p style="margin: 0 0 6px 0;">&copy; {{ date('Y') }} GoldenMark Money Inc. All rights reserved.</p>
                        <p style="margin: 0;">Need assistance or wish to modify your order? Reply to this email or visit our help center.</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
