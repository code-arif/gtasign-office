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
    <!-- Registration Confirmation -->
    <h1 style="color: #00aeef;">Registration Confirmed!</h1>
    <p>Hello {{ $user->first_name }},</p>
    <p>You are successfully registered for <strong>{{ $camp->camp_name }}</strong>.</p>
    <p>Registration ID: <strong>#{{ $registration->id }}</strong></p>
    <p>Registered on: {{ $registration->registered_at->format('F j, Y') }}</p>
@endsection
