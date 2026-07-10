@extends('emails.layouts.main')
@section('title', 'Interview Invitation - '.$role)

@section('content')
    @if(!is_null($content))
        {!! $content !!}
    @else
        <tr>
            <td class="content" style="padding:30px; color:#333; font-size:16px; line-height:1.5;">
                <p>Dear <strong>{{ $candidate_name }}</strong>,</p>
                <p>You are invited for an interview for the <strong>{{ $role }}</strong> at <strong>{{ config('app.name') }}</strong>.</p>

                <p>Please confirm your availability for the interview.</p>

                <p style="margin-top:20px;">
                    <strong>Date:</strong> {{ $interview_date }}<br>
                    <strong>Time:</strong> {{ $interview_time }}<br>
                </p>

                <p style="margin-top:10px;">— HR Team<br>{{config('app.name')}}</p>
            </td>
        </tr>
    @endif
@endsection
