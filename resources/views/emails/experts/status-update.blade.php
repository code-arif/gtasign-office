@extends('emails.layout.master_layout')

@section('header')
    <tr>
        <td class="header">
            <a href="{{ config('app.frontend_url') }}" style="display:inline-block;">
                @if (file_exists(public_path('default/logo.png')))
                    <img src="{{ $message->embed(public_path('default/logo.png')) }}" class="logo"
                        alt="{{ config('app.name') }}" style="max-width:180px;height:auto;">
                @else
                    <div style="color:#ffffff;font-size:24px;font-weight:700;">
                        {{ strtoupper(config('app.name')) }}
                    </div>
                @endif
            </a>
        </td>
    </tr>
@endsection

@section('content')
    <!-- Heading -->
    <table role="presentation" width="100%" style="margin-bottom:25px;">
        <tr>
            <td align="center">
                <h1 style="
                    color:{{ $status === 'active' ? '#0f5132' : ($status === 'suspended' ? '#842029' : '#084298') }};
                    font-size:26px;
                    font-weight:700;
                    margin:0;
                ">
                    Account Status: {{ ucfirst($status) }}
                </h1>
            </td>
        </tr>
    </table>

    <!-- Greeting -->
    <p style="font-size:16px;color:#334155;">
        Hello <strong>{{ $user->profile->first_name ?? 'Expert' }}</strong>,
    </p>

    <!-- Message -->
    @if($status === 'active')
        <p style="font-size:15px;color:#475569;line-height:1.6;">
            We are pleased to inform you that your account on <strong>{{ config('app.name') }}</strong> has been successfully 
            <strong style="color:#0f5132;">activated</strong>.
        </p>
        <p style="font-size:15px;color:#475569;line-height:1.6;">
            You can now login to your dashboard and continue managing your gigs and orders.
        </p>
    @elseif($status === 'suspended')
        <p style="font-size:15px;color:#475569;line-height:1.6;">
            We regret to inform you that your account on <strong>{{ config('app.name') }}</strong> has been 
            <strong style="color:#842029;">suspended</strong>.
        </p>
        @if($reason)
            <div class="feature-box" style="border-left-color:#842029; background-color:#fff5f5;">
                <p style="font-weight:600;margin-bottom:10px;color:#842029;">Reason for suspension:</p>
                <p style="color:#475569;">{{ $reason }}</p>
            </div>
        @endif
        <p style="font-size:15px;color:#475569;line-height:1.6;">
            If you believe this is a mistake, please contact our support team.
        </p>
    @else
        <p style="font-size:15px;color:#475569;line-height:1.6;">
            Your account status has been updated to 
            <strong style="color:#084298;">{{ $status }}</strong>.
        </p>
        <p style="font-size:15px;color:#475569;line-height:1.6;">
            If you have any questions regarding this change, please contact our support team.
        </p>
    @endif

    <!-- Indicator Icon -->
    <table role="presentation" width="100%" style="margin:35px 0;">
        <tr>
            <td align="center">
                <div style="
                    background:{{ $status === 'active' ? '#e8f5e9' : ($status === 'suspended' ? '#f8d7da' : '#cfe2ff') }};
                    width:70px;
                    height:70px;
                    border-radius:50%;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    margin:auto;
                ">
                    @if($status === 'active')
                        <span style="color:#16a34a;font-size:34px;">✓</span>
                    @elseif($status === 'suspended')
                        <span style="color:#b02a37;font-size:34px;">!</span>
                    @else
                        <span style="color:#084298;font-size:34px;">i</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Action Button -->
    @if($status === 'active')
        <div style="text-align:center;margin-top:25px;">
            <a href="{{ config('app.frontend_url') }}/auth/login" style="
                display:inline-block;
                background:#0f5132;
                color:#ffffff;
                padding:14px 32px;
                border-radius:6px;
                text-decoration:none;
                font-weight:600;
                font-size:15px;
            ">
                Login to Your Account
            </a>
        </div>
    @endif

    <p style="margin-top:35px;font-size:15px;color:#334155;">
        Best regards,<br>
        <strong>{{ config('app.name') }} Team</strong>
    </p>
@endsection

@section('footer')
    <tr>
        <td class="footer">
            <div style="margin-top:12px;font-size:12px;color:#94a3b8;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </td>
    </tr>
@endsection
