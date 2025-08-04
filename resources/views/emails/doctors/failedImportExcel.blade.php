<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Tahoma, sans-serif; background-color: #ffffff; text-align: center; direction: ltr; color: #000000;">
<div style="background-color: #ffffff; margin: 40px auto; border-radius: 8px; width: 90%; max-width: 700px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h1 style="color: #c0392b;">⚠️ إخفاق جزئي في الاستيراد</h1>
    <p>: نأسف لإبلاغك أن عملية استيراد بيانات الدكاترة لم تتم بشكل كامل، وحدثت الأخطاء التالية</p>

    <table style="margin: 20px auto; border-collapse: collapse; width: 100%;">
        <thead>
        <tr style="background-color: #f2f2f2;">
            <th style="border: 1px solid #ddd; padding: 10px;">#</th>
            <th style="border: 1px solid #ddd; padding: 10px;">الخطأ</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($errors as $index => $error)
            <tr>
                <td style="border: 1px solid #ddd; padding: 10px;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #ddd; padding: 10px;">{{ $error }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if (!empty($successful))
        <h2 style="margin-top: 40px; color: #2c3e50;">✅ :الدكاترة الذين تم إضافتهم بنجاح</h2>
        <table style="margin: 20px auto; border-collapse: collapse; width: 100%;">
            <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #ddd; padding: 10px;">#</th>
                <th style="border: 1px solid #ddd; padding: 10px;">البريد الإلكتروني</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($successful as $index => $email)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 10px;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #ddd; padding: 10px;">{{ $email }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif


    <div style="margin-top: 40px; color: #777777; font-size: 13px;">
        مع تحيات فريق جامعتي
    </div>
</div>
</body>
</html>
