@extends('layout.email-layout')

@section('title', 'Booking Confirmed - ' . config('app.name'))

@section('content')
    <!-- Greeting -->
    <h1 class="greeting">Hello, {{ $data['adminName'] ?? 'Admin' }}!</h1>
    <p class="subtitle">A booking has been successfully confirmed after payment verification. A client is now in place for the upcoming travel.</p>
    
    <!-- Success Confirmation Box -->
    <div class="info-box" style="background-color: #f9fafb; border-left-color: #1e3a0f; border-left-width: 4px; padding: 20px; margin: 20px 0; border-radius: 4px;">
        <p class="info-text" style="font-weight: 600; color: #1e3a0f; margin-bottom: 15px; font-size: 18px;">✓ Payment Verified & Booking Confirmed</p>
        <p class="info-text" style="color: #333333; line-height: 1.6;">
            The payment receipt has been reviewed and verified. The booking has been officially confirmed, and the seat is now booked with a client ready for travel.
        </p>
    </div>
    
    <!-- Confirmed Booking Details Section -->
    <div class="info-box">
        <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">Confirmed Booking Details:</p>
        <p class="info-text"><strong>Tour Package:</strong> {{ $data['packageTitle'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Seat Number:</strong> {{ $data['seatNumber'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Client Name:</strong> {{ $data['clientName'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Booked By Agent:</strong> {{ $data['agentName'] ?? 'N/A' }}</p>
    </div>
    
    <!-- Status Information -->
    <div class="content-section">
        <p class="section-text" style="font-size: 15px; color: #666666; line-height: 1.6;">
            <strong>Booking Status:</strong> The booking has been confirmed and the seat status has been updated to "booked". The client is confirmed and ready for travel. All payment verification has been completed successfully.
        </p>
    </div>
    
    <!-- Summary Box -->
    <div class="info-box" style="background-color: #f9fafb; border-left-color: #6b4423; border-left-width: 4px; padding: 20px; margin: 20px 0; border-radius: 4px;">
        <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">Summary:</p>
        <div style="space-y: 10px;">
            <p class="info-text" style="margin: 10px 0; line-height: 1.6;">
                ✓ Payment receipt reviewed and verified
            </p>
            <p class="info-text" style="margin: 10px 0; line-height: 1.6;">
                ✓ Booking status updated to confirmed
            </p>
            <p class="info-text" style="margin: 10px 0; line-height: 1.6;">
                ✓ Seat status updated to booked
            </p>
            <p class="info-text" style="margin: 10px 0; line-height: 1.6;">
                ✓ Client confirmed and ready for travel
            </p>
        </div>
    </div>
    
    <!-- Action Button -->
    <div class="button-container">
        <a href="{{ config('app.url') }}/admin/tours" class="button">
            View Tour Details
        </a>
    </div>
    
    <!-- Additional Info -->
    <div class="content-section">
        <p class="section-text" style="font-size: 14px; color: #666666; margin-top: 20px; line-height: 1.6;">
            This booking is now fully confirmed and the client is ready for travel. You can view all booking details and manage tours from your admin dashboard.
        </p>
    </div>
@endsection

@section('footer')
    <p class="footer-text">
        This is an automated notification. The booking has been successfully confirmed and is ready for travel. You can view all tour and booking details in your admin dashboard.
    </p>
    <p class="footer-text" style="margin-top: 15px;">
        <a href="{{ config('app.url') }}/admin/tours" class="footer-link">Go to Admin Dashboard</a>
    </p>
@endsection
