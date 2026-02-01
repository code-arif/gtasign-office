@extends('emails.layout.master_layout')

@section('header')
    <tr>
        <td class="header">
            <a href="{{ config('app.url') }}" style="display: inline-block;">
                <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="Whistle Works" class="logo">
            </a>
        </td>
    </tr>
@endsection

@section('content')
    <!-- Payment Failed -->
    <h1 style="color: #f44336;">Payment Failed</h1>
    <p>Hello {{ $user->first_name }},</p>
    <p>Your payment for <strong>{{ $camp->camp_name }}</strong> has failed.</p>
    @if ($error)
        <p>Error: {{ $error }}</p>
    @endif
    <p>Please try again or contact support.</p>
@endsection
