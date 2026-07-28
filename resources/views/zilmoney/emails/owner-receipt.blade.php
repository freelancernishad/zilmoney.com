<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoldenMark Money: Payment Confirmation Receipt</title>
</head>
<body style="margin: 0; padding: 40px 10px; background-color: #f8fafc; font-family: 'Segoe UI', Arial, sans-serif; color: #334155; line-height: 1.6;">

    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        
        <!-- Header Band -->
        <div style="background-color: #1e293b; padding: 24px 30px; text-align: left;">
            <span style="font-size: 20px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px;">GoldenMark Money</span>
            <span style="display: block; font-size: 12px; color: #94a3b8; font-weight: 600; margin-top: 4px;">Payment Confirmation Receipt</span>
        </div>

        <!-- Body Content -->
        <div style="padding: 30px 30px 25px 30px;">
            
            <p style="font-size: 15px; margin-top: 0; margin-bottom: 16px; color: #0f172a;">
                Dear <strong style="color: #2563eb;">{{ $ownerName }}</strong>,
            </p>

            <p style="font-size: 14px; color: #475569; margin-bottom: 24px;">
                This email confirms that an E-check payment of <strong style="color: #16a34a; font-size: 16px;">${{ number_format($amount, 2) }}</strong> has been successfully issued from <strong>{{ $payorName }}</strong> to <strong>{{ $payeeName }}</strong>.
            </p>

            <!-- Receipt Box -->
            <div style="background-color: #f1f5f9; border-radius: 10px; padding: 20px; margin-bottom: 25px; border: 1px solid #e2e8f0;">
                <h4 style="margin-top: 0; margin-bottom: 12px; font-size: 13px; font-weight: 800; text-transform: uppercase; color: #64748b; tracking: 0.5px;">Transaction Summary</h4>
                
                <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; color: #334155;">
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Check Number:</td>
                        <td style="padding: 6px 0; font-weight: 700; text-align: right; color: #0f172a;">{{ $checkNumber }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Payee / Recipient:</td>
                        <td style="padding: 6px 0; font-weight: 700; text-align: right; color: #0f172a;">{{ $payeeName }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Recipient Email:</td>
                        <td style="padding: 6px 0; font-weight: 700; text-align: right; color: #2563eb;">{{ $payeeEmail }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Bank Account:</td>
                        <td style="padding: 6px 0; font-weight: 700; text-align: right; color: #0f172a;">{{ $payorName }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Amount:</td>
                        <td style="padding: 6px 0; font-weight: 800; text-align: right; color: #16a34a; font-size: 15px;">${{ number_format($amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Issue Date:</td>
                        <td style="padding: 6px 0; font-weight: 700; text-align: right; color: #0f172a;">{{ $dateProcessed }}</td>
                    </tr>
                    @if(!empty($memo))
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Memo:</td>
                        <td style="padding: 6px 0; font-weight: 700; text-align: right; color: #0f172a;">{{ $memo }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- View Transaction Button -->
            <div style="margin-bottom: 25px;">
                <a href="{{ $trackUrl }}" target="_blank" style="background-color: #2563eb; color: #ffffff; font-size: 13.5px; font-weight: 700; text-decoration: none; padding: 11px 24px; border-radius: 8px; display: inline-block;">
                    View In Dashboard
                </a>
            </div>

            <!-- Sign Off -->
            <div style="font-size: 13px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                Best regards,<br>
                <strong style="color: #334155;">The GoldenMark Money Team</strong>
            </div>

        </div>

        <!-- Footer -->
        <div style="background-color: #f1f5f9; text-align: center; padding: 14px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0;">
            This is an automated payment receipt for your records.
        </div>

    </div>

</body>
</html>
