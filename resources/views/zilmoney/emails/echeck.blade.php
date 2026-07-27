<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zil Money: E-check Payment</title>
</head>
<body style="margin: 0; padding: 40px 10px; background-color: #eaf7ed; font-family: 'Segoe UI', Arial, sans-serif; color: #333333; line-height: 1.6;">

    <div style="max-width: 620px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
        
        <!-- Email Body Content -->
        <div style="padding: 40px 35px 30px 35px;">
            
            <p style="font-size: 16px; margin-top: 0; margin-bottom: 20px;">
                Dear <strong style="color: #1a73e8;">{{ $payeeName }}</strong>
            </p>

            <p style="font-size: 14px; color: #4a5568; margin-bottom: 25px;">
                A check payment from <strong>{{ $payorName }}</strong> has been issued to you. You can securely view and print your check using the button below.
            </p>

            <!-- Green Print Button -->
            <div style="margin-bottom: 30px;">
                <a href="{{ $printUrl }}" target="_blank" style="background-color: #00c067; color: #ffffff; font-size: 14px; font-weight: bold; text-decoration: none; padding: 12px 28px; border-radius: 8px; display: inline-block; box-shadow: 0 2px 4px rgba(0,192,103,0.3);">
                    Print Your Check
                </a>
            </div>

            <!-- Payment Details List -->
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 15px; font-weight: bold; color: #1a202c; margin-top: 0; margin-bottom: 12px;">Payment Details:</h3>
                
                <ul style="padding-left: 20px; margin: 0; font-size: 13.5px; color: #2d3748; line-height: 1.9;">
                    <li style="margin-bottom: 4px;"><strong>Payor:</strong> {{ $payorName }}</li>
                    <li style="margin-bottom: 4px;"><strong>Amount:</strong> ${{ number_format($amount, 2) }}</li>
                    <li style="margin-bottom: 4px;"><strong>Check Number:</strong> {{ $checkNumber }}</li>
                    <li style="margin-bottom: 4px;"><strong>Check Memo:</strong> {{ $memo ?? '' }}</li>
                    <li style="margin-bottom: 4px;"><strong>Mailing Type:</strong> Email</li>
                    <li style="margin-bottom: 4px;"><strong>Date Processed:</strong> {{ $dateProcessed }}</li>
                    <li style="margin-bottom: 4px;"><strong>Comment:</strong> {{ $comment ?? '' }}</li>
                </ul>
            </div>

            <!-- Tracking & Info -->
            <p style="font-size: 13.5px; color: #4a5568; margin-bottom: 15px;">
                You can track the status of your check using the following link: 
                <a href="{{ $trackUrl }}" target="_blank" style="color: #1a73e8; font-weight: bold; text-decoration: underline;">Track Check Status</a>.
            </p>

            <p style="font-size: 13.5px; color: #4a5568; margin-bottom: 25px;">
                If you have any questions or need assistance, please contact us at 
                <a href="mailto:support@zilmoney.com" style="color: #1a73e8; font-weight: bold; text-decoration: underline;">support@zilmoney.com</a>.
            </p>

            <!-- Login Section -->
            <p style="font-size: 13.5px; color: #4a5568; margin-bottom: 30px;">
                Log in to your account to easily view your payment history and manage your payments:
                <a href="{{ $loginUrl }}" target="_blank" style="background-color: #1a73e8; color: #ffffff; font-size: 13px; font-weight: bold; text-decoration: none; padding: 8px 18px; border-radius: 6px; display: inline-block; margin-left: 8px; vertical-align: middle;">
                    Login Here
                </a>
            </p>

            <!-- Sign Off -->
            <div style="font-size: 13px; color: #718096; border-top: 1px solid #edf2f7; pt: 20px; padding-top: 20px;">
                Best regards,<br>
                <strong style="color: #4a5568;">The Zil Money Team</strong><br>
                (408) 775-7720
            </div>

        </div>

        <!-- Light Mint Footer Band -->
        <div style="background-color: #c6f6d5; text-align: center; padding: 16px; font-size: 13px; color: #0284c7; font-weight: bold;">
            For further queries contact: support@zilmoney.com<br>
            Or (408) 775-7720
        </div>

        <!-- Branding Logo Footer -->
        <div style="padding: 20px; text-align: center; background-color: #ffffff;">
            <div style="display: inline-flex; align-items: center; justify-content: center; gap: 20px;">
                <span style="font-size: 20px; font-weight: 900; color: #1a73e8; font-family: sans-serif;">
                    Zil Money
                </span>
                <span style="font-size: 16px; font-weight: 700; color: #2b6cb0; font-family: sans-serif; font-style: italic;">
                    CheckWRITER
                </span>
            </div>
        </div>

    </div>

</body>
</html>
