@extends('emails.layout.master_layout')

@section('header')
    <tr>
        <td class="header">
            <!-- Logo -->
            <a href="{{ config('app.frontend_url') }}" style="display: inline-block;">
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
    <!-- Security Alert Icon -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 25px;">
        <tr>
            <td align="center">
                <div
                    style="
                background-color: #ff9800;
                width: 70px;
                height: 70px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 15px;
            ">
                    <span style="color: #ffffff; font-size: 32px;">!</span>
                </div>
                <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin: 0;">
                    Password Reset Request
                </h1>
            </td>
        </tr>
    </table>

    <!-- Greeting -->
    <p style="margin-bottom: 20px; font-size: 16px; color: #333333;">
        Hello <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>,
    </p>

    <!-- Message -->
    <p style="margin-bottom: 25px; font-size: 15px; color: #555555; line-height: 1.6;">
        We received a request to reset your password for your Whistle Works account.
        Use the One-Time Password (OTP) below to verify your identity and create a new password.
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
                margin-bottom: 15px;
            ">
                    {{ $otp }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Closing -->
    <p style="margin-top: 30px; font-size: 15px; color: #333333;">
        Stay secure,<br>
        <strong>The Whistle Works Security Team</strong>
    </p>
@endsection

@section('footer')
    <tr>
        <td class="footer">
            <!-- Security Links -->
            <div class="footer-links">
                <a href="{{ config('app.frontend_url') }}/about-us"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">About Us</a>
                <a href="{{ config('app.frontend_url') }}"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">Home</a>
                <a href="{{ config('app.frontend_url') }}/all-camps"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">All Camps</a>
            </div>

            <!-- Security Warning -->
            <div
                style="margin: 15px 0; padding: 10px; background-color: #f8f9fa; border-radius: 4px; font-size: 12px; color: #666666;">
                <strong>Security Notice:</strong> This email contains sensitive information.
                Please do not forward it to anyone and delete it after resetting your password.
            </div>

            <!-- Copyright -->
            <div class="copyright">
                &copy; {{ date('Y') }} Whistle Works Inc. All rights reserved.<br>
                <small>Your ultimate sports management platform</small>
            </div>
        </td>
    </tr>
@endsection
