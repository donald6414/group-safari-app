@extends('layout.email-layout')

@section('title', ($data['isDueDate'] ? 'Reservation Due Date Reached' : 'Reservation Due Date Reminder') . ' - ' . config('app.name'))

@section('content')
    @if($data['isDueDate'])
        <!-- Greeting -->
        <h1 class="greeting">Dear {{ $data['agentName'] ?? 'Agent' }},</h1>
        <p class="subtitle">This is to notify you that the reservation due date for the seat you reserved has been reached today.</p>
        
        <!-- Warning Box -->
        <div class="info-box" style="background-color: #fef2f2; border-left-color: #dc2626; border-left-width: 4px; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <p class="info-text" style="font-weight: 600; color: #dc2626; margin-bottom: 15px; font-size: 18px;">⚠ Reservation Due Date Reached</p>
            <p class="info-text" style="color: #333333; line-height: 1.6;">
                The seat reservation has expired and the seat has been released back to <strong>available</strong> status. The reservation period has ended as the due date has passed.
            </p>
        </div>
        
        <!-- Reservation Details Section -->
        <div class="info-box">
            <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">Reservation Details:</p>
            <p class="info-text"><strong>Package:</strong> {{ $data['packageTitle'] ?? 'N/A' }}</p>
            <p class="info-text"><strong>Seat Number:</strong> {{ $data['seatNumber'] ?? 'N/A' }}</p>
            <p class="info-text"><strong>Client Name:</strong> {{ $data['clientName'] ?? 'N/A' }}</p>
        </div>
        
        <!-- Next Steps Section -->
        <div class="content-section">
            <p class="section-text" style="font-size: 15px; color: #666666; line-height: 1.6; margin-bottom: 20px;">
                <strong>What This Means:</strong> The seat you reserved is now available for other bookings. If you wish to proceed with this booking, please create a new reservation.
            </p>
        </div>
        
        <!-- Action Button -->
        <div class="button-container">
            <a href="{{ config('app.url') }}/agent/tours" class="button">
                View Tours
            </a>
        </div>
        
        <!-- Additional Info -->
        <div class="content-section">
            <p class="section-text" style="font-size: 14px; color: #666666; margin-top: 20px; line-height: 1.6;">
                If you have any questions about this reservation or need assistance creating a new booking, please don't hesitate to contact our support team.
            </p>
        </div>
    @else
        <!-- Greeting -->
        <h1 class="greeting">Dear {{ $data['agentName'] ?? 'Agent' }},</h1>
        <p class="subtitle">This is a reminder that the seat you reserved is approaching its due date.</p>
        
        <!-- Reminder Box -->
        <div class="info-box" style="background-color: #fffbeb; border-left-color: #f59e0b; border-left-width: 4px; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <p class="info-text" style="font-weight: 600; color: #f59e0b; margin-bottom: 15px; font-size: 18px;">⏰ Time Remaining</p>
            <p class="info-text" style="color: #333333; line-height: 1.6; font-size: 16px;">
                <strong>{{ $data['daysLeft'] ?? 0 }} {{ ($data['daysLeft'] ?? 0) == 1 ? 'day' : 'days' }}</strong> left until the reservation due date.
            </p>
        </div>
        
        <!-- Reservation Details Section -->
        <div class="info-box">
            <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">Reservation Details:</p>
            <p class="info-text"><strong>Package:</strong> {{ $data['packageTitle'] ?? 'N/A' }}</p>
            <p class="info-text"><strong>Seat Number:</strong> {{ $data['seatNumber'] ?? 'N/A' }}</p>
            <p class="info-text"><strong>Client Name:</strong> {{ $data['clientName'] ?? 'N/A' }}</p>
        </div>
        
        <!-- Important Notice Section -->
        <div class="info-box" style="background-color: #f9fafb; border-left-color: #6b4423; border-left-width: 4px; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px; font-size: 16px;">Important Notice:</p>
            <p class="info-text" style="margin: 10px 0; line-height: 1.6;">
                Please take necessary action before the due date expires. Once the due date passes, the seat will be automatically released back to <strong>available</strong> status.
            </p>
        </div>
        
        <!-- Action Button -->
        <div class="button-container">
            <a href="{{ config('app.url') }}/agent/tours" class="button">
                View Tours
            </a>
        </div>
        
        <!-- Additional Info -->
        <div class="content-section">
            <p class="section-text" style="font-size: 14px; color: #666666; margin-top: 20px; line-height: 1.6;">
                If you have any questions about this reservation or need assistance, please don't hesitate to contact our support team. We're here to help!
            </p>
        </div>
    @endif
@endsection

@section('footer')
    <p class="footer-text">
        This is an automated notification email. {{ $data['isDueDate'] ? 'The reservation due date has been reached and the seat has been released.' : 'Please take action before the reservation due date expires.' }}
    </p>
    <p class="footer-text" style="margin-top: 15px;">
        <a href="{{ config('app.url') }}/agent/tours" class="footer-link">Go to Agent Dashboard</a>
    </p>
@endsection
