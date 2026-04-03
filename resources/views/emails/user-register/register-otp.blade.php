@extends('emails.layout.master_layout')

@section('header')
    <a href="{{ config('app.frontend_url') }}">

        @if (file_exists(public_path('default/logo.png')))
            <img src="{{ asset('default/logo.png') }}" class="logo">
        @endif

    </a>
@endsection

@section('content')
    <p>
        Hello <strong style="color:#0f5132">{{ $user->name }}</strong>,
    </p>

    <p>
        Thank you for choosing <strong>{{ config('app.name') }}</strong>.
        Use the OTP below to complete your registration.
    </p>

    <!-- OTP BOX -->

    <table width="100%" role="presentation" style="margin:35px 0">

        <tr>
            <td align="center">

                <table role="presentation">

                    <tr>

                        <td
                            style="
background:#f0fdf4;
border:2px solid #16a34a;
border-radius:14px;
padding:26px 40px;
text-align:center;
">

                            <div
                                style="
font-size:40px;
font-weight:700;
letter-spacing:10px;
color:#0f5132;
font-family:Courier, monospace;
">

                                {{ $otp }}

                            </div>

                            <div
                                style="
margin-top:10px;
font-size:12px;
color:#64748b;
letter-spacing:2px;
text-transform:uppercase;
">

                                One Time Password

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
            <li>Never share your OTP with anyone</li>
            <li>{{ config('app.name') }} will never ask for your OTP</li>
            <li>If you did not request this, simply ignore this email</li>
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
