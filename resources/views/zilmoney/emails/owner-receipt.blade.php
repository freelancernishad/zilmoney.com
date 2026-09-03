<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Payment Confirmation Receipt - GoldenMark Money</title>
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }
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
        .details-card {
            background-color: #f8fafc;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 24px;
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
        .details-table .amount-highlight {
            font-size: 16px;
            font-weight: 900;
            color: #d97706;
        }
        .btn-wrapper {
            text-align: center;
            margin: 30px 0 10px 0;
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

        /* Dark Mode Override Styles */
        @media (prefers-color-scheme: dark) {
            body, table {
                background-color: #0b0f19 !important;
            }
            .email-container {
                background-color: #1e293b !important;
                border-color: #334155 !important;
            }
            .brand-header {
                background-color: #1e293b !important;
            }
            .brand-dark-text {
                color: #f8fafc !important;
            }
            .brand-badge {
                background-color: #78350f !important;
                color: #fef3c7 !important;
                border-color: #92400e !important;
            }
            .hero-title {
                color: #f8fafc !important;
            }
            .hero-subtitle, .hero-subtitle strong {
                color: #cbd5e1 !important;
            }
            .details-card {
                background-color: #0f172a !important;
                border-color: #334155 !important;
            }
            .details-header {
                color: #94a3b8 !important;
                border-bottom-color: #334155 !important;
            }
            .details-table td {
                color: #cbd5e1 !important;
            }
            .details-table .label {
                color: #94a3b8 !important;
            }
            .details-table .value {
                color: #f8fafc !important;
            }
            .details-table .amount-highlight {
                color: #fbbf24 !important;
            }
            .footer {
                background-color: #0f172a !important;
                border-top-color: #334155 !important;
                color: #94a3b8 !important;
            }
            .footer-title {
                color: #f8fafc !important;
            }
            .footer p {
                color: #94a3b8 !important;
            }
            .footer-links a {
                color: #f59e0b !important;
            }
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
                            <span class="brand-dark-text" style="color: #0f172a;">GoldenMark</span>
                            <span style="color: #f59e0b;">Money</span>
                        </div>
                        <div>
                            <span class="brand-badge">Payment Receipt</span>
                        </div>
                    </div>

                    <!-- Content Body -->
                    <div class="content-body">
                        <h1 class="hero-title">Payment Confirmation</h1>
                        <p class="hero-subtitle">
                            Dear <strong>{{ $ownerName }}</strong>,<br><br>
                            This email confirms that an E-check payment of <strong>${{ number_format($amount, 2) }}</strong> has been successfully issued from <strong>{{ $payorName }}</strong> to <strong>{{ $payeeName }}</strong>.
                        </p>

                        <!-- Transaction Summary Card -->
                        <div class="details-card">
                            <div class="details-header">Transaction Summary</div>
                            <table class="details-table" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="label">Check Number:</td>
                                    <td class="value"><span style="font-family: monospace;">{{ $checkNumber }}</span></td>
                                </tr>
                                <tr>
                                    <td class="label">Payee / Recipient:</td>
                                    <td class="value">{{ $payeeName }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Recipient Email:</td>
                                    <td class="value">{{ $payeeEmail }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Bank / Payor Account:</td>
                                    <td class="value">{{ $payorName }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Amount:</td>
                                    <td class="value amount-highlight">${{ number_format($amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Issue Date:</td>
                                    <td class="value">{{ $dateProcessed }}</td>
                                </tr>
                                @if(!empty($memo))
                                <tr>
                                    <td class="label">Memo:</td>
                                    <td class="value">{{ $memo }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>

                        <!-- Action Button -->
                        <div class="btn-wrapper">
                            <a href="{{ $trackUrl }}" target="_blank" class="btn-primary">View In Dashboard</a>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <p class="footer-title" style="margin: 0 0 10px 0; font-weight: 700; color: #0f172a;">GoldenMark Money®</p>
                        <p style="margin: 0 0 10px 0; color: #64748b;">
                            74-09 37th Avenue Suite 203B, Jackson Heights, NY 11372<br>
                            Tel: 833 711 4030 &bull; Email: support@goldenmark.money
                        </p>
                        <div class="footer-links" style="margin-bottom: 12px;">
                            <a href="{{ config('app.frontend_url') }}/privacy-policy">Privacy Policy</a> &bull;
                            <a href="{{ config('app.frontend_url') }}/terms">Terms of Service</a> &bull;
                            <a href="{{ config('app.frontend_url') }}/contact">Support</a>
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
