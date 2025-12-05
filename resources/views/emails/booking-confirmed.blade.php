@extends('layout.email-layout')

@section('title', 'Booking Confirmed - ' . config('app.name'))

@section('content')
    <!-- Greeting -->
    <h1 class="greeting">Congratulations, {{ $data['agentName'] ?? 'Agent' }}!</h1>
    <p class="subtitle">Your payment receipt has been reviewed and verified. Your booking is now confirmed!</p>
    
    <!-- Success Confirmation Box -->
    <div class="info-box" style="background-color: #f9fafb; border-left-color: #1e3a0f; border-left-width: 4px; padding: 20px; margin: 20px 0; border-radius: 4px;">
        <p class="info-text" style="font-weight: 600; color: #1e3a0f; margin-bottom: 15px; font-size: 18px;">✓ Payment Verified & Booking Confirmed</p>
        <p class="info-text" style="color: #333333; line-height: 1.6;">
            We have successfully reviewed your payment receipt and confirmed that the payment is valid. Your booking has been officially confirmed, and you can now prepare for your upcoming travel adventure!
        </p>
    </div>
    
    <!-- Booking Details Section -->
    <div class="info-box">
        <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">Confirmed Booking Details:</p>
        <p class="info-text"><strong>Tour Package:</strong> {{ $data['packageTitle'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Seat Number:</strong> {{ $data['seatNumber'] ?? 'N/A' }}</p>
        <p class="info-text"><strong>Client Name:</strong> {{ $data['clientName'] ?? 'N/A' }}</p>
    </div>
    
    <!-- Next Steps Section -->
    <div class="content-section">
        <p class="section-text" style="font-size: 15px; color: #666666; line-height: 1.6; margin-bottom: 20px;">
            <strong>What's Next?</strong> Now that your booking is confirmed, it's time to prepare for your journey. Make sure you have all the necessary travel documents and information ready.
        </p>
    </div>
    
    <!-- Preparation Checklist -->
    <div class="info-box" style="background-color: #f9fafb; border-left-color: #6b4423; border-left-width: 4px; padding: 20px; margin: 20px 0; border-radius: 4px;">
        <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">Get Ready for Travel:</p>
        <div style="space-y: 10px;">
            <p class="info-text" style="margin: 10px 0; line-height: 1.6;">
                ✓ Review all travel documents and requirements
            </p>
            <p class="info-text" style="margin: 10px 0; line-height: 1.6;">
                ✓ Confirm travel dates and itinerary details
            </p>
            <p class="info-text" style="margin: 10px 0; line-height: 1.6;">
                ✓ Prepare necessary travel items and documents
            </p>
            <p class="info-text" style="margin: 10px 0; line-height: 1.6;">
                ✓ Stay updated with any tour information or changes
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
            If you have any questions about your confirmed booking or need assistance with travel preparations, please don't hesitate to contact our support team. We're here to help make your journey smooth and enjoyable!
        </p>
    </div>
@endsection

@section('footer')
    <p class="footer-text">
        This is an automated confirmation email. Your booking is confirmed and ready for travel. We look forward to providing you with an amazing travel experience!
    </p>
    <p class="footer-text" style="margin-top: 15px;">
        <a href="{{ config('app.url') }}/agent/tours" class="footer-link">Go to Agent Dashboard</a>
    </p>
@endsection
