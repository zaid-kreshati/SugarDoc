<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification - Diabetes Care System</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            margin: 40px auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            color: #2c3e50;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            margin-top: 20px;
            font-size: 16px;
            color: #555555;
            line-height: 1.7;
        }
        .code-box {
            background-color: #eef4ff;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #1d6fdc;
            margin: 25px 0;
            letter-spacing: 3px;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #888888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Welcome to Diabetes Care System</div>

        <div class="content">
            <p>Hello,</p>

            <p>
                Thank you for registering in <strong>Diabetes Care System</strong>.
                To protect your medical information and complete your registration,
                please verify your email address using the code below:
            </p>

            <div class="code-box">{{ $verification_code }}</div>

            <p>
                Enter this verification code in the application to activate your account.
            </p>

            <p>
                ⏱ This code will expire at:
                <strong>{{ $verification_expires_at }}</strong>
            </p>

            <p>
                If you did not request this registration, please ignore this email.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Diabetes Care System  
            <br>
            Your health data is handled securely and confidentially.
        </div>
    </div>
</body>
</html>
