<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ config('app.name') }} - {{ $subject ?? 'Notification' }}</title>
    <style type="text/css">
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table {
            border-spacing: 0;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        p {
            margin-bottom: 15px;
            line-height: 1.5;
        }

        /* Container */
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        /* Header */
        .header {
            background-color: #00aeef;
            padding: 25px 30px;
            text-align: center;
        }

        .logo {
            max-width: 200px;
            height: auto;
        }

        /* Content */
        .content {
            padding: 40px 30px;
            color: #333333;
        }

        /* Footer */
        .footer {
            background-color: #f8f9fa;
            padding: 15px 30px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }

        .footer-links {
            margin-top: 15px;
        }

        .footer-links a {
            color: #00aeef;
            text-decoration: none;
            margin: 0 10px;
        }

        .copyright {
            margin-top: 10px;
            color: #999999;
            font-size: 11px;
        }

        /* Mobile Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }

            .header,
            .content,
            .footer {
                padding: 20px 15px !important;
            }

            .content {
                padding: 30px 15px !important;
            }

            .logo {
                max-width: 150px !important;
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #121212 !important;
            }

            .email-container {
                background-color: #1e1e1e !important;
            }

            .content {
                color: #e0e0e0 !important;
            }

            .footer {
                background-color: #2d2d2d !important;
                color: #cccccc !important;
            }
        }
    </style>
</head>

<body>
    <center>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td align="center" style="padding: 20px 0;">
                    <!--[if mso]>
                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="600">
                    <tr>
                    <td width="600">
                    <![endif]-->

                    <table class="email-container" role="presentation" cellspacing="0" cellpadding="0" border="0"
                        width="100%">
                        <!-- Header -->
                        @yield('header')

                        <!-- Content -->
                        <tr>
                            <td class="content">
                                @yield('content')
                            </td>
                        </tr>

                        <!-- Footer -->
                        @yield('footer')
                    </table>

                    <!--[if mso]>
                    </td>
                    </tr>
                    </table>
                    <![endif]-->
                </td>
            </tr>
        </table>
    </center>
</body>

</html>
