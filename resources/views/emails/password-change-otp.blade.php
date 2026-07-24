<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mã xác thực đổi mật khẩu</title>
</head>

<body
    style="
        margin: 0;
        padding: 30px 15px;
        background-color: #f8fafc;
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
    "
>
    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
    >
        <tr>
            <td align="center">
                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="
                        max-width: 560px;
                        overflow: hidden;
                        border: 1px solid #fbcfe8;
                        border-radius: 18px;
                        background-color: #ffffff;
                    "
                >
                    <tr>
                        <td
                            style="
                                padding: 26px;
                                background-color: #ec4899;
                                color: #ffffff;
                                text-align: center;
                            "
                        >
                            <div
                                style="
                                    font-size: 24px;
                                    font-weight: 700;
                                "
                            >
                                Cosmetic Shop
                            </div>

                            <div
                                style="
                                    margin-top: 7px;
                                    font-size: 14px;
                                "
                            >
                                Xác thực thay đổi mật khẩu
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 32px;">
                            <p
                                style="
                                    margin: 0 0 18px;
                                    font-size: 16px;
                                    line-height: 1.6;
                                "
                            >
                                Xin chào
                                <strong>{{ $userName }}</strong>,
                            </p>

                            <p
                                style="
                                    margin: 0;
                                    color: #4b5563;
                                    line-height: 1.7;
                                "
                            >
                                Bạn vừa yêu cầu đổi mật khẩu cho
                                tài khoản Cosmetic Shop. Mã xác
                                thực của bạn là:
                            </p>

                            <div
                                style="
                                    margin: 26px 0;
                                    padding: 18px;
                                    border-radius: 14px;
                                    background-color: #fdf2f8;
                                    color: #be185d;
                                    font-size: 34px;
                                    font-weight: 800;
                                    letter-spacing: 9px;
                                    text-align: center;
                                "
                            >
                                {{ $otp }}
                            </div>

                            <p
                                style="
                                    margin: 0;
                                    color: #4b5563;
                                    line-height: 1.7;
                                "
                            >
                                Mã này có hiệu lực trong
                                <strong>10 phút</strong> và chỉ
                                được sử dụng một lần.
                            </p>

                            <p
                                style="
                                    margin: 18px 0 0;
                                    color: #9ca3af;
                                    font-size: 13px;
                                    line-height: 1.6;
                                "
                            >
                                Nếu bạn không thực hiện yêu cầu
                                này, hãy bỏ qua email và không
                                chia sẻ mã xác thực cho bất kỳ ai.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="
                                padding: 18px 25px;
                                border-top: 1px solid #f3f4f6;
                                color: #9ca3af;
                                font-size: 12px;
                                text-align: center;
                            "
                        >
                            © {{ date('Y') }} Cosmetic Shop
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
