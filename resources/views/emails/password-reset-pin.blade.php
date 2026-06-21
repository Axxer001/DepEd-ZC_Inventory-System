<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 40px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h1 { color: #0f172a; font-size: 24px; margin-top: 0; }
        p { color: #475569; line-height: 1.6; font-size: 16px; }
        .pin-box { background: #f1f5f9; border: 2px dashed #cbd5e1; border-radius: 8px; text-align: center; padding: 20px; margin: 30px 0; }
        .pin { font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #c00000; margin: 0; }
        .footer { margin-top: 40px; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Password Reset Request</h1>
        <p>Hello,</p>
        <p>We received a request to reset the password for your DepEd ZC Inventory System account. Use the PIN below to proceed with the password reset process.</p>
        
        <div class="pin-box">
            <p class="pin">{{ $pin }}</p>
        </div>
        
        <p>If you did not request a password reset, you can safely ignore this email. This PIN will expire shortly.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} DepEd Zamboanga City Inventory System. All rights reserved.
        </div>
    </div>
</body>
</html>
