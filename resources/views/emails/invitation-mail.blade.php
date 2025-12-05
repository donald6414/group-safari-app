@extends('layout.email-layout')

@section('title', 'Welcome to ' . config('app.name'))

@section('content')
    <!-- Greeting -->
    <h1 class="greeting">Hello, {{ $data['name'] }}!</h1>
    <p class="subtitle">Your agent account has been created successfully.</p>
    
    <!-- Credentials Section -->
    <div class="info-box">
        <p class="info-text" style="font-weight: 600; color: #1a1a1a; margin-bottom: 15px;">Your Login Credentials:</p>
        <p class="info-text"><strong>Email:</strong> {{ $data['email'] ?? 'Your registered email' }}</p>
        <p class="info-text"><strong>Temporary Password:</strong> <code>{{ $data['password'] }}</code></p>
        <p class="info-text" style="margin-top: 15px; font-size: 13px; color: #6b4423;">
            <strong>Important:</strong> Please change your password after your first login.
        </p>
    </div>
    
    <!-- Action Button -->
    <div class="button-container">
        <a href="{{ config('app.url') }}/login" class="button">
            Login to Your Account
        </a>
    </div>
    
    <!-- Additional Info -->
    <div class="content-section">
        <p class="section-text" style="font-size: 14px; color: #666666; margin-top: 20px;">
            If you have any questions, please contact our support team.
        </p>
    </div>
@endsection

@section('footer')
    <p class="footer-text">
        Wasn't you who received this invitation? Please contact us immediately by replying to this email.
    </p>
    <p class="footer-text" style="margin-top: 15px;">
        <a href="{{ config('app.url') }}/help" class="footer-link">Need help? Visit our Help Center</a>
    </p>
@endsection
