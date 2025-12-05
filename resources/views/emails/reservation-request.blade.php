@extends('layout.email-layout')

@section('title', 'New Reservation Request - ' . config('app.name'))

@section('content')
    <!-- Greeting -->
    <h1 class="greeting">Hello, {{ $data['adminName'] ?? 'Admin' }}!</h1>
    <p class="subtitle">A new seat reservation request has been submitted by an agent and requires your attention.</p>
    
    <!-- Reservation Details Section -->
    <div class="info-box" style="border-left-color: #ea580c;">
        <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">Reservation Request Details:</p>
        <p class="info-text"><strong>Tour Package:</strong> {{ $data['packageTitle'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Seat Number:</strong> {{ $data['seatNumber'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Client Name:</strong> {{ $data['clientName'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Requested By Agent:</strong> {{ $data['agentName'] ?? 'N/A' }}</p>
    </div>
    
    <!-- Action Button -->
    <div class="button-container">
        <a href="{{ config('app.url') }}/admin/tours" class="button" style="background-color: #ea580c; color: #ffffff !important;">
            View Tour Details
        </a>
    </div>
    
    <!-- Additional Info -->
    <div class="content-section">
        <p class="section-text" style="font-size: 14px; color: #666666; margin-top: 20px; line-height: 1.6;">
            Please review this reservation request in the admin dashboard. The seat has been temporarily reserved and will remain in this state until you confirm or cancel the booking.
        </p>
    </div>
@endsection

@section('footer')
    <p class="footer-text">
        This is an automated notification. Please log in to your admin dashboard to manage this reservation request.
    </p>
    <p class="footer-text" style="margin-top: 15px;">
        <a href="{{ config('app.url') }}/admin/tours" class="footer-link">Go to Admin Dashboard</a>
    </p>
@endsection
