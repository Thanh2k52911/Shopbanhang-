<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mã xác minh email</title>
</head>

<body
    style="
        margin: 0;
        background: #f8fafc;
        font-family: Arial, sans-serif;
        color: #111827;
    "
>
    <div
        style="
            max-width: 600px;
            margin: 0 auto;
            padding: 32px 16px;
        "
    >
        <div
            style="
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                padding: 32px;
            "
        >
            <h1
                style="
                    margin: 0;
                    text-align: center;
                    font-size: 24px;
                "
            >
                Xác minh email
            </h1>

            <p
                style="
                    margin: 24px 0 0;
                    line-height: 1.7;
                "
            >
                Xin chào
                <strong>{{ $user->name }}</strong>,
            </p>

            <p
                style="
                    margin: 12px 0 0;
                    line-height: 1.7;
                "
            >
                Mã xác minh tài khoản Cosmetic Shop của bạn là:
            </p>

            <div
                style="
                    margin: 28px 0;
                    text-align: center;
                "
            >
                <div
                    style="
                        display: inline-block;
                        padding: 16px 24px;
                        border-radius: 12px;
                        background: #fce7f3;
                        color: #be185d;
                        font-size: 34px;
                        font-weight: 700;
                        letter-spacing: 10px;
                    "
                >
                    {{ $otp }}
                </div>
            </div>

            <p
                style="
                    margin: 0;
                    line-height: 1.7;
                "
            >
                Mã có hiệu lực trong
                <strong>{{ $expiresInMinutes }} phút</strong>.

                Không chia sẻ mã này với bất kỳ ai.
            </p>

            <p
                style="
                    margin: 20px 0 0;
                    line-height: 1.7;
                    color: #6b7280;
                "
            >
                Nếu bạn không thực hiện đăng ký,
                hãy bỏ qua email này.
            </p>
        </div>

        <p
            style="
                margin: 16px 0 0;
                text-align: center;
                font-size: 12px;
                color: #9ca3af;
            "
        >
            © {{ date('Y') }} Cosmetic Shop
        </p>
    </div>
</body>
</html>
