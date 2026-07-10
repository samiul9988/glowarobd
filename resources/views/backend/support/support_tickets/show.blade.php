@extends('backend.layouts.app')

@section('content')
<div class="row gutters-5">
    <div class="{{ $ticket->order ? 'col-lg-7' : 'col-lg-10 mx-auto' }}">
        <div class="card">
            <div class="card-header d-block">
                <div class="row gutters-5">
                    <div class="col">
                        <span class="d-block font-weight-bold h6">
                            @if($ticket->user && $ticket->user->user_type == 'customer')
                                <a href="{{ route('customers.details', $ticket->user->id) }}" target="_blank">
                                    {{ $ticket->name ?? 'Customer' }}
                                </a>
                                <span class="fs-12 text-muted"> - {{ group_identity($ticket->user_id) }}</span>
                            @else
                                {{ $ticket->name ?? 'Customer' }}
                            @endif
                        </span>
                        <span class="d-block">{{ $ticket->phone }}</span>
                    </div>
                    <div class="col text-center">
                        <span class="d-block font-weight-bold h6">{{ Str::headline($ticket->issue) }}</span>
                        @if ($ticket->order_id && $ticket->order)
                            <a href="{{ route('all_orders.show', encrypt($ticket->order_id)) }}" target="_blank" class="font-weight-bold">#{{ $ticket->order?->code }}</a>
                        @endif
                    </div>
                    <div class="col d-flex justify-content-end">
                        <div class="text-center">
                            <span class="d-block mb-2">
                                @php
                                    $class = match($ticket->status) {
                                        'open' => 'warning',
                                        'working' => 'info',
                                        'closed' => 'secondary',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge badge-inline badge-{{ $class }} font-weight-bold">
                                    {{ ($ticket->status) }}
                                </span>
                            </span>
                            <span class="d-block">
                                At {{ \carbon\Carbon::parse($ticket->status == 'closed' ? $ticket->closed_at : $ticket->created_at)->format('d-m-Y h:i A') }}
                            </span>
                        </div>
                    </div>
                </div>
                @if($ticket->status == 'closed')
                    <div class="row gutters-5 mt-4">
                        <div class="col">
                            @if ($ticket->closedBy)
                                <span class="d-block font-weight-bold"> {{ ('Resolved By') }} </span>
                                <span class="d-block">{{ $ticket->closedBy->name }}</span>
                            @endif
                        </div>
                        <div class="col">
                        </div>
                        <div class="col d-flex justify-content-end">
                            @if(!is_null($ticket->closed_at))
                            <div class="text-center">
                                <span class="d-block font-weight-bold">
                                    {{ ('Resolve Time') }}
                                </span>
                                <span class="d-block">
                                    @php
                                        $closedAt = \Carbon\Carbon::parse($ticket->closed_at);
                                        $createdAt = \Carbon\Carbon::parse($ticket->created_at);

                                        $diff = $createdAt->diffInMinutes($closedAt);
                                        $hours = floor($diff / 60);
                                        $minutes = $diff % 60;
                                    @endphp
                                    @if($hours > 0)
                                        {{ $hours }} {{ $hours > 1 ? 'Hours' : 'Hour' }}
                                    @endif
                                    {{ $minutes }} {{ $minutes > 1 ? 'Minutes' : 'Minute' }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    @php
                        $log = $ticket->logs->where('action', 'created')->first();
                    @endphp
                    <div class="col">
                        <span class="font-weight-bold">{{ ('Created By') }}:</span>
                        <span class="">
                            @if($log && $log->user)
                                <span class="text-success font-weight-bold font-italic">{{ $log->user->name }}</span>
                            @else
                                {{ ('Customer') }}
                            @endif
                            At {{ \carbon\Carbon::parse($ticket->created_at)->format('d-m-Y h:i A') }}
                        </span>
                    </div>
                </div>
                @if($ticket->status == 'closed')
                    <div class="alert alert-danger">
                        {{ ('This ticket is closed. You cannot reply to it.') }}
                    </div>
                @endif
                <span class="d-block fs-13">{{ $ticket->subject }}</span>
                <div class="my-2 fs-15 border rounded p-4">
                    {{ $ticket->details }}
                    @if($ticket->files)
                        <div class="mt-2">
                            @foreach ((explode(",",$ticket->files)) as $key => $file)
                                @php $file_detail = \App\Models\Upload::where('id', $file)->first(); @endphp
                                @if($file_detail != null)
                                    <img class="preview rounded mb-1" src="{{ uploaded_asset($file) }}" alt="{{ $file_detail->file_original_name.'.'.$file_detail->extension }}" data-file="{{ uploaded_asset($file) }}" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'; $(this).data('file', '');" style="width: 100px; height: 100px; cursor: pointer;">
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                @if($ticket->status != 'closed')
                    @if ($ticket->ticketReplies->isNotEmpty())
                    <div class="pad-top">
                        <ul class="list-group list-group-flush">
                            @php
                                $ticketreply = $ticket->ticketReplies->first();
                            @endphp
                            <li class="list-group-item px-0">
                                <div class="media">
                                    <a class="media-left" href="#">
                                        @if($ticketreply->user->avatar_original != null)
                                            <span class="avatar avatar-sm mr-3"><img src="{{ uploaded_asset($ticketreply->user->avatar_original) }}"></span>
                                        @else
                                            <span class="avatar avatar-sm mr-3"><img src="{{ static_asset('assets/img/avatar-place.png') }}"></span>
                                        @endif
                                    </a>
                                    <div class="media-body">
                                        <div class="">
                                            <span class="font-weight-bold fs-14">{{ $ticketreply->user->name }}</span>
                                            @if($ticketreply->user->user_type != 'customer')
                                                <span class="text-muted text-sm font-weight-bold fs-11 d-block">
                                                    {{ $ticketreply->user->staff?->role?->name ?? translate($ticketreply->user->user_type) }}
                                                </span>
                                            @endif
                                            <p class="text-muted text-sm fs-11">{{ $ticketreply->created_at->format('d-m-Y h:i A') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="">
                                    @php echo $ticketreply->reply; @endphp

                                    <div class="mt-3">
                                    @foreach ((explode(",",$ticketreply->files)) as $key => $file)
                                        @php $file_detail = \App\Models\Upload::where('id', $file)->first(); @endphp
                                        @if($file_detail != null)
                                            <img class="preview rounded mb-1" src="{{ uploaded_asset($file) }}" alt="{{ $file_detail->file_original_name.'.'.$file_detail->extension }}" data-file="{{ uploaded_asset($file) }}" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'; $(this).data('file', '');" style="width: 80px; height: 80px; cursor: pointer;">
                                        @endif
                                    @endforeach
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    @endif
                    <form action="{{ route('tickets.admin_store') }}" method="post" id="ticket-reply-form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{$ticket->id}}" required>
                        <input type="hidden" name="status" value="{{ $ticket->status }}" required>
                        <div class="form-group">
                            <textarea class="aiz-text-editor" data-buttons='[["font", ["bold", "underline", "italic"]],["para", ["ul", "ol"]],["view", ["undo","redo"]]]' name="reply" required></textarea>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="attachments" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-info" onclick="submit_reply('working')">
                                {{ ('Submit as') }}
                                <strong>
                                    <span class="text-capitalize">
                                        {{ ('Working') }}
                                    </span>
                                </strong>
                            </button>
                            <button type="submit" class="btn btn-sm btn-secondary" onclick="submit_reply('closed')">
                                {{ ('Submit as') }}
                                <strong>
                                    <span class="text-capitalize">
                                        {{ ('Closed') }}
                                    </span>
                                </strong>
                            </button>
                        </div>
                    </form>
                @endif
                <hr>
                <div class="pad-top">
                    <ul class="list-group list-group-flush">
                        @forelse($ticket->ticketReplies as $ticketreply)
                            @if($ticket->status != 'closed' && $loop->index == 0)
                                @continue
                            @endif
                            <li class="list-group-item px-0">
                                <div class="media">
                                    <a class="media-left" href="#">
                                        @if($ticketreply->user->avatar_original != null)
                                            <span class="avatar avatar-sm mr-3"><img src="{{ uploaded_asset($ticketreply->user->avatar_original) }}"></span>
                                        @else
                                            <span class="avatar avatar-sm mr-3"><img src="{{ static_asset('assets/img/avatar-place.png') }}"></span>
                                        @endif
                                    </a>
                                    <div class="media-body">
                                        <div class="">
                                            <span class="font-weight-bold fs-14">{{ $ticketreply->user->name }}</span>
                                            @if($ticketreply->user->user_type != 'customer')
                                                <span class="text-muted text-sm font-weight-bold fs-11 d-block">
                                                    {{ $ticketreply->user->staff?->role?->name ?? translate($ticketreply->user->user_type) }}
                                                </span>
                                            @endif
                                            <p class="text-muted text-sm fs-11">{{ $ticketreply->created_at->format('d-m-Y h:i A') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="">
                                    @php echo $ticketreply->reply; @endphp

                                    <div class="mt-3">
                                    @foreach ((explode(",",$ticketreply->files)) as $key => $file)
                                        @php $file_detail = \App\Models\Upload::where('id', $file)->first(); @endphp
                                        @if($file_detail != null)
                                            <img class="preview rounded mb-1" src="{{ uploaded_asset($file) }}" alt="{{ $file_detail->file_original_name.'.'.$file_detail->extension }}" data-file="{{ uploaded_asset($file) }}" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'; $(this).data('file', '');" style="width: 80px; height: 80px; cursor: pointer;">
                                        @endif
                                    @endforeach
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-center font-weight-bold fs-14">
                                No replies yet.
                            </li>
                        @endforelse
                    </ul>
                </div>
                @if ($ticket->rating)
                    <hr>
                    <div class="text-center">
                        <h6 class="font-weight-bold h6">
                            {{ ('Customer Review') }}
                        </h6>
                        <span class="fs-20">
                            @foreach (range(1, 5) as $i)
                                <i class="las la-star text-{{ $i <= $ticket->rating ? 'warning' : 'secondary'}}"></i>
                            @endforeach
                        </span>
                        <span class="fs-16">
                            | {{ ucfirst($ticket->review) }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @if ($ticket->order)
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-1"><strong>Order #</strong> <span class="text-info">{{ $ticket->order->code }}</span></li>
                                <li class="mb-1"><strong>Order Date:</strong> {{ date('d-m-Y h:i A', $ticket->order->date) }}</li>
                                @if($ticket->order->payment_status == 'paid')
                                <li class="mb-1"><strong>Payment Method:</strong> {{ strtoupper($ticket->order->payment_type) }}</li>
                                @endif
                                <li class="mb-1"><strong>Order Status:</strong> {!! order_status_badge($ticket->order) !!}</li>
                                <li class="mb-1"><strong>Payment Status:</strong> {!! payment_status_badge($ticket->order) !!}</li>
                                <li class="mb-1"><strong>Order Source:</strong>
                                    <span class="badge badge-inline badge-success">
                                        {{strtoupper($ticket->order->order_source)}}
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                @php
                                    $shipping_address = json_decode($ticket->order->shipping_address, true);
                                @endphp
                                <li class="mb-1"><strong>Name:</strong> {{ data_get($shipping_address, 'name') }}</li>
                                <li class="mb-1"><strong>Phone:</strong> {{ data_get($shipping_address, 'phone') }}</li>
                                @if(data_get($shipping_address, 'email')!='')
                                    <li class="mb-1"><strong>Email:</strong> {{ data_get($shipping_address, 'email') }}</li>
                                @endif
                                <li class="mb-1"><strong>Address:</strong>
                                    {{ data_get($shipping_address, 'address') }}
                                    City: {{ data_get($shipping_address, 'city') }},
                                    Area: {{ data_get($shipping_address, 'area') }},
                                    @if(data_get($shipping_address, 'postal_code')!='')
                                    Postal Code: {{ data_get($shipping_address, 'postal_code') }}<br>
                                    @endif
                                    {{ data_get($shipping_address, 'country') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    {{-- <hr class="new-section-sm bord-no"> --}}
                    <div class="row mt-3">
                        <div class="col-lg-12 table-responsive">
                            <table class="table table-bordered aiz-table invoice-summary">
                                <thead>
                                    <tr class="bg-trans-dark">
                                        <th data-breakpoints="lg" class="min-col">#</th>
                                        <th width="10%" class="text-center">{{ ('Photo')}}</th>
                                        <th class="text-uppercase">{{ ('Description')}}</th>
                                        <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Qty')}}</th>
                                        <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Price')}}</th>
                                        <th data-breakpoints="lg" class="min-col text-center text-uppercase">{{ ('Total')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ticket->order->orderDetails as $key => $orderDetail)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td class="text-center">
                                                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                                    <a href="{{ to_frontend(route('product', $orderDetail->product->slug)) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                                @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                                    <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}"></a>
                                                @else
                                                    <strong>{{ ('N/A') }}</strong>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                                    <strong><a href="{{ to_frontend(route('product', $orderDetail->product->slug)) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                                    <small>{{ $orderDetail->variation }}</small>
                                                @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                                    <strong><a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank" class="text-muted">{{ $orderDetail->product->getTranslation('name') }}</a></strong>
                                                @else
                                                    <strong>{{ ('Product Unavailable') }}</strong>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ $orderDetail->quantity }}
                                            </td>
                                            <td class="text-center">{{ single_price($orderDetail->price/$orderDetail->quantity) }}</td>
                                            <td class="text-center">{{ single_price($orderDetail->price) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="clearfix float-right">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Sub Total')}} :</strong>
                                    </td>
                                    <td class="text-muted ">
                                        {{ single_price($ticket->order->orderDetails->sum('price')) }}
                                    </td>
                                </tr>
                                @if($ticket->order->coupon_discount>0 || $ticket->order->reward_point_discount>0)
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Discount')}} @if(@$ticket->order->orderDetails[0]->coupon_code!=NULL) ({{ $ticket->order->orderDetails[0]->coupon_code }}) @endif :</strong>
                                    </td>
                                    <td class="text-muted">
                                        {{ single_price($ticket->order->coupon_discount) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Reward point discount')}} @if(@$ticket->order->orderDetails[0]->reward_point_discount!=NULL) ({{ $ticket->order->orderDetails[0]->reward_point_discount }}) @endif :</strong>
                                    </td>
                                    <td class="text-muted ">
                                        {{ single_price($ticket->order->reward_point_discount) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('GRAND TOTAL')}} :</strong>
                                    </td>
                                    <td class="h6">
                                        {{ single_price($ticket->order->orderDetails->sum('price') - ($ticket->order->coupon_discount + $ticket->order->reward_point_discount)) }}
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Tax')}} :</strong>
                                    </td>
                                    <td class="text-muted ">
                                        {{ single_price($ticket->order->orderDetails->sum('tax')) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Shipping')}} :</strong>
                                    </td>
                                    <td class="text-muted ">
                                        {{ single_price($ticket->order->orderDetails->sum('shipping_cost')) }}
                                    </td>
                                </tr>
                                @php
                                    $recentOrderPaidAmount = $ticket->order->payments?->sum('amount') ?? 0;
                                    $recentOrderTotal = $ticket->order->orderDetails->sum('price') + $ticket->order->orderDetails->sum('tax') + $ticket->order->orderDetails->sum('shipping_cost') - ($ticket->order->coupon_discount + $ticket->order->reward_point_discount + $recentOrderPaidAmount);
                                @endphp
                                @if($ticket->order->payment_status != 'unpaid' && $recentOrderPaidAmount > 0)
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('Paid Amount')}} :</strong>
                                    </td>
                                    <td>
                                        {{ single_price($recentOrderPaidAmount) }} (-)
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td>
                                        <strong class="text-muted">{{ ('NET TOTAL')}} :</strong>
                                    </td>
                                    <td class="h6">
                                        {{ single_price($recentOrderTotal) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
@section('script')
    <script type="text/javascript">
        function submit_reply(status){
            $('input[name=status]').val(status);
            let reply = $('textarea[name=reply]').val();
            let attachments = $('input[name=attachments]').val();
            if(reply.length > 0){
                $('#ticket-reply-form').submit();
            } else {
                showAlert('error', '{{ ('Please write a reply') }}');
            }
        }
        $('.preview').on('click', function() {
            let file = $(this).data('file');
            if (!file) {
                showAlert('error', '{{ ('File not found') }}');
                return;
            }
            window.open(file, '_blank');
        });
    </script>
@endsection
