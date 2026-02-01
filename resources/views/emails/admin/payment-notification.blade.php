@extends('emails.layout.master_layout')

@section('header')
    <tr>
        <td class="header">
            <a href="{{ config('app.url') }}" style="display: inline-block;">
                <img src="{{ $message->embed(public_path('default/logo.png')) }}" alt="Whistle Works" class="logo">
            </a>
        </td>
    </tr>
@endsection

@section('content')
    <!-- Admin Notification -->
    <h1 style="color: #00aeef;">New Payment Received</h1>
    <p>A new payment has been received for camp registration.</p>
    <p><strong>Referee:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
    <p><strong>Camp:</strong> {{ $camp->camp_name }}</p>
    <p><strong>Amount:</strong> ${{ number_format($payment->amount, 2) }}</p>
    <p><strong>Payment ID:</strong> {{ $payment->id }}</p>
@endsection
