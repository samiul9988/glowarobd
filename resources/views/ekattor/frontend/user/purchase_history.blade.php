@extends(config('app.theme').'frontend.layouts.user_panel')

@section('panel_content')
    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">Purchase History</h1>
            </div>
        </div>
    </div>
    @if (count($orders) > 0)
        @foreach ($orders as $key => $order)
            @if (count($order->orderDetails) > 0)
                <div class="card rounded p-3 compact_purchase_list">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h6 class="text-danger font-weight-bold">{{ $order->code }}</h6>
                            <p><i class="fal fa-calendar"></i> {{ date('d-m-Y', $order->date) }}</p>
                            <p>
                                <i class="fal fa-credit-card"></i>
                                <span>{{ translate('Payment Status')}} : </span>
                                <span>
                                    @if ($order->payment_status == 'paid')
                                        <span class="">{{translate('Paid')}} <i class="fas fa-check-circle text-success"></i></span>
                                    @elseif($order->payment_status == 'refunded')
                                        <span class="">{{translate('Refunded')}} <i class="fas fa-check-circle text-warning"></i></span>
                                    @else
                                        <span class="">{{translate('Unpaid')}} <i class="fas fa-times-circle text-danger"></i></span>
                                    @endif
                                    @if($order->payment_status_viewed == 0)
                                        <span class="ml-2" style="color:green"><strong>*</strong></span>
                                    @endif
                                </span>
                            </p>
                            <p>
                                <i class="fal fa-truck-moving"></i>
                                <span>{{ translate('Delivery Status')}} : </span>
                                <span>
                                    {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                                    @if($order->delivery_viewed == 0)
                                        <span class="ml-2" style="color:green"><strong>*</strong></span>
                                    @endif
                                </span>
                            </p>
                        </div>
                        <div class="col-4 text-right">
                            <h6 class="text-danger font-weight-bold">{{ single_price($order->grand_total) }}</h6>
                            <a href="javascript:void(0)" class="btn btn-success rounded-pill py-1 px-1" onclick="show_purchase_history_details({{ $order->id }})" title="{{ translate('Order Details') }}">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
        <div class="aiz-pagination">
            {{ $orders->links() }}
        </div>
    @endif


    <div class="card d-none">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Purchase History') }}</h5>
        </div>
        @if (count($orders) > 0)
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Code')}}</th>
                            <th data-breakpoints="md">{{ translate('Date')}}</th>
                            <th>{{ translate('Amount')}}</th>
                            <th data-breakpoints="md">{{ translate('Delivery Status')}}</th>
                            <th data-breakpoints="md">{{ translate('Payment Status')}}</th>
                            <th class="text-right">{{ translate('Options')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $key => $order)
                            @if (count($order->orderDetails) > 0)
                                <tr>
                                    <td>
                                        <a href="#{{ $order->code }}" onclick="show_purchase_history_details({{ $order->id }})">{{ $order->code }}</a>
                                    </td>
                                    <td>{{ date('d-m-Y', $order->date) }}</td>
                                    <td>
                                        {{ single_price($order->grand_total) }}
                                    </td>
                                    <td>
                                        {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                                        @if($order->delivery_viewed == 0)
                                            <span class="ml-2" style="color:green"><strong>*</strong></span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($order->payment_status == 'paid')
                                            <span class="badge badge-inline badge-success">{{translate('Paid')}}</span>
                                        @elseif($order->payment_status == 'refunded')
                                            <span class="badge badge-inline badge-warning">{{translate('Refunded')}}</span>
                                        @else
                                            <span class="badge badge-inline badge-danger">{{translate('Unpaid')}}</span>
                                        @endif
                                        @if($order->payment_status_viewed == 0)
                                            <span class="ml-2" style="color:green"><strong>*</strong></span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        {{-- @if ($order->orderDetails->first()->delivery_status == 'pending' && $order->payment_status == 'unpaid')
                                            <a href="javascript:void(0)" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('orders.destroy', $order->id)}}" title="{{ translate('Cancel') }}">
                                               <i class="las la-trash"></i>
                                           </a>
                                        @endif --}}
                                        <a href="javascript:void(0)" class="btn btn-soft-info btn-icon btn-circle btn-sm" onclick="show_purchase_history_details({{ $order->id }})" title="{{ translate('Order Details') }}">
                                            <i class="las la-eye"></i>
                                        </a>
                                        <a class="btn btn-soft-warning btn-icon btn-circle btn-sm" href="{{ route('invoice.download', $order->id) }}" title="{{ translate('Download Invoice') }}">
                                            <i class="las la-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $orders->links() }}
              	</div>
            </div>
        @endif
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')

    <div class="modal fade" id="order_details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content p-0">
                <div id="order-details-modal-body">

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="payment_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div id="payment_modal_body">

                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        $('#order_details').on('hidden.bs.modal', function () {
            location.reload();
        })
    </script>

@endsection
