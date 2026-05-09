@extends('emails.layout.master_layout')

@section('header-title', 'Contact Form Submission')
@section('header-subtitle', 'Administrative Notification')

@section('content')
    <h2>New Inquiry Received</h2>
    <p>
        An inquiry has been submitted via the contact form on the platform. Please find the details of the submission below.
    </p>

    <table class="data-table" role="presentation">
        <thead>
            <tr>
                <th colspan="2">Submission Details</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="data-label">Name</td>
                <td class="data-value">{{ $contact->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="data-label">Email</td>
                <td class="data-value">{{ $contact->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="data-label">Subject</td>
                <td class="data-value">{{ $contact->subject ?? 'General Inquiry' }}</td>
            </tr>
            <tr>
                <td class="data-label">Submitted At</td>
                <td class="data-value">{{ $contact->created_at ? $contact->created_at->format('M d, Y H:i') : now()->format('M d, Y H:i') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="feature-box">
        <p><strong>Message:</strong></p>
        <p style="margin-top: 12px; white-space: pre-line;">{{ $contact->message ?? 'No message provided.' }}</p>
    </div>
@endsection

@section('footer-tagline', 'Inquiry Management System')
@section('footer-address', 'Client Relations Support')
