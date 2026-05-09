@extends('emails.layout.master_layout')

@section('header-title', 'Email Verification')
@section('header-subtitle', 'Account Registration')

@section('content')
    <h2>Verification Code</h2>
    <p>
        Hello {{ $user->profile->first_name ?? 'User' }},
    </p>
    <p>
        Thank you for initiating your registration with <strong>{{ config('app.name') }}</strong>. To complete the verification process, please use the following one-time password (OTP):
    </p>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 32px; border-radius: 6px; text-align: center; margin: 32px 0;">
        <div style="font-size: 36px; font-weight: 700; letter-spacing: 8px; color: #0f172a; font-family: 'Courier New', Courier, monospace;">
            {{ $otp }}
        </div>
        <div style="margin-top: 12px; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 2px;">
            One Time Password
        </div>
    </div>

    <div class="feature-box">
        <p style="font-weight: 600; margin-bottom: 8px;">Security Information:</p>
        <ul class="list-positive">
            <li>This code is valid for 60 minutes from the time of request.</li>
            <li>Do not share this verification code with any third party.</li>
            <li>If you did not initiate this request, please disregard this notification.</li>
        </ul>
    </div>

    <p style="margin-top: 32px;">
        Best regards,<br>
        <strong>The {{ config('app.name') }} Team</strong>
    </p>
@endsection

@section('footer-tagline', 'Verification Services')
@section('footer-address', 'Security & Compliance Department')
