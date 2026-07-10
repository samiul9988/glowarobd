@extends('emails.layouts.main')
@section('title', 'Interview Invitation - ' . $role)

@section('content')
    @if (!is_null($content))
        {!! $content !!}
    @else
        <tr>
            <td class="content" style="padding:25px; color:#333; font-size:15px; line-height:1.5;">
                <p>Dear <strong>{{$candidate_name}}</strong>,</p>

                <p>We are pleased to appoint you as <strong>{{$role}}</strong> at <strong>{{config('app.name')}}</strong>.</p>

                <p>
                    <strong>Joining Date:</strong> {{$joining_date}} at {{$reporting_time}}<br>
                    <strong>Working Shift:</strong> {{$working_shift}}<br>
                    <strong>Working Hours:</strong> {{$working_hours}}<br>
                    <strong>Salary:</strong> {{$salary}}<br>
                    <strong>Probation:</strong> 3 months
                </p>

                <p>Please confirm your acceptance by <strong>{{$deadline}}</strong>.</p>

                <p>Welcome aboard! 🎉</p>

                <p>— HR Team<br>{{config('app.name')}}</p>
            </td>
        </tr>
    @endif
@endsection
