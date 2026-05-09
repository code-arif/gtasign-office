@extends('emails.layout.master_layout')

@section('header-title', 'Payment Received')
@section('header-subtitle', 'Administrative Notification')

@section('content')
    <h2>New Order Payment Confirmed</h2>
    <p>
        This is an automated notification to confirm that a payment has been successfully processed for a new order. The order is now active and the expert has been notified.
    </p>

    <table class="data-table" role="presentation">
        <thead>
            <tr>
                <th colspan="2">Order Details</th>
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
                <td class="data-label">Client</td>
                <td class="data-value">
                    {{ $order->buyer->profile->first_name ?? '' }} {{ $order->buyer->profile->last_name ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td class="data-label">Expert</td>
                <td class="data-value">
                    {{ $order->seller->profile->first_name ?? '' }} {{ $order->seller->profile->last_name ?? 'N/A' }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="data-table" role="presentation">
        <thead>
            <tr>
                <th colspan="2">Financial Summary</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="data-label">Total Amount</td>
                <td class="data-value">${{ number_format($order->price, 2) }}</td>
            </tr>
            <tr>
                <td class="data-label">Platform Fee</td>
                <td class="data-value">${{ number_format($order->platform_fee, 2) }}</td>
            </tr>
            <tr>
                <td class="data-label">Expert Earnings</td>
                <td class="data-value">${{ number_format($order->seller_earnings, 2) }}</td>
            </tr>
            <tr>
                <td class="data-label">Payment Method</td>
                <td class="data-value" style="text-transform: capitalize;">{{ $order->payment_method ?? 'Stripe' }}</td>
            </tr>
            <tr>
                <td class="data-label">Processed At</td>
                <td class="data-value">{{ $order->paid_at ? $order->paid_at->format('M d, Y H:i') : now()->format('M d, Y H:i') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="feature-box">
        <p>
            <strong>Escrow Status:</strong> Funds are currently held in escrow. Disbursement to the expert will occur upon successful completion and client approval of the order.
        </p>
    </div>
@endsection

@section('footer-tagline', 'Internal Administration')
@section('footer-address', 'Transaction Monitoring System')
