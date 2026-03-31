<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 40px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { color: #2a41e8; margin: 0; font-size: 28px; }
        .otp-box { background: #f0f4ff; border: 2px dashed #2a41e8; border-radius: 10px; padding: 25px; text-align: center; margin: 25px 0; }
        .otp-code { font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #2a41e8; margin: 0; }
        .message { color: #555; line-height: 1.6; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #999; }
        .warning { background: #fff3cd; border-radius: 6px; padding: 10px 15px; font-size: 13px; color: #856404; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>etera</h1>
        </div>

        <p class="message">Hello <strong>{{ $userName }}</strong>,</p>

        <p class="message">Thank you for registering on etera. Please use the following verification code to complete your registration:</p>

        <div class="otp-box">
            <p class="otp-code">{{ $otp }}</p>
        </div>

        <p class="message">This code is valid for <strong>10 minutes</strong>. Please do not share this code with anyone.</p>

        <div class="warning">
            ⚠️ If you did not request this verification code, please ignore this email.
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} etera. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
