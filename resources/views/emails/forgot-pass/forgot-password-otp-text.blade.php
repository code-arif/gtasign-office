Hello {{ $user->profile->first_name ?? 'User' }},

We received a request to reset the password for your {{ config('app.name') }} account.
Use the OTP below to continue the password reset process.

OTP: {{ $otp }}

SECURITY REMINDER:
* Never share this OTP with anyone.
* {{ config('app.name') }} will never ask for your OTP.
* If you did not request a password reset, please ignore this email.

This OTP will expire in 1 hour.

Best regards,
{{ config('app.name') }} Team

© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
