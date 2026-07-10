@extends('backend.layouts.app')
@section('content')
    <style>
        .lds-dual-ring.hidden {
            display: none;
        }
        .lds-dual-ring {
            display: inline-block;
            width: 80px;
            height: 80px;
        }
        .lds-dual-ring:after {
            content: " ";
            display: block;
            width: 64px;
            height: 64px;
            margin: 5% auto;
            border-radius: 50%;
            border: 6px solid #fff;
            border-color: #fff transparent #fff transparent;
            animation: lds-dual-ring 1.2s linear infinite;
        }
        @keyframes lds-dual-ring {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0,0,0,.8);
            z-index: 999;
            opacity: 1;
            transition: all 0.5s;
        }
    </style>
    <div id="loader" class="lds-dual-ring hidden overlay"></div>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ('Customer Refund Requests')}}</h5>
        </div>
        <div class="card-body">
            <div class="btn-group btn-sm" role="group" aria-label="Basic example">
                <a href="{{ route('refund_request.status', 'pending') }}" class="btn btn-secondary @if($currentStatus=='pending') active @endif">Pending <span class="badge badge-light">{{ $statusCount['pending'] }}</span></a>
                <a href="{{ route('refund_request.status', 'approved') }}" class="btn btn-secondary @if($currentStatus=='approved') active @endif">Approved <span class="badge badge-light">{{ $statusCount['approved'] }}</span></a>
                <a href="{{ route('refund_request.status', 'cancelled') }}" class="btn btn-secondary @if($currentStatus=='cancelled') active @endif">Cancelled <span class="badge badge-light">{{ $statusCount['cancelled'] }}</span></a>
            </div>
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th data-breakpoints="lg">#</th>
                        <th data-breakpoints="lg">Order Code:</th>
                        <th data-breakpoints="lg">{{ ('Date')}}</th>
                        <th>{{ ('Customer')}}</th>
                        <th data-breakpoints="lg">{{ ('Refund Amount')}}</th>
                        <th>{{ ('Payment Type')}}</th>
                        <th data-breakpoints="lg">{{ ('Reason') }}</th>
                        <th data-breakpoints="lg" width="15%">{{ ('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach($requests as $key => $request)
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>
                            <a href="{{route('all_orders.show', encrypt(@$request->order->id))}}" target="_blank" alt="{{ @$request->order->code }}">
                                {{ $request->order->code ?? 'Order Not Found' }}
                            </a>
                        </td>
                        <td>{{ $request->created_at }}</td>
                        <td>
                            @if ($request->user != null)
                                Name: {{ $request->user->name }} <br>
                                Email: {{ $request->user->email }} <br>
                                Phone: {{ $request->user->phone }}
                            @endif
                        </td>
                        <td>{{ single_price($request->refund_amount) }}</td>
                        <td>{{ $request->payment_type }}</td>
                        <td>
                            {{ $request->reason }}
                        </td>
                        <td>
                            @if($request->status == 'pending')
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" onclick="acceptRefund('{{$request->id}}');" title="{{ ('Accept Request') }}">
                                <i class="las la-money-bill"></i>
                            </a>
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" onclick="cancelRefund('{{$request->id}}');" title="{{ ('Cancel Request') }}">
                                <i class="las la-ban"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $requests->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function acceptRefund(id){
            if(confirm('Are you sure you want to refund this order?')){
                $('#loader').removeClass('hidden');
                $.post('{{ route('refund_request.accept') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
                    $('#loader').addClass('hidden');
                    location.reload();
                });
            }
        }

        function cancelRefund(id){
            if(confirm('Are you sure you want to cancel this refund request?')){
                $('#loader').removeClass('hidden');
                $.post('{{ route('refund_request.cancel') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
                    $('#loader').addClass('hidden');
                    location.reload();
                });
            }
        }
    </script>
@endsection
