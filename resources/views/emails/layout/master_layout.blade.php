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
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            color: #1e293b;
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
            margin-bottom: 16px;
            line-height: 1.6;
            font-size: 15px;
        }

        h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 16px;
            line-height: 1.25;
            color: #0f172a;
        }

        h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 12px;
            line-height: 1.3;
            color: #0f172a;
        }

        /* Container */
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
        }

        /* Header */
        .header {
            background-color: #ffffff;
            padding: 40px 40px 20px 40px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }

        .header-title {
            color: #0f172a;
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .header-subtitle {
            color: #64748b;
            font-size: 14px;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .logo {
            max-width: 160px;
            height: auto;
            margin-bottom: 20px;
        }

        /* Content */
        .content {
            padding: 40px;
            color: #334155;
        }

        /* Lists - Common Styles */
        .list-negative,
        .list-positive {
            margin: 20px 0;
            padding-left: 0;
            list-style-type: none;
        }

        .list-negative li,
        .list-positive li {
            margin-bottom: 10px;
            padding-left: 24px;
            position: relative;
            font-size: 14px;
            line-height: 1.5;
        }

        .list-negative li:before {
            content: "•";
            color: #ef4444;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .list-positive li:before {
            content: "•";
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
        }

        /* Button */
        .btn {
            display: inline-block;
            background-color: #0f172a;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            margin: 20px 0;
        }

        /* Feature Box */
        .feature-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 24px;
            margin: 24px 0;
            border-radius: 6px;
        }

        .feature-box p {
            margin-bottom: 0;
            color: #475569;
            font-size: 14px;
        }

        /* Footer */
        .footer {
            background-color: #ffffff;
            padding: 40px;
            text-align: left;
            font-size: 13px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }

        .footer-tagline {
            color: #64748b;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .footer-links {
            margin-top: 16px;
        }

        .footer-links a {
            color: #64748b;
            text-decoration: none;
            margin-right: 20px;
            font-size: 12px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .copyright {
            margin-top: 16px;
            color: #cbd5e1;
            font-size: 11px;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            margin: 24px 0;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .data-table td {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            color: #0f172a;
            font-size: 14px;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-label {
            color: #64748b;
            font-size: 13px;
            width: 40%;
        }

        .data-value {
            color: #0f172a;
            font-weight: 500;
            text-align: right;
        }

        /* Mobile Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                border: none !important;
            }

            .header,
            .content,
            .footer {
                padding: 30px 20px !important;
            }

            h1 {
                font-size: 22px !important;
            }

            h2 {
                font-size: 18px !important;
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

                        <!-- Header Section -->
                        <tr>
                            <td class="header">
                                @hasSection('header')
                                    @yield('header')
                                @else
                                    {{-- Optional Logo placeholder --}}
                                    {{-- <img src="{{ asset('logo.png') }}" class="logo" alt="{{ config('app.name') }}"> --}}
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

                        <!-- Footer Section -->
                        <tr>
                            <td class="footer">
                                @hasSection('footer')
                                    @yield('footer')
                                @else
                                    <div class="footer-tagline">
                                        @yield('footer-tagline', config('app.name'))
                                    </div>
                                    
                                    <div style="line-height: 1.5;">
                                        @yield('footer-address', 'Professional Services Platform')
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
