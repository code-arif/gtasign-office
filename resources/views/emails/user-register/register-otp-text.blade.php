Hello {{ $user->profile->first_name ?? 'User' }},

Thank you for choosing {{ config('app.name') }}. 
Use the OTP below to complete your registration.

OTP: {{ $otp }}

SECURITY REMINDER:
* Never share your OTP with anyone.
* {{ config('app.name') }} will never ask for your OTP.
* If you did not request this, simply ignore this email.

This OTP will expire in 1 hour.

Best regards,
{{ config('app.name') }} Team

© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
