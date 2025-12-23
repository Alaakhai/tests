<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h2>رمز التحقق من الحضور</h2>

    <p>المقرر: <strong>{{ $courseName }}</strong></p>

    <p>رمز التحقق الخاص بك هو:</p>

    <h1 style="letter-spacing: 4px">{{ $otp }}</h1>

    <p>الرمز صالح لمدة 10 دقائق فقط.</p>

    <p>يرجى إعطاء هذا الرمز للمعلم لتأكيد حضورك.</p>
</body>
</html>
