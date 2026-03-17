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
color:#0f5132;
font-size:26px;
font-weight:700;
margin:0;
">

                    Welcome to {{ config('app.name') }}

                </h1>

            </td>
        </tr>
    </table>


    <!-- Greeting -->
    <p style="font-size:16px;color:#334155;">
        Hello <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>,
    </p>


    <!-- Message -->
    <p style="font-size:15px;color:#475569;line-height:1.6;">
        Your account has been successfully verified and is now
        <strong style="color:#0f5132;">active</strong>.
    </p>

    <p style="font-size:15px;color:#475569;line-height:1.6;">
        You can now login to your account and start using
        <strong>{{ config('app.name') }}</strong>.
    </p>


    <!-- Success Indicator -->
    <table role="presentation" width="100%" style="margin:35px 0;">
        <tr>
            <td align="center">

                <div
                    style="
background:#e8f5e9;
width:70px;
height:70px;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
margin:auto;
">

                    <span style="color:#16a34a;font-size:34px;">✓</span>

                </div>

                <p style="
margin-top:15px;
font-size:16px;
font-weight:600;
color:#16a34a;
">

                    Account Activated

                </p>

            </td>
        </tr>
    </table>


    <!-- Login Button -->
    <div style="text-align:center;margin-top:25px;">

        <a href="{{ config('app.frontend_url') }}/auth/login"
            style="
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


    <p style="margin-top:35px;font-size:15px;color:#334155;">
        Best regards,<br>
        <strong>{{ config('app.name') }} Team</strong>
    </p>
@endsection



@section('footer')
    <tr>
        <td class="footer">

            {{-- <div>
                <a href="{{ config('app.frontend_url') }}/about-us">About</a>
                &nbsp; | &nbsp;
                <a href="{{ config('app.frontend_url') }}">Home</a>
            </div> --}}

            <div style="margin-top:12px;font-size:12px;color:#94a3b8;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>

        </td>
    </tr>
@endsection
