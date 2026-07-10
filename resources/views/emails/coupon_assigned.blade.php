@extends('emails.layouts.main')
@section('title', 'Coupon Assigned')

@section('content')
    <tr>
        <td class="content" style="padding:30px; color:#333; font-size:16px; line-height:1.5;">
            <p>Hi <strong>{{ $info['customer'] }}</strong>,</p>
            <p>We’re excited to share some good news with you! As one of our valued customers, you’ve
                been selected to receive an exclusive coupon. 🎁</p>

            <p>Use this special code at checkout and enjoy instant savings on your next order. Don’t
                miss the chance to make your shopping even more rewarding!</p>

            <!-- Coupon Box -->
            <div
                style="background:#fff5e6; border:1px dashed #ff9800; border-radius:6px; text-align:center; padding:20px; margin:20px 0;">
                <p style="margin:0; font-size:14px; color:#555;">Your Coupon Code</p>
                <p class="coupon-code"
                    style="font-size:24px; font-weight:bold; letter-spacing:2px; margin:10px 0; color:#ff6f00;">
                    {{ $info['coupon'] }}
                </p>
                <p style="margin:0; font-size:14px; color:#777;">Valid until:
                    <strong>{{ $info['validity'] }}</strong>
                </p>
            </div>

            <p style="margin-top:20px;">Make sure to take advantage of it before it expires. Start
                shopping today and let your savings add up!</p>

            <!-- Button -->
            <p class="button" style="text-align:center; margin:30px 0;">
                <a href="{{ config('app.frontend') }}" target="_blank"
                    style="background:#4fc3f7; color:#fff; text-decoration:none; padding:12px 25px; border-radius:4px; font-weight:bold; display:inline-block;">
                    Shop Now
                </a>
            </p>

            <p style="margin-top:20px;">Thank you for choosing
                <strong>{{ config('app.name') }}</strong>. We’re committed to bringing you the best
                deals and shopping experience every time you visit us.
            </p>

            <p style="margin-top:10px;">Warm regards,<br>The {{ config('app.name') }} Team</p>
        </td>
    </tr>
@endsection
