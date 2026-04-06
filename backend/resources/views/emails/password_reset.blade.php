<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 40px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { color: #2a41e8; margin: 0; font-size: 28px; }
        .btn-reset { display: inline-block; background: #2a41e8; color: #fff !important; padding: 14px 40px; border-radius: 8px; text-decoration: none; font-size: 16px; font-weight: bold; margin: 20px 0; }
        .btn-container { text-align: center; margin: 25px 0; }
        .message { color: #555; line-height: 1.6; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #999; }
        .link-fallback { font-size: 12px; color: #999; word-break: break-all; margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 6px; }
        .warning { background: #fff3cd; border-radius: 6px; padding: 10px 15px; font-size: 13px; color: #856404; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>etera</h1>
        </div>

        <p class="message">Hello <strong>{{ $userName }}</strong>,</p>

        <p class="message">We received a request to reset your etera password. Click the button below to create a new password:</p>

        <div class="btn-container">
            <a href="{{ $resetUrl }}" class="btn-reset">Reset My Password</a>
        </div>

        <p class="message">This link is valid for <strong>60 minutes</strong>. After that, you'll need to request a new link.</p>

        <div class="link-fallback">
            <strong>Can't click the button?</strong> Copy and paste this link into your browser:<br>
            {{ $resetUrl }}
        </div>

        <div class="warning">
            ⚠️ If you did not request a password reset, please ignore this email. Your password will remain unchanged.
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} etera. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
