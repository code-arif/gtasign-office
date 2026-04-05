@extends('emails.layout.master_layout')

@section('header')
    <a href="{{ config('app.frontend_url') }}">

        @if (file_exists(public_path('default/logo.png')))
            <img src="{{ $message->embed(public_path('default/logo.png')) }}" class="logo" alt="{{ config('app.name') }}">
        @else
            <div style="color:#fff;font-size:24px;font-weight:700;">
                {{ strtoupper(config('app.name')) }}
            </div>
        @endif

    </a>
@endsection


@section('content')
    <p>
        Hello <strong style="color:#0f5132">{{ $user->profile->first_name ?? 'User' }}</strong>,
    </p>

    <p>
        We received a request to reset the password for your
        <strong>{{ config('app.name') }}</strong> account.
    </p>

    <p>
        Use the OTP below to continue the password reset process.
    </p>


    <!-- OTP BOX -->

    <table width="100%" role="presentation" style="margin:35px 0">
        <tr>
            <td align="center">
                <table role="presentation">
                    <tr>
                        <td
                            style="background:#f0fdf4;border:2px solid #16a34a;border-radius:14px;padding:26px 40px;text-align:center;">

                            <div
                                style="font-size:40px;font-weight:700;letter-spacing:10px;color:#0f5132;font-family:Courier, monospace;">
                                {{ $otp }}
                            </div>

                            <div
                                style="margin-top:10px;font-size:12px;color:#64748b;letter-spacing:2px;text-transform:uppercase;">
                                Password Reset OTP
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>


    <!-- Security -->

    <div class="feature-box">

        <p style="font-weight:600;margin-bottom:10px;color:#065f46">
            Security Reminder
        </p>

        <ul class="list-positive">

            <li>Never share this OTP with anyone</li>

            <li>{{ config('app.name') }} will never ask for your OTP</li>

            <li>If you did not request a password reset, please ignore this email</li>

        </ul>

    </div>


    <p>
        This OTP will expire in
        <strong style="color:#0f5132">1 hour</strong>.
    </p>


    <p style="margin-top:30px">
        Best regards,<br>
        <strong>{{ config('app.name') }} Team</strong>
    </p>
@endsection


@section('footer')
    <div style="margin-top:14px;font-size:12px;color:#94a3b8">
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
@endsection
