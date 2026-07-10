@extends('backend.layouts.app')

@section('content')

<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('Upcoming Delivery') }}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ ('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="bulk_delete()"> {{ ('Delete selection')}}</a>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exampleModal">
                        <i class="las la-sync-alt"></i>
                        {{ ('Change Order Status')}}
                    </a>
                </div>
            </div>

            <!-- Change Status Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">
                                {{ ('Choose an order status')}}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" style="min-height: 400px">
                            <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity" id="update_delivery_status">
                                <option value="pending">{{ ('Pending')}}</option>
                                <option value="confirmed">{{ ('Confirmed')}}</option>
                                <option value="picked_up">{{ ('Picked Up')}}</option>
                                <option value="on_the_way">{{ ('On The Way')}}</option>
                                <option value="delivered">{{ ('Delivered')}}</option>
                                <option value="cancelled">{{ ('Cancel')}}</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="change_status()">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="col-lg-2 ml-auto">
                <select class="form-control aiz-selectpicker" name="delivery_status" id="delivery_status">
                    <option value="">{{ ('Filter by Delivery Status')}}</option>
                    <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{ ('Pending')}}</option>
                    <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>{{ ('Confirmed')}}</option>
                    <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>{{ ('Picked Up')}}</option>
                    <option value="on_the_way" @if ($delivery_status == 'on_the_way') selected @endif>{{ ('On The Way')}}</option>
                    <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>{{ ('Delivered')}}</option>
                    <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>{{ ('Cancel')}}</option>
                </select>
            </div> --}}

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ ('Type Order code & customer') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{--<div class="btn-group btn-sm" role="group" aria-label="Basic example">
                <a href="{{ route('all_orders.status', 'pending') }}" class="btn btn-secondary @if($currentStatus=='pending') active @endif">Pending <span class="badge badge-light">{{ $deliveryStatusCount['pending'] }}</span></a>
                <a href="{{ route('all_orders.status', 'confirmed') }}" class="btn btn-secondary @if($currentStatus=='confirmed') active @endif">Confirmed <span class="badge badge-light">{{ $deliveryStatusCount['confirmed'] }}</span></a>
                <a href="{{ route('all_orders.status', 'picked_up') }}" class="btn btn-secondary @if($currentStatus=='picked_up') active @endif">Picked Up <span class="badge badge-light">{{ $deliveryStatusCount['picked_up'] }}</span></a>
                <a href="{{ route('all_orders.status', 'on_the_way') }}" class="btn btn-secondary @if($currentStatus=='on_the_way') active @endif">On The Way <span class="badge badge-light">{{ $deliveryStatusCount['on_the_way'] }}</span></a>
                <a href="{{ route('all_orders.status', 'delivered') }}" class="btn btn-secondary @if($currentStatus=='delivered') active @endif">Delivered <span class="badge badge-light">{{ $deliveryStatusCount['delivered'] }}</span></a>
                <a href="{{ route('all_orders.status', 'cancelled') }}" class="btn btn-secondary @if($currentStatus=='cancelled') active @endif">Cancelled <span class="badge badge-light">{{ $deliveryStatusCount['cancelled'] }}</span></a>
            </div>--}}

            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <!--<th>#</th>-->
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{ ('Order Code') }}</th>
                        <th data-breakpoints="md">{{ ('Num. of Products') }}</th>
                        <th data-breakpoints="md">{{ ('Customer') }}</th>
                        <th data-breakpoints="md">{{ ('Amount') }}</th>
                        <th data-breakpoints="md">{{ ('Delivery Status') }}</th>
                        <th data-breakpoints="md">{{ ('Payment Method')}}</th>
                        <th data-breakpoints="md">{{ ('Payment Status') }}</th>
                        @if (addon_is_activated('refund_request'))
                        <th>{{ ('Refund') }}</th>
                        @endif
                        <th data-breakpoints="lg">{{ ('Order Source')}}</th>
                        <th class="text-right" width="15%">{{ ('options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $key => $order)
                    <tr>
    <!--                    <td>
                            {{ ($key+1) + ($orders->currentPage() - 1)*$orders->perPage() }}
                        </td>-->
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{$order->id}}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ $order->code }}
                        </td>
                        <td>
                            {{ count($order->orderDetails) }}
                        </td>
                        <td>
                            <?php /*
                            @if ($order->user != null)
                            {{ $order->user->name }}<br>
                            {{ json_decode($order->shipping_address)->phone }}
                            @else
                            {{-- Guest ({{ $order->guest_id }}) --}}
                            */ ?>

                            {{ json_decode($order->shipping_address)->name }}<br>
                            {{ json_decode($order->shipping_address)->phone }}

                            <?php /* @endif */ ?>
                        </td>
                        <td>
                            {{ single_price($order->grand_total) }}
                        </td>
                        <td>
                            @php
                                $status = $order->delivery_status;
                                if($order->delivery_status == 'cancelled') {
                                    $status = '<span class="badge badge-inline badge-danger">'.translate('Cancel').'</span>';
                                }

                            @endphp
                            {!! $status !!}
                        </td>
                        <td>
                            {{ (ucfirst(str_replace('_', ' ', $order->payment_type))) }}
                        </td>
                        <td>
                            @if ($order->payment_status == 'paid')
                            <span class="badge badge-inline badge-success">{{ ('Paid')}}</span>
                            @else
                            <span class="badge badge-inline badge-danger">{{ ('Unpaid')}}</span>
                            @endif
                        </td>
                        @if (addon_is_activated('refund_request'))
                        <td>
                            @if (count($order->refund_requests) > 0)
                            {{ count($order->refund_requests) }} {{ ('Refund') }}
                            @else
                            {{ ('No Refund') }}
                            @endif
                        </td>
                        @endif
                        <td>
                            <span class="badge badge-inline badge-success">{{$order->order_source}}</span>
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('all_orders.show', encrypt($order->id))}}" title="{{ ('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('invoice.download', $order->id) }}" title="{{ ('Download Invoice') }}" target="_blank">
                                <i class="las la-download"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('orders.destroy', $order->id)}}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $orders->appends(request()->input())->links() }}
            </div>

        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

       function change_status() {
           var data = new FormData($('#sort_orders')[0]);
           data.append('status', $('#update_delivery_status').val());
           $.ajax({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               url: "{{route('bulk-order-status')}}",
               type: 'POST',
               data: data,
               cache: false,
               contentType: false,
               processData: false,
               success: function (response) {
                   if(response == 1) {
                       location.reload();
                   }
               }
           });
       }

        function bulk_delete() {
            var data = new FormData($('#sort_orders')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-order-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }
    </script>
@endsection
