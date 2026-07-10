@extends('emails.layouts.main')
@section('title', 'Interview Invitation - ' . $role)

@section('content')
    @if (!is_null($content))
        {!! $content !!}
    @else
        <tr>
            <td class="content" style="padding:25px; color:#333; font-size:15px; line-height:1.7;">
                <p>Dear <strong>{{ $candidate_name }}</strong>,</p>
                <p>
                    We are pleased to inform you that your salary has been revised in recognition of your dedication,
                    performance, and valuable contribution to <strong>{{ config('app.name') }}</strong>.
                </p>
                <p>
                    Your hard work and commitment have been highly appreciated by the management,
                    and we look forward to your continued success with the organization.
                </p>
                <p>
                    Please find the attached Increment Letter PDF containing the complete details
                    of your revised compensation and effective date.
                </p>
                <p>
                    Congratulations and best wishes for your continued growth and success.
                </p>
                <p>
                    — HR Team<br>
                    {{ config('app.name') }}
                </p>
            </td>
        </tr>
    @endif
@endsection
