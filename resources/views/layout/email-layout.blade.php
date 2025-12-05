<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', config('app.name'))</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset styles */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            outline: none;
            text-decoration: none;
        }
        
        /* Base styles */
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
            background-color: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
        }
        
        .email-wrapper {
            width: 100%;
            background-color: #f5f5f5;
            padding: 40px 20px;
        }
        
        .email-header {
            padding: 30px 20px 20px;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: #1e3a0f;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .logo-img {
            max-width: 200px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        
        .email-content-container {
            max-width: 600px;
            margin: 0 auto 30px;
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        
        .email-content {
            padding: 40px;
            background-color: #ffffff;
            text-align: left;
        }
        
        .greeting {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 10px 0;
            text-align: left;
        }
        
        .subtitle {
            font-size: 16px;
            color: #666666;
            margin: 0 0 30px 0;
            line-height: 1.5;
            text-align: left;
        }
        
        .content-section {
            margin: 30px 0;
            text-align: left;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 15px 0;
            text-align: left;
        }
        
        .section-text {
            font-size: 15px;
            color: #666666;
            margin: 0 0 20px 0;
            line-height: 1.6;
            text-align: left;
        }
        
        .icon-container {
            text-align: left;
            margin: 30px 0;
        }
        
        .icon-circle {
            display: inline-block;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #1e3a0f;
            margin: 0 0 20px 0;
            text-align: center;
            line-height: 80px;
        }
        
        .icon-circle.orange {
            background-color: #6b4423;
        }
        
        .icon-circle.brown {
            background-color: #6b4423;
        }
        
        .icon {
            width: 40px;
            height: 40px;
            color: #ffffff;
            vertical-align: middle;
            display: inline-block;
        }
        
        .button-container {
            text-align: left;
            margin: 30px 0;
        }
        
        .button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #1e3a0f;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            transition: background-color 0.3s ease;
            border: none;
        }
        
        .button:hover {
            background-color: #2d5016;
        }
        
        .button[style*="background-color: #6b4423"]:hover {
            background-color: #8b5a3c;
        }
        
        code {
            background-color: #ffffff;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            color: #1a1a1a;
            border: 1px solid #e5e7eb;
        }
        
        .info-box {
            background-color: #f9fafb;
            border-left: 4px solid #1e3a0f;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: left;
        }
        
        .info-text {
            font-size: 14px;
            color: #666666;
            margin: 5px 0;
            line-height: 1.5;
            text-align: left;
        }
        
        .footer {
            padding: 30px 20px;
            text-align: left;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .footer-link {
            color: #6b4423;
            text-decoration: none;
            font-size: 14px;
        }
        
        .footer-link:hover {
            text-decoration: underline;
        }
        
        .footer-text {
            font-size: 14px;
            color: #666666;
            margin: 10px 0;
            line-height: 1.6;
            text-align: left;
        }
        
        /* Responsive styles */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 20px 10px;
            }
            
            .email-content {
                padding: 30px 20px;
            }
            
            .email-header {
                padding: 20px 10px 15px;
            }
            
            .email-content-container {
                margin-bottom: 20px;
            }
            
            .greeting {
                font-size: 20px;
            }
            
            .button {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }
            
            .footer {
                padding: 20px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td align="center" style="padding: 0;">
                    <!-- Header -->
                    <div class="email-header">
                        <a href="{{ config('app.url') }}" style="display: inline-block; text-decoration: none;">
                            @php
                                $logoPath = public_path('africa-travel-bureau.png');
                                $logoUrl = asset('africa-travel-bureau.png');
                                $logoExists = file_exists($logoPath);
                            @endphp
                            @if($logoExists)
                                <img src="{{ $logoUrl }}" alt="{{ config('app.name', 'Africa Travel Bureau') }}" class="logo-img" style="max-width: 200px; height: auto; display: block; margin: 0 auto;" />
                            @else
                                <span class="logo">{{ config('app.name', 'Africa Travel Bureau') }}</span>
                            @endif
                        </a>
                    </div>
                    
                    <!-- Content Container -->
                    <div class="email-content-container">
                        <div class="email-content">
                            @yield('content')
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="footer">
                        @yield('footer')
                        
                        <p class="footer-text">
                            &copy; {{ date('Y') }} {{ config('app.name', 'Group Safari') }}. All rights reserved.
                        </p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

