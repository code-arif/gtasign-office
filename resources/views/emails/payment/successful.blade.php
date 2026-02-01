@extends('emails.layout.master_layout')

@section('header')
    <tr>
        <td class="header">
            <!-- Logo -->
            <a href="{{ config('app.url') }}" style="display: inline-block;">
                <img src="{{ $message->embed(public_path('default/logo.png')) }}" alt="Whistle Works" class="logo"
                    style="max-width: 200px; height: auto;">
            </a>
        </td>
    </tr>
@endsection

@section('content')
    <!-- Success Icon -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 25px;">
        <tr>
            <td align="center">
                <div
                    style="
                background-color: #4CAF50;
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 15px;
            ">
                    <span style="color: #ffffff; font-size: 40px;">✓</span>
                </div>
                <h1 style="color: #333333; font-size: 28px; font-weight: bold; margin: 0;">
                    Payment Successful!
                </h1>
                <p style="color: #666666; font-size: 16px; margin-top: 10px;">
                    Thank you for your payment
                </p>
            </td>
        </tr>
    </table>

    <!-- Greeting -->
    <p style="margin-bottom: 20px; font-size: 16px; color: #333333;">
        Hello <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>,
    </p>

    <!-- Message -->
    <p style="margin-bottom: 25px; font-size: 15px; color: #555555; line-height: 1.6;">
        Your payment for <strong>{{ $camp->camp_name }}</strong> has been successfully processed.
        You are now registered for the camp.
    </p>

    <!-- Payment Details -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
        style="margin: 30px 0; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="padding: 25px;">
                <h3
                    style="color: #333333; font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid #00aeef; padding-bottom: 10px;">
                    Payment Details
                </h3>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: #666666; font-size: 14px; min-width: 140px; display: inline-block;">Payment
                                ID:</span>
                            <strong style="color: #333333; font-size: 14px;">{{ $payment->id }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: #666666; font-size: 14px; min-width: 140px; display: inline-block;">Amount
                                Paid:</span>
                            <strong style="color: #333333; font-size: 14px;">
                                ${{ number_format($payment->amount, 2) }} {{ strtoupper($payment->currency) }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: #666666; font-size: 14px; min-width: 140px; display: inline-block;">Payment
                                Date:</span>
                            <strong style="color: #333333; font-size: 14px;">
                                {{ $payment->paid_at->format('F j, Y \a\t h:i A') }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: #666666; font-size: 14px; min-width: 140px; display: inline-block;">Payment
                                Method:</span>
                            <strong style="color: #333333; font-size: 14px;">
                                {{ ucfirst($payment->metadata['payment_method'] ?? 'Stripe') }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0;">
                            <span
                                style="color: #666666; font-size: 14px; min-width: 140px; display: inline-block;">Status:</span>
                            <strong style="color: #4CAF50; font-size: 14px;">✓ Successful</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Camp Details -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
        style="margin: 30px 0; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="padding: 25px;">
                <h3
                    style="color: #333333; font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid #00aeef; padding-bottom: 10px;">
                    Camp Details
                </h3>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: #666666; font-size: 14px; min-width: 140px; display: inline-block;">Camp
                                Name:</span>
                            <strong style="color: #333333; font-size: 14px;">{{ $camp->camp_name }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span
                                style="color: #666666; font-size: 14px; min-width: 140px; display: inline-block;">Location:</span>
                            <strong style="color: #333333; font-size: 14px;">{{ $camp->location }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: #666666; font-size: 14px; min-width: 140px; display: inline-block;">Start
                                Date:</span>
                            <strong style="color: #333333; font-size: 14px;">
                                {{ \Carbon\Carbon::parse($camp->start_date)->format('F j, Y') }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0;">
                            <span style="color: #666666; font-size: 14px; min-width: 140px; display: inline-block;">End
                                Date:</span>
                            <strong style="color: #333333; font-size: 14px;">
                                {{ \Carbon\Carbon::parse($camp->end_date)->format('F j, Y') }}
                            </strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Registration Confirmation -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
        style="margin: 30px 0; background-color: #e8f5e9; border-radius: 8px; padding: 20px;">
        <tr>
            <td align="center">
                <h3 style="color: #2e7d32; font-size: 18px; margin-bottom: 15px;">
                    Registration Confirmed! 🎉
                </h3>
                <p style="font-size: 15px; color: #388e3c; line-height: 1.6; margin-bottom: 20px;">
                    Your registration for <strong>{{ $camp->camp_name }}</strong> is now complete.
                    You'll receive further instructions about the camp schedule and check-in process
                    closer to the event date.
                </p>

                <!-- Dashboard Button -->
                {{-- <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 15px;">
                    <tr>
                        <td align="center">
                            <a href="{{ config('app.frontend_url') }}"
                                style="background-color: #00aeef; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 4px; font-weight: bold; font-size: 16px; display: inline-block;">
                                View My Camps
                            </a>
                        </td>
                    </tr>
                </table> --}}
            </td>
        </tr>
    </table>

    <!-- Receipt -->
    {{-- <p style="font-size: 14px; color: #666666; line-height: 1.6;">
        A receipt has been generated for this payment. You can download it from your
        <a href="{{ config('app.url') }}/dashboard/payments/{{ $payment->id }}"
            style="color: #00aeef; text-decoration: none;">
            payment history
        </a>.
    </p> --}}

    <!-- Support -->
    {{-- <p style="margin-top: 25px; font-size: 14px; color: #666666; line-height: 1.6;">
        If you have any questions about your registration or the camp, please contact:
        <br>
        <a href="mailto:camps@whistleworks.com" style="color: #00aeef; text-decoration: none;">camps@whistleworks.com</a>
        or call
        <a href="tel:+18009447883" style="color: #00aeef; text-decoration: none;">+1-800-944-7883</a>
    </p> --}}

    <!-- Closing -->
    <p style="margin-top: 30px; font-size: 15px; color: #333333;">
        We look forward to seeing you at the camp!<br>
        <strong>The Whistle Works Team</strong>
    </p>
@endsection

@section('footer')
    <tr>
        <td class="footer">
            <!-- Links -->
            <div class="footer-links">
                <a href="{{ config('app.frontend_url') }}/about-us"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">About Us</a>
                <a href="{{ config('app.frontend_url') }}"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">Home</a>
                <a href="{{ config('app.frontend_url') }}/all-camps"
                    style="color: #00aeef; text-decoration: none; margin: 0 10px;">All Camps</a>
            </div>

            <!-- Receipt Info -->
            <div style="margin: 15px 0; font-size: 12px; color: #666666; text-align: center;">
                This email serves as confirmation of your payment and registration.<br>
                Please keep it for your records.
            </div>

            <!-- Copyright -->
            <div class="copyright">
                &copy; {{ date('Y') }} Whistle Works Inc. All rights reserved.<br>
                <small>Official camp registration confirmation for {{ $user->email }}</small>
            </div>
        </td>
    </tr>
@endsection
