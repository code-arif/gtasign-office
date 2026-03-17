<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ config('app.name') }} - @yield('title', $subject ?? 'Notification')</title>
    <style type="text/css">
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            background-color: #f4f6f9;
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

        h1 {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            line-height: 1.3;
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
            background-color: #0f172a;
            padding: 30px 30px;
            text-align: center;
        }

        .header-title {
            color: #ffffff;
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }

        .header-subtitle {
            color: #38bdf8;
            font-size: 16px;
            margin-top: 5px;
        }

        .logo {
            max-width: 200px;
            height: auto;
        }

        /* Content */
        .content {
            padding: 40px 30px;
            color: #334155;
        }

        /* Lists - Common Styles */
        .list-negative,
        .list-positive {
            margin: 25px 0;
            padding-left: 20px;
            list-style-type: none;
        }

        .list-negative li,
        .list-positive li {
            margin-bottom: 12px;
            padding-left: 28px;
            position: relative;
        }

        .list-negative li:before {
            content: "✕";
            color: #ef4444;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .list-positive li:before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .list-negative li {
            color: #64748b;
        }

        .list-positive li {
            color: #334155;
            font-weight: 500;
        }

        /* Button */
        .btn {
            display: inline-block;
            background-color: #05402e;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
        }

        .btn:hover {
            background-color: #05402e;
        }

        /* Feature Box */
        .feature-box {
            background-color: #f8fafc;
            border-left: 4px solid #05402e;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }

        .feature-box p {
            margin-bottom: 0;
            color: #475569;
        }

        /* Footer */
        .footer {
            background-color: #f8fafc;
            padding: 30px 30px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }

        .footer-links {
            margin-top: 20px;
        }

        .footer-links a {
            color: #05402e;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .copyright {
            margin-top: 20px;
            color: #94a3b8;
            font-size: 12px;
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

            h1 {
                font-size: 28px !important;
            }

            h2 {
                font-size: 22px !important;
            }

            .btn {
                padding: 12px 24px !important;
                font-size: 15px !important;
            }

            .logo {
                max-width: 150px !important;
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #0f172a !important;
            }

            .email-container {
                background-color: #1e293b !important;
            }

            .content {
                color: #e2e8f0 !important;
            }

            .feature-box {
                background-color: #334155 !important;
            }

            .feature-box p {
                color: #cbd5e1 !important;
            }

            .list-positive li {
                color: #e2e8f0 !important;
            }

            .list-negative li {
                color: #94a3b8 !important;
            }

            .footer {
                background-color: #0f172a !important;
                border-top-color: #334155 !important;
                color: #94a3b8 !important;
            }
        }
    </style>

    <!-- Additional Styles -->
    @yield('styles')
</head>

<body>
    <center>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td align="center" style="padding: 20px 0;">
                    <table class="email-container" role="presentation" cellspacing="0" cellpadding="0" border="0"
                        width="100%">

                        <!-- Header Section - Can be overridden -->
                        <tr>
                            <td class="header">
                                @hasSection('header')
                                    @yield('header')
                                @else
                                    <h1 class="header-title">@yield('header-title', config('app.name'))</h1>
                                    @hasSection('header-subtitle')
                                        <div class="header-subtitle">@yield('header-subtitle')</div>
                                    @endif
                                @endif
                            </td>
                        </tr>

                        <!-- Main Content Area -->
                        <tr>
                            <td class="content">
                                @yield('content')
                            </td>
                        </tr>

                        <!-- Footer Section - Can be overridden -->
                        <tr>
                            <td class="footer">
                                @hasSection('footer')
                                    @yield('footer')
                                @else
                                    <div style="margin-bottom: 15px;">
                                        <strong>{{ config('app.name') }}</strong><br>
                                        @yield('footer-tagline', 'Enterprise-Grade AI Security')
                                    </div>

                                    <div class="footer-links">
                                        @yield('footer-links')
                                    </div>

                                    <div class="copyright">
                                        @yield('copyright', '&copy; ' . date('Y') . ' ' . config('app.name') . '. All rights reserved.')
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>

    <!-- Additional Scripts -->
    @yield('scripts')
</body>

</html>
