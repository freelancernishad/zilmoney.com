<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-check Payment Received - GoldenMark Money</title>
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
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
        }
        .header-top-bar {
            height: 5px;
            background: linear-gradient(90deg, #f59e0b 0%, #ea580c 100%);
        }
        .brand-header {
            padding: 32px 36px 20px 36px;
            background: #ffffff;
            text-align: center;
        }
        .brand-logo-text {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .brand-badge {
            display: inline-block;
            margin-top: 10px;
            padding: 4px 14px;
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .content-body {
            padding: 10px 36px 36px 36px;
        }
        .hero-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 12px 0;
            text-align: center;
        }
        .hero-subtitle {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin: 0 0 24px 0;
            text-align: left;
        }
        .amount-card {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1px solid #fde68a;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            margin: 24px 0;
        }
        .amount-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #b45309;
            margin-bottom: 6px;
        }
        .amount-value {
            font-size: 36px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -1px;
        }
        .details-card {
            background-color: #f8fafc;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 20px 24px;
            margin: 24px 0;
        }
        .details-header {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .details-table {
            width: 100%;
        }
        .details-table td {
            padding: 8px 0;
            font-size: 14px;
            color: #475569;
        }
        .details-table .label {
            font-weight: 600;
            color: #64748b;
            width: 40%;
        }
        .details-table .value {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
        }
        .btn-wrapper {
            text-align: center;
            margin: 28px 0 16px 0;
        }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            padding: 14px 34px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
        }
        .secondary-link {
            text-align: center;
            margin: 12px 0 0 0;
            font-size: 13px;
        }
        .secondary-link a {
            color: #d97706;
            font-weight: 600;
            text-decoration: underline;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px 36px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
        }
        .footer-links a {
            color: #d97706;
            text-decoration: none;
            font-weight: 600;
            margin: 0 6px;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 20px 10px;">
        <tr>
            <td align="center">
                <div class="email-container">
                    <!-- Top Accent Bar -->
                    <div class="header-top-bar"></div>

                    <!-- Brand Header -->
                    <div class="brand-header">
                        <div class="brand-logo-text">
                            <span style="color: #0f172a;">GoldenMark</span>
                            <span style="color: #f59e0b;">Money</span>
                        </div>
                        <div>
                            <span class="brand-badge">E-Check Payment</span>
                        </div>
                    </div>

                    <!-- Content Body -->
                    <div class="content-body">
                        <h1 class="hero-title">You've Received an E-Check!</h1>
                        <p class="hero-subtitle">
                            Dear <strong>{{ $payeeName }}</strong>,<br><br>
                            A check payment from <strong>{{ $payorName }}</strong> has been issued to you. You can securely view and print your check using the button below.
                        </p>

                        <!-- Amount Display Banner -->
                        <div class="amount-card">
                            <div class="amount-label">Payment Amount</div>
                            <div class="amount-value">${{ number_format($amount, 2) }}</div>
                        </div>

                        <!-- Print Check Action Button -->
                        <div class="btn-wrapper">
                            <a href="{{ $printUrl }}" target="_blank" class="btn-primary">Print Your Check</a>
                        </div>

                        <!-- Check Details Table -->
                        <div class="details-card">
                            <div class="details-header">Payment Summary</div>
                            <table class="details-table" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="label">Payor:</td>
                                    <td class="value">{{ $payorName }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Check Number:</td>
                                    <td class="value"><span style="font-family: monospace;">{{ $checkNumber }}</span></td>
                                </tr>
                                @if(!empty($memo))
                                <tr>
                                    <td class="label">Check Memo:</td>
                                    <td class="value">{{ $memo }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="label">Mailing Type:</td>
                                    <td class="value">Email (E-Check)</td>
                                </tr>
                                <tr>
                                    <td class="label">Date Processed:</td>
                                    <td class="value">{{ $dateProcessed }}</td>
                                </tr>
                                @if(!empty($comment))
                                <tr>
                                    <td class="label">Comment:</td>
                                    <td class="value">{{ $comment }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>

                        <div class="secondary-link">
                            Track status: <a href="{{ $trackUrl }}" target="_blank">Track Check Status</a> &bull; 
                            <a href="{{ $loginUrl }}" target="_blank">Login to Account</a>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <p style="margin: 0 0 10px 0; font-weight: 700; color: #0f172a;">GoldenMark Money®</p>
                        <p style="margin: 0 0 10px 0; color: #64748b;">
                            74-09 37th Avenue Suite 203B, Jackson Heights, NY 11372<br>
                            Tel: 833 711 4030 &bull; Email: support@goldenmark.money
                        </p>
                        <div class="footer-links" style="margin-bottom: 12px;">
                            <a href="{{ config('app.url') }}/privacy-policy">Privacy Policy</a> &bull;
                            <a href="{{ config('app.url') }}/terms">Terms of Service</a> &bull;
                            <a href="{{ config('app.url') }}/contact">Support</a>
                        </div>
                        <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                            &copy; {{ date('Y') }} GoldenMark Money®. All rights reserved. Powered by ZSI.ai.
                        </p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
