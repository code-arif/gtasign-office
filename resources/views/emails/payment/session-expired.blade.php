@extends('emails.layout')

@section('header')
    <tr>
        <td class="header">
            <!-- Logo -->
            <a href="{{ config('app.url') }}" style="display: inline-block;">
                <img src="{{ $message->embed(public_path('default/logo.png')) }}" alt="Whistle Works" class="logo"
                    style="max-width: 200px; height: auto;">
                <!-- Fallback text if image doesn't load -->
                <div style="color: #ffffff; font-size: 24px; font-weight: bold; margin-top: 10px; display: none;">
                    WHISTLE WORKS
                </div>
            </a>
        </td>
    </tr>
@endsection

@section('content')
    <!-- Session Expired Icon -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 25px;">
        <tr>
            <td align="center">
                <div
                    style="
                background-color: #ff9800;
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 15px;
            ">
                    <span style="color: #ffffff; font-size: 40px; font-weight: bold;">⏰</span>
                </div>
                <h1 style="color: #333333; font-size: 28px; font-weight: bold; margin: 0;">
                    Payment Session Expired
                </h1>
                <p style="color: #666666; font-size: 16px; margin-top: 10px;">
                    Your payment window has closed
                </p>
            </td>
        </tr>
    </table>

    <!-- Greeting -->
    <p style="margin-bottom: 20px; font-size: 16px; color: #333333;">
        Hello <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>,
    </p>

    <!-- Message -->
    <p style="margin-bottom: 25px; font-size: 15px; color: #555555; line-height: 1.6;">
        Your payment session for <strong>{{ $camp->camp_name }}</strong> has expired.
        The checkout session was not completed within the allotted time.
    </p>

    <!-- Important Notice -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
        style="margin: 25px 0; background-color: #fff3e0; border-left: 4px solid #ff9800; padding: 20px; border-radius: 4px;">
        <tr>
            <td>
                <h3 style="color: #e65100; font-size: 16px; margin-bottom: 10px;">
                    ⚠️ Important Notice
                </h3>
                <p style="margin: 0; font-size: 14px; color: #e65100; line-height: 1.5;">
                    Your camp registration is <strong>not complete</strong> yet.
                    To secure your spot, you need to complete the payment process.
                </p>
            </td>
        </tr>
    </table>

    <!-- Session Details -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
        style="margin: 30px 0; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="padding: 25px;">
                <h3
                    style="color: #333333; font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid #00aeef; padding-bottom: 10px;">
                    Session Details
                </h3>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: #666666; font-size: 14px; min-width: 160px; display: inline-block;">Camp
                                Name:</span>
                            <strong style="color: #333333; font-size: 14px;">{{ $camp->camp_name }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span
                                style="color: #666666; font-size: 14px; min-width: 160px; display: inline-block;">Location:</span>
                            <strong style="color: #333333; font-size: 14px;">{{ $camp->location }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: #666666; font-size: 14px; min-width: 160px; display: inline-block;">Start
                                Date:</span>
                            <strong style="color: #333333; font-size: 14px;">
                                {{ \Carbon\Carbon::parse($camp->start_date)->format('F j, Y') }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: #666666; font-size: 14px; min-width: 160px; display: inline-block;">Camp
                                Fee:</span>
                            <strong style="color: #333333; font-size: 14px;">
                                ${{ number_format($camp->price, 2) }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: #666666; font-size: 14px; min-width: 160px; display: inline-block;">Session
                                ID:</span>
                            <strong style="color: #333333; font-size: 14px; font-family: monospace;">
                                {{ substr($sessionId, 0, 8) }}...{{ substr($sessionId, -8) }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0;">
                            <span
                                style="color: #666666; font-size: 14px; min-width: 160px; display: inline-block;">Status:</span>
                            <strong style="color: #ff9800; font-size: 14px;">Expired ⏰</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Why Sessions Expire -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
        style="margin: 25px 0; background-color: #f8f9fa; border-radius: 8px; padding: 20px;">
        <tr>
            <td>
                <h3 style="color: #333333; font-size: 16px; margin-bottom: 15px;">
                    ℹ️ Why Do Payment Sessions Expire?
                </h3>

                <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #666666; line-height: 1.6;">
                    <li style="margin-bottom: 10px;">
                        <strong>Security:</strong> Payment sessions expire to protect your financial information
                    </li>
                    <li style="margin-bottom: 10px;">
                        <strong>Time Limit:</strong> Each payment session is valid for 30-60 minutes
                    </li>
                    <li style="margin-bottom: 10px;">
                        <strong>Prevention:</strong> This prevents unauthorized transactions from incomplete sessions
                    </li>
                    <li>
                        <strong>Fresh Start:</strong> You need to start a new, secure payment session
                    </li>
                </ul>
            </td>
        </tr>
    </table>

    <!-- What to Do Now -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 30px 0;">
        <tr>
            <td>
                <h3
                    style="color: #333333; font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid #00aeef; padding-bottom: 10px;">
                    What to Do Now
                </h3>

                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <!-- Option 1 -->
                    <div style="border: 1px solid #00aeef; border-radius: 8px; padding: 20px;">
                        <h4 style="color: #00aeef; font-size: 16px; margin-bottom: 10px;">
                            1️⃣ Restart Payment
                        </h4>
                        <p style="font-size: 14px; color: #666666; line-height: 1.6; margin-bottom: 15px;">
                            Start a new payment session to complete your registration.
                        </p>
                        <a href="{{ config('app.url') }}/camps/{{ $camp->id }}/register"
                            style="background-color: #00aeef; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; font-size: 14px; display: inline-block;">
                            Restart Payment
                        </a>
                    </div>

                    <!-- Option 2 -->
                    <div style="border: 1px solid #4CAF50; border-radius: 8px; padding: 20px;">
                        <h4 style="color: #4CAF50; font-size: 16px; margin-bottom: 10px;">
                            2️⃣ View Available Camps
                        </h4>
                        <p style="font-size: 14px; color: #666666; line-height: 1.6; margin-bottom: 15px;">
                            Browse other available camps that might interest you.
                        </p>
                        <a href="{{ config('app.url') }}/camps"
                            style="background-color: #4CAF50; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; font-size: 14px; display: inline-block;">
                            Browse Camps
                        </a>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Time-Sensitive Information -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
        style="margin: 25px 0; background-color: #e3f2fd; border-radius: 8px; padding: 20px;">
        <tr>
            <td>
                <h3 style="color: #1565c0; font-size: 16px; margin-bottom: 15px;">
                    ⏳ Time-Sensitive Information
                </h3>

                <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #1565c0; line-height: 1.6;">
                    <li style="margin-bottom: 8px;">
                        <strong>Limited Spots:</strong> Camps have limited availability
                    </li>
                    <li style="margin-bottom: 8px;">
                        <strong>Early Registration:</strong> Complete payment to secure your spot
                    </li>
                    <li style="margin-bottom: 8px;">
                        <strong>Price Guarantee:</strong> Current camp fee is guaranteed until filled
                    </li>
                    <li>
                        <strong>Next Session:</strong> New payment sessions are created instantly
                    </li>
                </ul>
            </td>
        </tr>
    </table>

    <!-- Common Issues & Solutions -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 25px 0;">
        <tr>
            <td>
                <h3 style="color: #333333; font-size: 16px; margin-bottom: 15px;">
                    🔧 Having Trouble? Try These:
                </h3>

                <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 6px;">
                        <strong style="color: #333333; font-size: 14px;">Clear Browser Cache</strong>
                        <p style="font-size: 13px; color: #666666; margin: 5px 0 0;">
                            Clear your browser cache and cookies, then try again
                        </p>
                    </div>

                    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 6px;">
                        <strong style="color: #333333; font-size: 14px;">Try Different Browser</strong>
                        <p style="font-size: 13px; color: #666666; margin: 5px 0 0;">
                            Use a different web browser (Chrome, Firefox, Safari)
                        </p>
                    </div>

                    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 6px;">
                        <strong style="color: #333333; font-size: 14px;">Disable Extensions</strong>
                        <p style="font-size: 13px; color: #666666; margin: 5px 0 0;">
                            Temporarily disable ad-blockers or privacy extensions
                        </p>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Closing -->
    <p style="margin-top: 30px; font-size: 15px; color: #333333;">
        Don't miss out on this opportunity!<br>
        <strong>The Whistle Works Team</strong>
    </p>
@endsection

@section('footer')
    <tr>
        <td class="footer">
            <!-- Payment Links -->
           <div class="footer-links">
                <a href="{{ config('app.frontend_url') }}/about-us"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">About Us</a>
                <a href="{{ config('app.frontend_url') }}"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">Home</a>
                <a href="{{ config('app.frontend_url') }}/all-camps"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">All Camps</a>
            </div>

            <!-- Security Notice -->
            <div
                style="margin: 15px 0; padding: 12px; background-color: #f8f9fa; border-radius: 4px; font-size: 12px; color: #666666; text-align: center;">
                <strong>Secure Payments:</strong> All payments are processed through Stripe with 256-bit SSL encryption.
                <a href="{{ config('app.url') }}/security" style="color: #00aeef; text-decoration: none;">Learn More</a>
            </div>

            <!-- Copyright -->
            <div class="copyright">
                &copy; {{ date('Y') }} Whistle Works Inc. All rights reserved.<br>
                <small>This email was sent to {{ $user->email }} regarding an expired payment session.</small>
            </div>

            <!-- Unsubscribe -->
            <div style="margin-top: 15px; font-size: 11px; color: #999999; text-align: center;">
                This is a transactional email regarding your payment session.<br>
                <a href="{{ config('app.url') }}/preferences" style="color: #999999;">Manage Email Preferences</a>
            </div>
        </td>
    </tr>
@endsection
