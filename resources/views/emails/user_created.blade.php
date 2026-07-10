<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Coupon Assigned</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <style>
        /* General reset for responsiveness */
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        table {
            border-collapse: collapse;
        }

        /* Mobile styles */
        @media only screen and (max-width: 620px) {
            .container {
                width: 100% !important;
            }

            .content {
                padding: 20px !important;
            }

            h2 {
                font-size: 18px !important;
            }

            p {
                font-size: 14px !important;
            }

            .coupon-code {
                font-size: 20px !important;
            }

            .button a {
                padding: 10px 18px !important;
                font-size: 14px !important;
            }
        }
    </style>
</head>

<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f8fb;">
    @php
        $logo = get_setting('header_logo');
    @endphp

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding:30px 15px;">
                <table class="container" border="0" cellpadding="0" cellspacing="0" width="600"
                    style="background:#ffffff; border-radius:8px; overflow:hidden; max-width:600px; width:100%;">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="background:#eaf6ff; padding:20px;">
                            @if ($logo != null)
                                <img src="{{ uploaded_asset($logo) }}" alt="{{ config('app.name') }}"
                                    style="max-width:150px; margin-bottom:10px;">
                            @else
                                <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}"
                                    style="max-width:150px; margin-bottom:10px;">
                            @endif
                            <h2 style="margin:0; font-size:20px; color:#004d80;">{{ config('app.name') }}</h2>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="content" style="padding:30px; color:#333; font-size:16px; line-height:1.5;">
                            <p>Hi <strong>{{ $user->name }}</strong>,</p>
                            <p>Welcome to {{ config('app.name') }}. Your account has been created successfully.</p>

                            <div>
                                <p><strong>Your Login Details:</strong></p>
                                <p>Email: {{ $user->email }}</p>
                                <p>Password: {{ $password }}</p>
                            </div>

                            <p style="margin-top:20px;">Don't forget to change your password after logging in. Please keep your login details secure.</p>

                            <!-- Button -->
                            <p class="button" style="text-align:center; margin:30px 0;">
                                <a href="{{ config('app.frontend') }}" target="_blank"
                                    style="background:#4fc3f7; color:#fff; text-decoration:none; padding:12px 25px; border-radius:4px; font-weight:bold; display:inline-block;">
                                    Shop Now
                                </a>
                            </p>

                            <p style="margin-top:10px;">Warm regards,<br>{{ config('app.name') }}</p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td bgcolor="#f4f8fb" style="padding:15px; text-align:center; font-size:12px; color:#888;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                            This is an automated message. Please do not reply directly to this email.<br>
                            <a href="{{ config('app.frontend') }}" style="color:#4fc3f7; text-decoration:none;">Visit our
                                website</a>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
