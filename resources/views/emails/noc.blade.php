@extends('emails.layouts.main')
@section('title', 'Interview Invitation - ' . $role)

@section('content')
    @if (!is_null($content))
        {!! $content !!}
    @else
        <tr>
            <td class="content" style="padding:25px; color:#333; font-size:14px; line-height:1.5;">
                <p>Dear <strong>{{ @$candidate_name }}</strong>,</p>

                <p>Please find attached the No Objection Certificate (NOC) issued at {{ $issue_date }} in your favor.</p>

                <p>This certificate is issued at your request. Please retain a copy for your records.</p>

                <p>— HR Team<br>{{config('app.name')}}</p>
            </td>
        </tr>
    @endif
@endsection
