@extends('layout.email-layout')

@section('title', 'Payment Receipt Uploaded - ' . config('app.name'))

@section('content')
    <!-- Greeting -->
    <h1 class="greeting">Hello, {{ $data['adminName'] ?? 'Admin' }}!</h1>
    <p class="subtitle">A payment receipt has been uploaded for a reservation and requires your verification.</p>
    
    <!-- Payment Receipt Details Section -->
    <div class="info-box" style="border-left-color: #3b82f6;">
        <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">Booking Details:</p>
        <p class="info-text"><strong>Tour Package:</strong> {{ $data['packageTitle'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Seat Number:</strong> {{ $data['seatNumber'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Client Name:</strong> {{ $data['clientName'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Submitted By Agent:</strong> {{ $data['agentName'] ?? 'N/A' }}</p>
    </div>
    
    <!-- Action Required Section -->
    <div class="content-section">
        <p class="section-text" style="font-size: 15px; color: #666666; line-height: 1.6;">
            <strong>Action Required:</strong> Please review the uploaded payment receipt and verify the payment details. Once verified, you can confirm the booking to finalize the reservation.
        </p>
        <p class="section-text" style="font-size: 14px; color: #666666; margin-top: 15px; line-height: 1.6;">
            The payment receipt is available in the booking details section. Please verify the receipt and confirm the booking if everything is in order.
        </p>
    </div>
    
    <!-- Action Button -->
    <div class="button-container">
        <a href="{{ config('app.url') }}/admin/tours" class="button" style="background-color: #3b82f6; color: #ffffff !important;">
            Review & Confirm Booking
        </a>
    </div>
    
    <!-- Additional Info -->
    <div class="content-section">
        <p class="section-text" style="font-size: 14px; color: #666666; margin-top: 20px; line-height: 1.6;">
            The booking will remain in reserved status until you verify the payment receipt and confirm the booking.
        </p>
    </div>
@endsection

@section('footer')
    <p class="footer-text">
        This is an automated notification. Please log in to your admin dashboard to review the payment receipt and confirm the booking.
    </p>
    <p class="footer-text" style="margin-top: 15px;">
        <a href="{{ config('app.url') }}/admin/tours" class="footer-link">Go to Admin Dashboard</a>
    </p>
@endsection
