@extends('layout.email-layout')

@section('title', 'Reservation Request Received - ' . config('app.name'))

@section('content')
    <!-- Greeting -->
    <h1 class="greeting">Hello, {{ $data['agentName'] ?? 'Agent' }}!</h1>
    <p class="subtitle">Your seat reservation request has been received and is being processed.</p>
    
    <!-- Reservation Details Section -->
    <div class="info-box" style="border-left-color: #22c55e;">
        <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">Reservation Request Details:</p>
        <p class="info-text"><strong>Tour Package:</strong> {{ $data['packageTitle'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Seat Number:</strong> {{ $data['seatNumber'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Client Name:</strong> {{ $data['clientName'] ?? 'N/A' }}</p>
    </div>
    
    <!-- Status Information -->
    <div class="content-section">
        <p class="section-text" style="font-size: 15px; color: #666666; line-height: 1.6;">
            Your reservation request has been successfully submitted and forwarded to the admin team for review. The seat has been temporarily reserved while your request is being processed.
        </p>
        <p class="section-text" style="font-size: 14px; color: #666666; margin-top: 15px; line-height: 1.6;">
            <strong>Next Steps:</strong> Please upload the payment receipt for this booking. Once the payment receipt is uploaded and confirmed, the booking will be finalized.
        </p>
    </div>
    
    <!-- Action Button -->
    <div class="button-container">
        <a href="{{ config('app.url') }}/agent/tours" class="button">
            View My Tours
        </a>
    </div>
    
    <!-- Additional Info -->
    <div class="content-section">
        <p class="section-text" style="font-size: 14px; color: #666666; margin-top: 20px; line-height: 1.6;">
            You will receive a confirmation email once your reservation has been approved by the admin team.
        </p>
    </div>
@endsection

@section('footer')
    <p class="footer-text">
        This is an automated confirmation email. If you have any questions about this reservation, please contact the admin team.
    </p>
    <p class="footer-text" style="margin-top: 15px;">
        <a href="{{ config('app.url') }}/agent/tours" class="footer-link">Go to Agent Dashboard</a>
    </p>
@endsection
