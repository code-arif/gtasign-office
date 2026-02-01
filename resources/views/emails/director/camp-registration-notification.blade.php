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
    <h1 style="color: #00aeef;">New Referee Registration</h1>

    <p>
        A referee has successfully registered and completed payment for your camp.
    </p>

    <hr>

    <p><strong>Referee Name:</strong> {{ $referee->first_name }} {{ $referee->last_name }}</p>
    <p><strong>Email:</strong> {{ $referee->email }}</p>

    <p><strong>Camp Name:</strong> {{ $camp->camp_name }}</p>
    <p><strong>Camp Date:</strong> {{ optional($camp->start_date)->format('M d, Y') }}</p>

    <p><strong>Payment Amount:</strong> ${{ number_format($payment->amount, 2) }}</p>
    <p><strong>Payment ID:</strong> {{ $payment->id }}</p>
    <p><strong>Payment Status:</strong> {{ ucfirst($payment->status) }}</p>

    <hr>

    <p>
        You can manage referee assignments and camp details from your director dashboard.
    </p>

    <p>
        Regards,<br>
        <strong>Whistle Works Team</strong>
    </p>
@endsection
