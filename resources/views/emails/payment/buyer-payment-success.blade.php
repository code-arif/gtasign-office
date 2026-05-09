@extends('emails.layout.master_layout')

@section('header-title', 'Payment Confirmed')
@section('header-subtitle', 'Order Activation')

@section('content')
    <h2>Thank you for your purchase</h2>
    <p>
        Hello {{ $order->buyer->profile->first_name ?? 'there' }},
    </p>
    <p>
        Your payment has been successfully processed and your order is now active. The expert has been notified and will begin working on your project shortly.
    </p>

    <table class="data-table" role="presentation">
        <thead>
            <tr>
                <th colspan="2">Order Summary</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="data-label">Order Reference</td>
                <td class="data-value">{{ $order->order_number }}</td>
            </tr>
            <tr>
                <td class="data-label">Service</td>
                <td class="data-value">{{ $order->gig->title ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="data-label">Expert</td>
                <td class="data-value">
                    {{ $order->seller->profile->first_name ?? '' }} {{ $order->seller->profile->last_name ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td class="data-label">Amount Paid</td>
                <td class="data-value" style="font-weight: 700;">
                    ${{ number_format($order->price, 2) }}
                </td>
            </tr>
            <tr>
                <td class="data-label">Expected Delivery</td>
                <td class="data-value" style="font-weight: 700; color: #f59e0b;">
                    {{ $order->expected_delivery_at ? $order->expected_delivery_at->format('M d, Y') : 'N/A' }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="feature-box">
        <p>
            <strong>Payment Security:</strong> Your funds are securely held in escrow and will only be released to the expert once you have reviewed and approved the final delivery.
        </p>
    </div>

    <div class="feature-box">
        <p style="font-weight: 600; margin-bottom: 8px;">What to expect next:</p>
        <ul class="list-positive">
            <li>The expert will initiate the workflow based on your provided requirements.</li>
            <li>You can provide additional information via the secure order chat.</li>
            <li>You will receive a notification once the delivery is ready for your review.</li>
            <li>You have the option to request revisions if the delivery does not meet your expectations.</li>
        </ul>
    </div>
@endsection

@section('footer-tagline', 'Client Services Department')
@section('footer-address', 'Secure Professional Transactions')
