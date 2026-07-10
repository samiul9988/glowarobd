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
                    Congratulations!
                </p>
                <p>
                    We are delighted to announce your promotion at <strong>{{ config('app.name') }}</strong>.
                    This achievement reflects your dedication, hard work, leadership,
                    and valuable contribution to the organization.
                </p>
                <p>
                    We are confident that you will continue to excel in your new responsibilities
                    and contribute positively toward the continued success of the company.
                </p>
                <p>
                    Please find the attached Promotion Letter PDF containing the complete details
                    regarding your new designation and revised compensation structure.
                </p>
                <p>
                    We wish you continued success and growth in your professional journey with us.
                </p>
                <p>
                    — HR Team<br>
                    {{ config('app.name') }}
                </p>
            </td>
        </tr>
    @endif
@endsection
