@extends('emails.layouts.main')
@section('title', 'Product Back In Stock')

@section('content')
    <tr>
        <td class="content" style="padding:30px; color:#333; font-size:16px; line-height:1.5;">
            <p><strong>Good News! 🎉</strong></p>
            <p>The product you're waiting for is now available!</p>

            <!-- Button -->
            <p class="button" style="text-align:center; margin:30px 0;">
                <a href="{{ to_frontend(route('product', $product->slug)) }}" target="_blank"
                    style="background:#4fc3f7; color:#fff; text-decoration:none; padding:8px 20px; border-radius:4px; font-weight:bold; display:inline-block; font-size:14px;">
                    View Product
                </a>
            </p>

            <p style="margin-top:20px;">Hurry before it runs out again!</p>

            <p style="margin-top:10px; font-size: 15px;">Warm regards,<br><span
                    style="font-size: 12px; font-weight: bold;">{{ config('app.name') }}</span></p>
        </td>
    </tr>
@endsection
