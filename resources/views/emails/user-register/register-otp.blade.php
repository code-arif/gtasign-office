@extends('emails.layout.master_layout')

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
    <!-- Greeting -->
    <p style="margin-bottom: 20px; font-size: 16px; color: #333333;">
        Hello {{ $user->name }},
    </p>

    <!-- Message -->
    <p style="margin-bottom: 25px; font-size: 15px; color: #555555; line-height: 1.6;">
        Thank you for choosing <strong>Whistle Works</strong>. Use the following OTP to complete your sign-up procedure.
        This OTP is valid for <span style="color: #00aeef; font-weight: bold;">1 hour</span>.
    </p>

    <!-- OTP Box -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 30px 0;">
        <tr>
            <td align="center">
                <div
                    style="
                background-color: #00aeef;
                color: #ffffff;
                font-size: 32px;
                font-weight: bold;
                letter-spacing: 8px;
                padding: 20px 40px;
                border-radius: 8px;
                text-align: center;
                display: inline-block;
                font-family: monospace;
                box-shadow: 0 4px 12px rgba(0, 174, 239, 0.2);
            ">
                    {{ $otp }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Instructions -->
    <p style="margin-bottom: 15px; font-size: 14px; color: #777777;">
        <strong>Important:</strong>
    </p>
    <ul style="margin-bottom: 25px; padding-left: 20px; font-size: 14px; color: #666666; line-height: 1.6;">
        <li>Do not share this OTP with anyone</li>
        <li>Our team will never ask for your OTP</li>
        <li>If you didn't request this OTP, please ignore this email</li>
    </ul>

    <!-- Closing -->
    <p style="margin-top: 30px; font-size: 15px; color: #333333;">
        Best regards,<br>
        <strong>The Whistle Works Team</strong>
    </p>
@endsection

@section('footer')
    <tr>
        <td class="footer">
            <!-- Social Links -->
            <div class="footer-links">
                <a href="{{ config('app.frontend_url') }}/about-us"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">About Us</a>
                <a href="{{ config('app.frontend_url') }}"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">Home</a>
                <a href="{{ config('app.frontend_url') }}/all-camps"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">All Camps</a>
            </div>

            <!-- Copyright -->
            <div class="copyright">
                &copy; {{ date('Y') }} Whistle Works Inc. All rights reserved.<br>
                <small>1600 Sports Arena Parkway, California, USA</small>
            </div>
        </td>
    </tr>
@endsection
