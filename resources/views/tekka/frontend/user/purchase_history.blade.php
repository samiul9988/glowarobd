@extends(config('app.theme').'frontend.layouts.user_panel')

@section('meta')
<x-seo />
@endsection

@section('panel_content')
    <!-- <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">Purchase History</h1>
            </div>
        </div>
    </div> -->
    <div class="user-profile row bg-white py-2 rounded-sm align-items-center m-0 mb-3 px-2">
        <div class=" p-0 col-12 col-md-6 py-2 py-md-0">
            <p class="m-0 fw-500 fs-24 text-capitalize  text-lg-left text-start ">
                <span class="fw-700 " style="color:#FA7E16">Welcome, </span> {{ Auth::user()->name }}
            </p>
        </div>
        <div class="col-6 p-0 align-items-center justify-content-center justify-content-md-end pr-1 pr-md-4 d-none d-md-flex">
                <span class="avatar avatar-md pr-2 pr-md-0">
                    @if (Auth::user()->avatar_original != null)
                        <img src="{{ uploaded_asset(Auth::user()->avatar_original) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                    @else
                        <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="image rounded-circle" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                    @endif

                </span>
                @php

                    @$user_group = $currentlyAuthenticatedUser->customeringroup;

                @endphp
                <div>
                    <h4 class="h5 fs-16 fw-500 text-capitalize mb-1">
                        {{ Auth::user()->name }}
                    </h4>
                    @if(Auth::user()->phone != null)
                        <div class="text-truncate opacity-60 fs-14">{{ Auth::user()->phone }}</div>
                    @else
                        <div class="text-truncate opacity-60 fs-14">{{ Auth::user()->email }}</div>
                    @endif
                </div>
            </div>
    </div>
    @if (count($orders) > 0)
       <div class="purchase-history-wrapper">
           {{-- Search and Filter options --}}
           {{-- TODO:  --}}
            {{-- <div class="purchase-history-search">
                <div class="row">
                    <div class="col-6 p-0 ">
                       <form action="">
                            <input class="searchbar" type="text" placeholder="Search" autofocus required>
                            <button class="fa fa-search" type="submit"></button>
                       </form>
                    </div>
                    <div class="col-6 d-flex align-items-center justify-content-end purchase-sorby">
                        <h5>Sort By</h5>
                        <span class="pipeline"></span>
                        <select name="" id="">
                            <option value="all">All</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>

                        </select>
                    </div>
                </div>
            </div> --}}


            <table class=" purchase-history-table">
                <tr class="fs-16 fw-500">
                    <th>Invoice ID</th>
                    <th>Date</th>
                    <th>Delivery</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="d-flex justify-content-center">Action</th>
                </tr>
                @foreach ($orders as $key => $order)
                    @if (count($order->orderDetails) > 0)
                        <tr class="">
                            <td class="text-dark">{{ $order->code }}</td>
                            <td>{{ date('d-m -Y', $order->date) }}</td>
                            <td>
                                {{ ucfirst(translate(str_replace('_', ' ', $order->delivery_status))) }}
                                @if($order->delivery_viewed == 0)
                                    <span class="ml-2" style="color:green"></span>
                                @endif
                            </td>
                            <td>
                                {{ single_price(get_order_grand_total($order)) }}
                            </td>
                            <td>
                                @if ($order->payment_status == 'paid')
                                    <span class="text-success">{{ ('Paid')}}</span>
                                @elseif($order->payment_status == 'refunded')
                                    <span class="text-warning">{{ ('Refunded')}} </span>
                                @elseif($order->payment_status == 'unpaid')
                                    <span class="text-danger">{{ ('Un-paid')}}</span>
                                @else
                                    <span class="text-info">{{ (ucfirst($order->payment_status))}}</span>
                                @endif
                                @if($order->payment_status_viewed == 0)
                                    <span class="ml-2" style="color:green"></span>
                                @endif
                            </td>
                            <td class="d-flex justify-content-center">
                                <a href="javascript:void(0)" class="btn "             onclick="show_purchase_history_details({{ $order->id }})" title="{{ ('Order Details') }}">
                                    Details
                                </a>
                            </td>
                        </tr>



                        {{-- <!-- old -->
                        <div class="card rounded p-3 compact_purchase_list ">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h6 class="text-danger font-weight-bold">{{ $order->code }}</h6>
                                    <p><i class="fal fa-calendar"></i> {{ date('d-m-Y', $order->date) }}</p>
                                    <p>
                                        <i class="fal fa-credit-card"></i>
                                        <span>{{ ('Payment Status')}} : </span>
                                        <span>
                                            @if ($order->payment_status == 'paid')
                                                <span class="">{{ ('Paid')}} <i class="fas fa-check-circle text-success"></i></span>
                                            @elseif($order->payment_status == 'refunded')
                                                <span class="">{{ ('Refunded')}} <i class="fas fa-check-circle text-warning"></i></span>
                                            @else
                                                <span class="">{{ ('Unpaid')}} <i class="fas fa-times-circle text-danger"></i></span>
                                            @endif
                                            @if($order->payment_status_viewed == 0)
                                                <span class="ml-2" style="color:green"><strong>*</strong></span>
                                            @endif
                                        </span>
                                    </p>
                                    <p>
                                        <i class="fal fa-truck-moving"></i>
                                        <span>{{ ('Delivery Status')}} : </span>
                                        <span>
                                            {{ (ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                                            @if($order->delivery_viewed == 0)
                                                <span class="ml-2" style="color:green"><strong>*</strong></span>
                                            @endif
                                        </span>
                                    </p>
                                </div>
                                <div class="col-4 text-right">
                                    <h6 class="text-danger font-weight-bold">{{ single_price($order->grand_total) }}</h6>
                                    <a href="javascript:void(0)" class="btn btn-success rounded-pill py-1 px-1" onclick="show_purchase_history_details({{ $order->id }})" title="{{ ('Order Details') }}">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div> <!-- old -->
                        --}}

                    @endif
                @endforeach
            </table>
       </div>
       <div class="purchase-history-pagination">
            <div class="aiz-pagination">
                {{ $orders->links() }}
            </div>
       </div>

    @endif


    <div class="card d-none">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ('Purchase History') }}</h5>
        </div>
        @if (count($orders) > 0)
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ ('Code')}}</th>
                            <th data-breakpoints="md">{{ ('Date')}}</th>
                            <th>{{ ('Amount')}}</th>
                            <th data-breakpoints="md">{{ ('Delivery Status')}}</th>
                            <th data-breakpoints="md">{{ ('Payment Status')}}</th>
                            <th class="text-right">{{ ('Options')}}</th>
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
                                        {{ (ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                                        @if($order->delivery_viewed == 0)
                                            <span class="ml-2" style="color:green"><strong>*</strong></span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($order->payment_status == 'paid')
                                            <span class="badge badge-inline badge-success">{{ ('Paid')}}</span>
                                        @elseif($order->payment_status == 'refunded')
                                            <span class="badge badge-inline badge-warning">{{ ('Refunded')}}</span>
                                        @else
                                            <span class="badge badge-inline badge-danger">{{ ('Unpaid')}}</span>
                                        @endif
                                        @if($order->payment_status_viewed == 0)
                                            <span class="ml-2" style="color:green"><strong>*</strong></span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        {{-- @if ($order->orderDetails->first()->delivery_status == 'pending' && $order->payment_status == 'unpaid')
                                            <a href="javascript:void(0)" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('orders.destroy', $order->id)}}" title="{{ ('Cancel') }}">
                                               <i class="las la-trash"></i>
                                           </a>
                                        @endif --}}
                                        <a href="javascript:void(0)" class="btn btn-soft-info btn-icon btn-circle btn-sm" onclick="show_purchase_history_details({{ $order->id }})" title="{{ ('Order Details') }}">
                                            <i class="las la-eye"></i>
                                        </a>
                                        <a class="btn btn-soft-warning btn-icon btn-circle btn-sm" href="{{ route('invoice.download', $order->id) }}" title="{{ ('Download Invoice') }}">
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
