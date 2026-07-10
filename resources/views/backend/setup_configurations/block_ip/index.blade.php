@php
    error_reporting(0);
@endphp
@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ ('Block Ip List')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>{{ ('Ip Address')}}</th>
                    <th>{{ ('User')}}</th>
                    <th>{{ ('Reason')}}</th>
                    <th>{{ ('Block Time')}}</th>
                    <th class="text-right">{{ ('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($blockIps as $blockIp)
                    <tr>
                        <td>{{$loop->index + 1}}</td>
                        <td> <strong>{{$blockIp->ip}}</strong></td>
                        <td>
                            <span>Phone: {{ @$blockIp->user->phone }}</span> <br>
                            <span>Name: {{ @$blockIp->user->name }}</span>
                        </td>
                        <td>{{$blockIp->reason}}</td>
                        <td><strong>{{date("F j, Y, g:i a",strtotime($blockIp->created_at))}}</strong></td>

                        <td class="text-right">
                             <a href="#" class="btn btn-primary btn-sm confirm-delete" data-href="{{route('block.ip.destroy', $blockIp->id)}}" title="{{ ('destroy') }}">
                                {{ ('Remove Blocklist') }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $blockIps->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">

    </script>
@endsection
