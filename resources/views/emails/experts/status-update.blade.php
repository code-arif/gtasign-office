@extends('emails.layout.master_layout')

@section('header-title', 'Account Status Updated')
@section('header-subtitle', 'Expert Notification')

@section('content')
    <h2>Account status: {{ ucfirst($status) }}</h2>
    <p>
        Hello {{ $user->profile->first_name ?? 'Expert' }},
    </p>

    @if($status === 'active')
        <p>
            We are pleased to inform you that your expert account on <strong>{{ config('app.name') }}</strong> has been successfully activated. You may now access your dashboard and manage your services.
        </p>
        <div style="text-align: left; margin: 32px 0;">
            <a href="{{ config('app.frontend_url') }}/auth/login" class="btn">
                Access Dashboard
            </a>
        </div>
    @elseif($status === 'suspended')
        <p>
            We are writing to inform you that your account on <strong>{{ config('app.name') }}</strong> has been suspended.
        </p>
        @if($reason)
            <div class="feature-box">
                <p><strong>Reason for suspension:</strong></p>
                <p style="margin-top: 8px;">{{ $reason }}</p>
            </div>
        @endif
        <p>
            If you have questions regarding this decision or wish to appeal, please contact our support department.
        </p>
    @else
        <p>
            Your account status on <strong>{{ config('app.name') }}</strong> has been updated to <strong>{{ $status }}</strong>.
        </p>
        <p>
            Should you require further clarification regarding this update, please do not hesitate to contact our team.
        </p>
    @endif

    <p style="margin-top: 32px;">
        Best regards,<br>
        <strong>The {{ config('app.name') }} Team</strong>
    </p>
@endsection

@section('footer-tagline', 'Expert Relations Department')
@section('footer-address', 'Professional Services Management')
