<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رسالة تحقق</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgb(149 157 165 / 0.2);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 120px;
            height: auto;
        }
        h1 {
            color: #0d6efd;
            font-weight: bold;
        }
        .greeting {
            margin-bottom: 20px;
            font-size: 18px;
        }
        .otp-box {
            background-color: #e7f3ff;
            border: 2px solid #0d6efd;
            padding: 15px 20px;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 8px;
            text-align: center;
            border-radius: 6px;
            color: #0d6efd;
            width: fit-content;
            margin: 0 auto 30px auto;
            user-select: all;
        }
        .footer {
            font-size: 14px;
            color: #888888;
            text-align: center;
            border-top: 1px solid #dddddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <img src="{{ asset('logo/logo.png') }}" alt="{{ config('app.name') }}">
    </div>
    <h1>مرحبًا {{ $name }}!</h1>
    <p class="greeting">رمز التحقق الخاص بك هو:</p>
    <div class="otp-box">{{ $otp }}</div>
    <p> يرجى استخدام هذا الرمز خلال 5 دقائق لتأكيد البريد الإلكتروني</p>
    <p>شكراً لاستخدامك تطبيق {{ config('app.name') }}.</p>

    <div class="footer">
        هذا البريد الإلكتروني مُرسل تلقائيًا، الرجاء عدم الرد عليه.
    </div>
</div>
</body>
</html>
