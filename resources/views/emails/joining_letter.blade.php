@extends('emails.layouts.main')
@section('title', 'Interview Invitation - ' . $role)

@section('content')
    @if (!is_null($content))
        {!! $content !!}
    @else
        <tr>
            <td class="content" style="padding:25px; color:#333; font-size:15px; line-height:1.5;">
                <p>Dear <strong>{{$candidate_name}}</strong>,</p>

                <p>Welcome to <strong>{{config('app.name')}}</strong>! Your employment has officially started from <strong>{{$joining_date}}</strong> as <strong>{{$role}}</strong>.</p>

                <p>
                    <strong>Employee ID:</strong> {{$employee_id}}<br>
                    <strong>Joining Date:</strong> {{$joining_date}}<br>
                    <strong>Reporting Time:</strong> {{$reporting_time}}
                </p>

                <p>Please collect your laptop & ID card from HR on arrival. Find the attachment below for more details.</p>

                <p>Once again, congratulations and welcome to the team!</p>

                <p>— HR Team<br>{{config('app.name')}}</p>
            </td>
        </tr>
    @endif
@endsection
