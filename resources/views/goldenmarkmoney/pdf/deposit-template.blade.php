<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Deposit Slip #{{ $deposit->ref_id }}</title>
    <style>
        @page { margin: 0; size: letter; }
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; margin: 0; padding: 0; color: #333; }
        
        .container {
            width: 100%;
            height: 10.5in;
            padding: 0.5in;
            box-sizing: border-box;
            position: relative;
        }

        .header {
            border-bottom: 2px solid #2E375B;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #2E375B;
            text-transform: uppercase;
        }

        .meta-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            color: #555;
            width: 1.2in;
        }

        .value {
            color: #111;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2E375B;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .item-table th {
            background-color: #f4f7f9;
            color: #2E375B;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #ddd;
            font-size: 11px;
        }

        .item-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .summary-box {
            float: right;
            width: 3in;
            margin-top: 20px;
            border: 1px solid #ddd;
            background: #fdfdfd;
            padding: 10px;
            border-radius: 4px;
        }

        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .summary-label {
            display: table-cell;
            font-weight: bold;
            color: #555;
        }

        .summary-val {
            display: table-cell;
            text-align: right;
            font-weight: bold;
            font-size: 13px;
        }

        .summary-total {
            border-top: 2px solid #2E375B;
            padding-top: 8px;
            color: #2E375B;
            font-size: 15px;
        }

        .clear {
            clear: both;
        }

        /* MICR line styling at the bottom */
        .micr-container {
            position: absolute;
            bottom: 0.5in;
            left: 0.5in;
            right: 0.5in;
            text-align: center;
            border-top: 1px dashed #ccc;
            padding-top: 20px;
        }

        .micr-line {
            font-family: monospace;
            font-size: 18px;
            letter-spacing: 3px;
            color: #111;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <div class="title">Deposit Slip</div>
                    </td>
                    <td class="text-right" style="font-size: 11px; color: #666;">
                        Generated on {{ date('m/d/Y h:i A') }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Meta info (Company & Bank) -->
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div style="font-weight: bold; font-size: 13px; color: #2E375B; margin-bottom: 5px;">DEPOSITOR (BUSINESS DETAILS)</div>
                    <div style="font-weight: bold; font-size: 14px;">{{ $deposit->businessDetail->legal_business_name ?? $deposit->businessDetail->business_name }}</div>
                    <div style="line-height: 1.4; color: #555; margin-top: 3px;">
                        {{ $deposit->businessDetail->address }}<br>
                        {{ $deposit->businessDetail->city }}, {{ $deposit->businessDetail->state }} {{ $deposit->businessDetail->zip }}<br>
                        Email: {{ $deposit->businessDetail->email ?? 'N/A' }}
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                    <div style="font-weight: bold; font-size: 13px; color: #2E375B; margin-bottom: 5px;">BANK TO DEPOSIT</div>
                    <div style="font-weight: bold; font-size: 14px;">{{ $deposit->account->official_name ?? $deposit->account->account_nick_name ?? 'Bank Name' }}</div>
                    <div style="line-height: 1.4; color: #555; margin-top: 3px;">
                        Account Number: ******{{ substr($deposit->account->account_number, -4) }}<br>
                        Routing Number: {{ $deposit->account->routing_number }}<br>
                        Bank Name: {{ $deposit->account->bank_name ?? 'N/A' }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Deposit Details -->
        <div class="section-title">Deposit Details</div>
        <table class="meta-table">
            <tr>
                <td class="label">Date:</td>
                <td class="value">{{ $deposit->date->format('m/d/Y') }}</td>
                <td class="label">Ref ID:</td>
                <td class="value">{{ $deposit->ref_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Deposit From:</td>
                <td class="value" colspan="3">{{ $deposit->deposit_from ?? 'N/A' }}</td>
            </tr>
            @if($deposit->memo)
            <tr>
                <td class="label">Memo:</td>
                <td class="value" colspan="3">{{ $deposit->memo }}</td>
            </tr>
            @endif
        </table>

        <!-- Cash Breakdown -->
        @if(!$deposit->blank_deposit_slip && $deposit->cash_items && count($deposit->cash_items) > 0)
        <div class="section-title">Cash Breakdown</div>
        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 8%">#</th>
                    <th style="width: 30%">Cashier/Clerk</th>
                    <th>Note</th>
                    <th style="width: 25%" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deposit->cash_items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['cashier_clerk'] ?? 'N/A' }}</td>
                    <td>{{ $item['note'] ?? '' }}</td>
                    <td class="text-right">${{ number_format((float)($item['amount'] ?? 0), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Check Breakdown -->
        @if(!$deposit->blank_deposit_slip && $deposit->check_items && count($deposit->check_items) > 0)
        <div class="section-title">Check Breakdown</div>
        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 8%">#</th>
                    <th style="width: 25%">From</th>
                    <th style="width: 15%">Check #</th>
                    <th style="width: 20%">Cashier/Clerk</th>
                    <th>Note</th>
                    <th style="width: 20%" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deposit->check_items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['from'] ?? 'N/A' }}</td>
                    <td>{{ $item['check_number'] ?? 'N/A' }}</td>
                    <td>{{ $item['cashier_clerk'] ?? 'N/A' }}</td>
                    <td>{{ $item['note'] ?? '' }}</td>
                    <td class="text-right">${{ number_format((float)($item['amount'] ?? 0), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($deposit->blank_deposit_slip)
        <div style="border: 2px dashed #ccc; padding: 40px; text-align: center; color: #777; margin-top: 30px; font-weight: bold; border-radius: 8px;">
            BLANK DEPOSIT SLIPTEMPLATE FOR MANUAL ENTRY
        </div>
        @endif

        <!-- Summary -->
        <div class="summary-box">
            <div class="summary-row">
                <div class="summary-label">Cash Total:</div>
                <div class="summary-val">${{ number_format($cashTotal, 2) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Check Total:</div>
                <div class="summary-val">${{ number_format($checkTotal, 2) }}</div>
            </div>
            <div class="summary-row summary-total">
                <div class="summary-label">Net Deposit:</div>
                <div class="summary-val">${{ number_format($totalAmount, 2) }}</div>
            </div>
        </div>

        <div class="clear"></div>

        <!-- MICR Code at Bottom -->
        <div class="micr-container">
            <div class="micr-line">
                d{{ $deposit->account->routing_number }}d t{{ $deposit->account->account_number }}t 0{{ $deposit->ref_id ? filter_var($deposit->ref_id, FILTER_SANITIZE_NUMBER_INT) : '00' }}
            </div>
            <div style="font-size: 9px; color: #888; margin-top: 5px; text-transform: uppercase; font-weight: bold;">
                Do not write below this line - Transit MICR Encoding
            </div>
        </div>
    </div>

</body>
</html>
