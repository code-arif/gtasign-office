@extends('emails.layout.master_layout')

@section('header-title', 'New Order Received')
@section('header-subtitle', 'Order Activation')

@section('content')
    <h2>Order Confirmation</h2>
    <p>
        Hello {{ $order->seller->profile->first_name ?? 'Expert' }},
    </p>
    <p>
        A new order has been placed and successfully paid for. The funds are currently held in escrow and the order is now active. You may begin working on the requirements immediately.
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
                <td class="data-label">Your Earnings</td>
                <td class="data-value" style="font-weight: 700; color: #10b981;">
                    ${{ number_format($order->seller_earnings, 2) }}
                </td>
            </tr>
            <tr>
                <td class="data-label">Delivery Deadline</td>
                <td class="data-value" style="font-weight: 700; color: #f59e0b;">
                    {{ $order->expected_delivery_at ? $order->expected_delivery_at->format('M d, Y') : 'N/A' }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="feature-box">
        <p style="font-weight: 600; margin-bottom: 8px;">Recommended Next Steps:</p>
        <ul class="list-positive">
            <li>Review the order requirements in your dashboard.</li>
            <li>Initiate communication with the client via the order workspace.</li>
            <li>Submit your delivery before the specified deadline.</li>
            <li>Funds will be released to your balance after client approval.</li>
        </ul>
    </div>
@endsection

@section('footer-tagline', 'Expert Services Division')
@section('footer-address', 'Professional Collaboration Workspace')
