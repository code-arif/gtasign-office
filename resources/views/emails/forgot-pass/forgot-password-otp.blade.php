@extends('emails.layout.master_layout')

@section('header-title', 'Password Reset Request')
@section('header-subtitle', 'Account Security')

@section('content')
    <h2>Reset Verification Code</h2>
    <p>
        Hello {{ $user->profile->first_name ?? 'User' }},
    </p>
    <p>
        We received a request to reset the password for your <strong>{{ config('app.name') }}</strong> account. To proceed with the password reset, please use the following one-time password (OTP):
    </p>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 32px; border-radius: 6px; text-align: center; margin: 32px 0;">
        <div style="font-size: 36px; font-weight: 700; letter-spacing: 8px; color: #0f172a; font-family: 'Courier New', Courier, monospace;">
            {{ $otp }}
        </div>
        <div style="margin-top: 12px; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 2px;">
            Password Reset Code
        </div>
    </div>

    <div class="feature-box">
        <p style="font-weight: 600; margin-bottom: 8px;">Security Information:</p>
        <ul class="list-positive">
            <li>This verification code is valid for 60 minutes.</li>
            <li>If you did not request this reset, please ignore this email; no further action is required.</li>
            <li>For security reasons, never disclose this code to anyone.</li>
        </ul>
    </div>

    <p style="margin-top: 32px;">
        Best regards,<br>
        <strong>The {{ config('app.name') }} Team</strong>
    </p>
@endsection

@section('footer-tagline', 'Security Operations Center')
@section('footer-address', 'Account Protection Services')
