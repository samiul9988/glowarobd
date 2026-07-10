@extends('emails.layouts.main')
@section('title', 'Payment Information')
@section('content')
    <tr>
        <td class="content" style="padding:30px; color:#333; font-size:16px; line-height:1.5;">
            <p>Hi <strong>{{ $info['customer'] }}</strong>,</p>
            <p>Thank you for your order! 🙌 Below are the payment details for your recent purchase.</p>

            <!-- Payment Summary -->
            <div style="background:#fff5e6; border:1px solid #ffd699; border-radius:6px; padding:20px; margin:20px 0;">
                <table class="payment-summary" width="100%" cellpadding="5" cellspacing="0"
                    style="font-size:15px; color:#444;">
                    <tr>
                        <td><strong>Order ID:</strong></td>
                        <td>#{{ $info['order_id'] }}</td>
                    </tr>
                    @if (filled(trim($info['payment_method'] ?? '')))
                        <tr>
                            <td><strong>Payment Method:</strong></td>
                            <td>{{ $info['payment_method'] }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td><strong>Amount Paid:</strong></td>
                        <td>{{ $info['amount'] }}</td>
                    </tr>
                    <tr>
                        <td bgcolor="#f4f8fb" style="padding:15px; text-align:center; font-size:12px; color:#888;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                            This is an automated message. Please do not reply directly to this email.<br>
                            <a href="{{ config('app.frontend') }}" style="color:#4fc3f7; text-decoration:none;">Visit our
                                website</a>
                            {{-- <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                                <div style="display: flex; gap: 6px; justify-content: start;">
                                    <a style="font-size:16px; background-color: rgb(59, 89, 152);width:28px;height:28px;border-radius: 50px; display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer; " target="_blank" href="">@include('icons.facebook')</a>
                                    <a style="font-size:16px;  background-color: rgb(189, 50, 162);width:28px;height:28px;border-radius: 50px; display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer; " target="_blank" href="">@include('icons.instagram')</a>
                                    <a style="font-size:16px; background-color: rgb(255, 0, 0);width:28px;height:28px;border-radius: 50px;  display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer;" target="_blank" href="">@include('icons.youtube')</a>
                                </div>
                                <div style="display: flex; gap: 6px; justify-content: end;">
                                   <a style="font-size:16px; background-color: rgb(0, 0, 0);width:28px;height:28px;border-radius: 50px; display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer;" target="_blank" href="">@include('icons.play-store')</a>
                                   <a style="font-size:16px; background-color: rgb(59, 89, 152);width:28px;height:28px;border-radius: 50px; display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer;" target="_blank" href="">@include('icons.app-store')</a>
                                </div>
                            </div> --}}
                        </td>
                    </tr>
                </table>
            </div>

            <p>If you have any questions about your payment or order, our support team is here to help you.</p>

            <!-- Button -->
            <p class="button" style="text-align:center; margin:30px 0;">
                <a href="{{ route('orders.track', ['order_code' => $info['order_id']]) }}" target="_blank"
                    style="background:#4fc3f7; color:#fff; text-decoration:none; padding:12px 25px; border-radius:4px; font-weight:bold; display:inline-block;">
                    View Order Details
                </a>
            </p>

            <p style="margin-top:20px;">Thank you for shopping with <strong>{{ config('app.name') }}</strong>.
                We truly appreciate your trust in us and look forward to serving you again! 🛍️</p>

            <p style="margin-top:10px;">Warm regards,<br>The {{ config('app.name') }} Team</p>
        </td>
    </tr>
@endsection
