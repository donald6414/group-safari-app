@extends('layout.email-layout')

@section('title', 'Payment Receipt Received - ' . config('app.name'))

@section('content')
    <!-- Greeting -->
    <h1 class="greeting">Hello, {{ $data['agentName'] ?? 'Agent' }}!</h1>
    <p class="subtitle">We have successfully received your payment receipt and our team is currently reviewing it.</p>
    
    <!-- Booking Details Section -->
    <div class="info-box">
        <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">Booking Details:</p>
        <p class="info-text"><strong>Tour Package:</strong> {{ $data['packageTitle'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Seat Number:</strong> {{ $data['seatNumber'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Client Name:</strong> {{ $data['clientName'] ?? 'N/A' }}</p>
    </div>
    
    <!-- Review Status Section -->
    <div class="content-section">
        <p class="section-text" style="font-size: 15px; color: #666666; line-height: 1.6;">
            <strong>Review Status:</strong> Your payment receipt is currently being reviewed by our team. We will verify the payment details and process your booking accordingly.
        </p>
    </div>
    
    <!-- What Happens Next Section -->
    <div class="info-box" style="background-color: #f9fafb; border-left-color: #6b4423; border-left-width: 4px; padding: 20px; margin: 20px 0; border-radius: 4px;">
        <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">What Happens Next?</p>
        <div style="space-y: 10px;">
            <p class="info-text" style="margin: 10px 0; line-height: 1.6;">
                <strong>✓ If the receipt is valid:</strong> Your booking will be confirmed automatically, and you will receive a confirmation email.
            </p>
            <p class="info-text" style="margin: 10px 0; line-height: 1.6;">
                <strong>ℹ If we need more details:</strong> Our team will reach out to you directly for additional information or clarification.
            </p>
        </div>
    </div>
    
    <!-- Action Button -->
    <div class="button-container">
        <a href="{{ config('app.url') }}/agent/tours" class="button">
            View My Bookings
        </a>
    </div>
    
    <!-- Additional Info -->
    <div class="content-section">
        <p class="section-text" style="font-size: 14px; color: #666666; margin-top: 20px; line-height: 1.6;">
            You can track the status of this booking in your agent dashboard. We appreciate your patience as we review your payment receipt.
        </p>
    </div>
@endsection

@section('footer')
    <p class="footer-text">
        This is an automated confirmation email. If you have any questions about your payment receipt or booking status, please contact our support team.
    </p>
    <p class="footer-text" style="margin-top: 15px;">
        <a href="{{ config('app.url') }}/agent/tours" class="footer-link">Go to Agent Dashboard</a>
    </p>
@endsection
