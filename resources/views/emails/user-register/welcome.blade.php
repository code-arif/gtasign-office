@extends('emails.layout.master_layout')

@section('header')
<tr>
    <td class="header">
        <!-- Logo -->
        <a href="{{ config('app.url') }}" style="display: inline-block;">
            <img src="{{ $message->embed(public_path('default/logo.png')) }}"
                 alt="Whistle Works"
                 class="logo"
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
<!-- Welcome Heading -->
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 25px;">
    <tr>
        <td align="center">
            <h1 style="color: #00aeef; font-size: 28px; font-weight: bold; margin: 0;">
                Welcome to Whistle Works!
            </h1>
        </td>
    </tr>
</table>

<!-- Greeting -->
<p style="margin-bottom: 20px; font-size: 16px; color: #333333;">
    Hello <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>,
</p>

<!-- Welcome Message -->
<p style="margin-bottom: 25px; font-size: 15px; color: #555555; line-height: 1.6;">
    Congratulations! Your email has been successfully verified. We're excited to have you as part of the
    <strong>Whistle Works</strong> community - your ultimate sports management platform.
</p>

<!-- Success Icon -->
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <div style="
                background-color: #00aeef;
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
            ">
                <span style="color: #ffffff; font-size: 40px;">✓</span>
            </div>
            <p style="font-size: 18px; color: #00aeef; font-weight: bold; margin: 0;">
                Email Verified Successfully!
            </p>
        </td>
    </tr>
</table>

<!-- Account Details -->
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 25px 0; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
    <tr>
        <td style="padding: 20px;">
            <h3 style="color: #333333; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #00aeef; padding-bottom: 8px;">
                Your Account Details
            </h3>

            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                        <span style="color: #666666; font-size: 14px; min-width: 120px; display: inline-block;">Username:</span>
                        <strong style="color: #333333; font-size: 14px;">{{ $user->username }}</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                        <span style="color: #666666; font-size: 14px; min-width: 120px; display: inline-block;">Email:</span>
                        <strong style="color: #333333; font-size: 14px;">{{ $user->email }}</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                        <span style="color: #666666; font-size: 14px; min-width: 120px; display: inline-block;">Name:</span>
                        <strong style="color: #333333; font-size: 14px;">{{ $user->first_name }} {{ $user->last_name }}</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">
                        <span style="color: #666666; font-size: 14px; min-width: 120px; display: inline-block;">Account Status:</span>
                        <strong style="color: #4CAF50; font-size: 14px;">Active ✓</strong>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Support Section -->
{{-- <p style="margin-top: 25px; font-size: 14px; color: #666666; line-height: 1.6;">
    Need help getting started? Check out our
    <a href="{{ config('app.url') }}/help-center" style="color: #00aeef; text-decoration: none;">Help Center</a>
    or
    <a href="{{ config('app.url') }}/contact" style="color: #00aeef; text-decoration: none;">contact our support team</a>.
</p> --}}

<!-- Closing -->
<p style="margin-top: 30px; font-size: 15px; color: #333333;">
    Welcome aboard,<br>
    <strong>The Whistle Works Team</strong>
</p>
@endsection

@section('footer')
<tr>
    <td class="footer">
        <!-- Social Links -->
        <div class="footer-links">
            <a href="{{ config('app.frontend_url') }}/about-us" style="color: #00aeef; text-decoration: none; margin: 0 10px;">About Us</a>
            <a href="{{ config('app.frontend_url') }}" style="color: #00aeef; text-decoration: none; margin: 0 10px;">Home</a>
            <a href="{{ config('app.frontend_url') }}/all-camps" style="color: #00aeef; text-decoration: none; margin: 0 10px;">All Camps</a>
        </div>


        <!-- Copyright -->
        <div class="copyright">
            &copy; {{ date('Y') }} Whistle Works Inc. All rights reserved.<br>
            <small>Your ultimate sports management platform</small>
        </div>
    </td>
</tr>
@endsection
