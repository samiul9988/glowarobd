@extends('backend.layouts.app')

@section('content')
@php
    $isAdmin = Auth::check() && Auth::user()->user_type == 'admin';
    $permissions = json_decode(Auth::user()?->staff?->role?->permissions, true) ?? [];
@endphp
<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('All Orders') }}</h5>
                <a href="{{ route('services.manage') }}" target="_blank">
                    Track Order
                </a>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ ('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="javascript:;" onclick="bulk_delete_modal()"> {{ ('Delete selection')}}</a>
                    <a class="dropdown-item" href="javascript:;" onclick="bulk_print()"> {{ ('Bulk Invoice Print')}}</a>
                    {{-- @if (in_array($currentStatus, ['merchant', 'confirmed', 'hold'])) --}}
                    <a class="dropdown-item" href="javascript:;" onclick="bulk_product_print()"> {{ ('Bulk Product Print')}}</a>
                    {{-- @endif --}}
                    <a class="dropdown-item" href="javascript:;" onclick="bulk_shipping_print()"> {{ ('Bulk Shipping Label Print')}}</a>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exampleModal">
                        <i class="las la-sync-alt"></i>
                        {{ ('Change Order Status')}}
                    </a>
                </div>
            </div>

            {{-- Change Status Modal --}}
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
                                @if(in_array(@$call_status, ['shipment_failed', 'others']))
                                    <option value="picked_up" selected>{{ ('Picked Up') }}</option>
                                @endif
                                @foreach(statusWiseOrderStatuses($currentStatus) as $status)
                                    @if ($status == 'hold')
                                        @continue
                                    @endif
                                    <option value="{{ $status }}">{{ucfirst(translate($status))}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="bulk-update-status">Save changes</button>
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
                    {{-- <select class="form-control" name="source">
                        <option value="">{{ ('Filter by order source') }}</option>
                        @foreach ($order_sources ?? [] as $key => $order_source)
                            @if(strlen(trim($order_source)))
                                <option value="{{ strtolower($order_source) }}" @if(strtolower($order_source) == strtolower($source)) selected @endif>{{ strtoupper($order_source) }}</option>
                            @endif
                        @endforeach
                    </select> --}}
                    <select class="form-control aiz-selectpicker mb-2 mb-md-0" id="order_source" name="source">
                        <option value="">{{ ('Order Source') }}</option>
                        <option value="android" @if (strtolower($source)=='android') selected @endif>{{ ('Android') }}</option>
                        <option value="ios" @if (strtolower($source)=='ios') selected @endif>{{ ('iOS') }}</option>
                        <option value="website" @if (strtolower($source)=='website') selected @endif>{{ ('Website') }}</option>
                        <option value="pos" @if (strtolower($source)=='pos') selected @endif>{{ ('POS') }}</option>
                        <option value="merchant" @if (strtolower($source)=='merchant') selected @endif>{{ ('Merchant') }}</option>
                        <option value="showroom" @if (strtolower($source)=='showroom') selected @endif>{{ ('Showroom') }}</option>
                    </select>
                </div>
            </div>
            @if($currentStatus == 'hold')
                <div class="col-lg-2">
                    <div class="form-group mb-0">
                        <select class="form-control" name="call_status" id="call_status">
                            <option value="">{{ ('Filter by hold status') }}</option>
                            @foreach (getHoldStatuses() as $value => $text)
                                <option value="{{ $value }}" @if ($call_status == $value) selected @endif>{{ (ucwords($text)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
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
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group btn-sm" role="group" aria-label="Basic example">
                    @include('backend.sales.all_orders.status')
                </div>
                @if($orders->isNotEmpty())
                    <div>
                        <button type="button" class="btn btn-soft-primary btn-sm" onclick="location.href='{{ route('orders.export', array_merge(request()->query(), ['status' => $currentStatus])) }}'">{{ ('Export') }}</button>

                        @if($isAdmin || in_array('create_order_return_request', $permissions))
                            <a href="{{ route('return-orders.create') }}" class="btn btn-soft-success btn-sm">
                                {{ ('Return Request')}}
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
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
                        <th data-breakpoints="md">{{ ('Group') }}</th>
                        <th data-breakpoints="md">{{ ('Customer') }}</th>
                        <th data-breakpoints="md">{{ ('Amount') }}</th>
                        <th data-breakpoints="xl">{{ ('Delivery Status') }}</th>
                        <th data-breakpoints="md">{{ ('Payment Method')}}</th>
                        @if ($currentStatus == 'hold')
                            <th data-breakpoints="md">{{ ('Hold Status') }}</th>
                        @else
                            <th data-breakpoints="md">{{ ('Payment Status') }}</th>
                        @endif
                        @if (addon_is_activated('refund_request'))
                            <th>{{ ('Refund') }}</th>
                        @endif
                        <th data-breakpoints="lg">{{ ('Order Source')}}</th>
                        <th data-breakpoints="lg">{{ ('Note/Reason')}}</th>
                        <th class="text-right" width="15%">{{ ('options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $key => $order)
                    @php
                        $call_status = $order->callLogs->first()?->status ?? 'N/A';
                        $call_status = ucwords(str_replace('_', ' ', $call_status));
                    @endphp
                    <tr @if($order->pendingReturnRequest) class="bg-soft-secondary" @endif>
                        {{-- <td>
                            {{ ($key+1) + ($orders->currentPage() - 1)*$orders->perPage() }}
                        </td> --}}
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
                            <span class="d-block">
                                {{ $order->code }}
                                @if (hasGiftItem($order))
                                    @includeIf('components.tooltip', [
                                        'title' => 'This order contains gift items.',
                                    ])
                                @endif
                            </span>
                            @if ($order->guest_order)
                                <span class="badge badge-inline badge-info">Guest Order</span>
                            @endif
                        </td>
                        <td>
                            {{ count($order->orderDetails) }}
                        </td>
                        <td>
                            {!! $order->user?->customeringroup?->group?->group_name ?? 'New User' !!}
                            {{-- {!! $order->user->group ?? 'Unknown' !!} --}}
                        </td>
                        <td>
                            {{ @json_decode($order->shipping_address)->name }}<br>
                            {{ @json_decode($order->shipping_address)->phone }}
                        </td>
                        <td>
                            {{-- {{ single_price($order->grand_total) }} --}}
                            {{ single_price(get_order_grand_total($order)) }}
                            {{-- | {{ single_price(intval(get_order_due_amount($order))) }} --}}
                        </td>
                        <td class="text-capitalize">
                            @php
                                $status = $order->delivery_status;
                                if($order->delivery_status == 'cancelled') {
                                    $status = '<span class="badge badge-inline badge-danger">Cancel</span>';
                                }
                            @endphp
                            {!! $status !!}
                        </td>
                        <td>
                            {{ (ucfirst(str_replace('_', ' ', $order->payment_type))) }}
                        </td>
                        @if ($currentStatus == 'hold')
                            <td>
                                {{ ($call_status) }}
                            </td>
                        @else
                            <td>
                                @if ($order->payment_status == 'paid')
                                    <span class="badge badge-inline badge-success">{{ ('Paid')}}</span>
                                @elseif ($order->payment_status == 'unpaid')
                                    <span class="badge badge-inline badge-danger">{{ ('Unpaid')}}</span>
                                @else
                                    <span class="badge badge-inline badge-info">{{ (ucfirst($order->payment_status))}}</span>
                                @endif
                            </td>
                        @endif
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
                            <span class="badge badge-inline badge-success">
                                {{strtoupper($order->order_source)}}
                            </span>
                            @if ($order->orderTrack)
                                <br>
                                <span class="badge badge-inline badge-info">
                                    {{strtoupper(\App\Enums\UtmSources::value($order->orderTrack->utm_source))}}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($currentStatus == 'cancelled' && $order->cancellation)
                                <span class="d-block">
                                    <strong class="text-danger">Reason:</strong> {{ ucfirst(Str::limit($order->cancellation?->reason ?? 'N/A', 30)) }}
                                    @if (strlen($order->cancellation?->reason ?? '') > 30)
                                        @include('components.tooltip', [
                                            'title' => ucfirst($order->cancellation?->reason),
                                        ])
                                    @endif
                                </span>
                                <span class="d-block text-muted">- by {{ $order->cancellation?->cancelledBy?->name ?? 'N/A' }} At {{ $order->cancellation?->created_at?->format('d-m-Y h:i A') ?? 'N/A' }}</span>
                            @else
                                <span class="d-block">
                                    <strong>Note:</strong> {{ ucfirst(Str::limit($order->callLogs->first()?->note ?? 'N/A', 30)) }}
                                    @if (strlen($order->callLogs->first()?->note ?? '') > 30)
                                        @include('components.tooltip', [
                                            'title' => ucfirst($order->callLogs->first()?->note),
                                        ])
                                    @endif
                                </span>
                            @endif
                        </td>
                        <td class="text-right">
                            @include('backend.sales.all_orders.actions')
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
    @include('modals.bulk_delete_modal')
@endsection
@section('script')
    <script type="text/javascript">
        // getStatusCount();
        async function getStatusCount() {
            try {
                const response = await fetch("{{ route('orders.get-status-count') }}");
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                // Update the counts in the UI
                const statusCounts = data.counts;
                for (const [status, count] of Object.entries(statusCounts)) {
                    const countElement = $(`#${status}-count`);
                    if (countElement) {
                        countElement.text(count);
                    }
                }
            } catch (error) {
                console.error('Error fetching status counts:', error);
            }
        }
        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }
        });

        $('#bulk-update-status').on('click', async function() {
            const selectedOrders = $('.check-one:checkbox:checked').length;
            if (selectedOrders === 0) {
                showAlert('error', 'Please select at least one order to update the status.');
                return;
            }
            const status = $('#update_delivery_status').val();
            $('#exampleModal').modal('hide');
            if (status === 'cancelled') {
                try {
                    const cancellationReason = await takeReason();
                    if (cancellationReason.trim() === '') {
                        showAlert('error', 'Cancel request aborted: No reason provided.');
                        return;
                    } else {
                        change_status(cancellationReason);
                    }
                } catch (error) {
                    console.error('Error getting cancellation reason:', error);
                    return;
                }
            } else {
                change_status(); // For other status changes
            }
        });

        function change_status(cancellationReason = {}) {
            var data = new FormData($('#sort_orders')[0]);
            data.append('status', $('#update_delivery_status').val());
            data.append('reason', cancellationReason);
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
                        showAlert('success', 'Order status updated successfully.', window.location.href);
                    } else {
                        showAlert('error', 'Something went wrong. Please try again.');
                    }
                },
                error: function(response) {
                    showAlert('error', 'Server Error!');
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

        function bulk_print() {
            var data = new FormData($('#sort_orders')[0]);
            var ids = '';
            $.each($('#sort_orders').serializeArray(), function(k,v){
                if(v.name=='id[]')
                ids += v.value+',';
            });
            window.open(
                '{{route('invoice.invoice_bulk_download')}}?ids='+ids.replace(/,\s*$/, ""),
                '_blank'
                )
        }
        function bulk_product_print() {
            var status = '{{ $currentStatus }}';
            var data = new FormData($('#sort_orders')[0]);
            var ids = '';
            $.each($('#sort_orders').serializeArray(), function(k,v){
                if(v.name=='id[]')
                ids += v.value+',';
            });
            // if(ids.length == 0){
            //     AIZ.plugins.notify('danger', '{{ ('Please select order') }}');
            //     return;
            // }
            window.open(
                '{{route('orders.bulk_product_download')}}?status='+status+'&ids='+ids.replace(/,\s*$/, ""),
                '_blank' // This is what makes it open in a new window.
                )
        }
        function bulk_shipping_print() {
            var data = new FormData($('#sort_orders')[0]);
            var ids = '';
            $.each($('#sort_orders').serializeArray(), function(k,v){
                if(v.name=='id[]')
                ids += v.value+',';
            });
            window.open(
                '{{route('invoice.invoice_bulk_shipping_download')}}?ids='+ids.replace(/,\s*$/, ""),
                '_blank' // This is what makes it open in a new window.
                )
        }
        function bulk_delete_modal(){
            $('#bulk_delete-modal').modal('show');
        }
    </script>
@endsection
