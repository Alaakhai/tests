<!DOCTYPE html>
<html>
<head>
    <title>Verification Code</title>
</head>
<body>
    <p>Hello {{ $studentName }},</p>
    <p>We noticed you were marked as absent today. If this is incorrect, please use the verification code below.</p>
    <h2>Your verification code is: <strong>{{ $verificationCode }}</strong></h2>
    <p>Thank you.</p>
</body>
</html>