@extends('emails.layout.master_layout')

@section('header-title', 'Welcome to ' . config('app.name'))
@section('header-subtitle', 'Account Activation')

@section('content')
    <h2>Account Verified Successfully</h2>
    <p>
        Hello {{ $user->first_name }} {{ $user->last_name }},
    </p>
    <p>
        Thank you for joining <strong>{{ config('app.name') }}</strong>. We are pleased to confirm that your account has been successfully verified and is now fully active.
    </p>
    <p>
        You may now access our professional services platform and begin utilizing all the features available to you.
    </p>

    <div style="text-align: left; margin: 32px 0;">
        <a href="{{ config('app.frontend_url') }}/auth/login" class="btn">
            Login to Your Account
        </a>
    </div>

    <p style="margin-top: 32px;">
        Best regards,<br>
        <strong>The {{ config('app.name') }} Team</strong>
    </p>
@endsection

@section('footer-tagline', 'User Onboarding Department')
@section('footer-address', 'Professional Services Network')
