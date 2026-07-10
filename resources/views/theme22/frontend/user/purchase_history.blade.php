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
                                <span>{{ ('Payment Status')}} : </span>
                                <span>
                                    @if ($order->payment_status == 'paid')
                                        <span class="">{{ ('Paid')}} <i class="fas fa-check-circle text-success"></i></span>
                                    @elseif($order->payment_status == 'refunded')
                                        <span class="">{{ ('Refunded')}} <i class="fas fa-check-circle text-warning"></i></span>
                                    @elseif($order->payment_status == 'unpaid')
                                        <span class="">{{ ('Unpaid')}} <i class="fas fa-times-circle text-danger"></i></span>
                                    @else
                                        <span class="">{{ (ucfirst($order->payment_status))}} <i class="fas fa-money-bill-alt text-info"></i></span>
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
                                    {{ ucfirst(translate(str_replace('_', ' ', $order->delivery_status))) }}
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
                            <a href="javascript:void(0)" class="btn btn-info rounded-pill py-1 px-1" onclick="showTicketModal({{ $order->id }})">
                                Create Ticket
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

    <div class="modal fade" id="ticket_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title strong-600 heading-5">{{ ('Create a Ticket') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-3 pt-2">
                    <form class="" action="{{ route('tickets.user_store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{ ('Issue') }} <span class="text-danger">*</span></label>
                                    @php
                                        $issues = [
                                            'General Query',
                                            'Refund Issue',
                                            'Authenticity Issue',
                                            'Skincare Suggestion',
                                            'Exchange Product',
                                            'Product Query',
                                            'Restock Reminder',
                                        ];
                                    @endphp
                                    <select style="border-radius: 5px !important;" class="form-control mb-3" name="issue" id="issue" required>
                                        <option value="">Select Issue</option>
                                        @foreach ($issues as $issue)
                                            <option value="{{ Str::slug($issue) }}"
                                                @if (old('issue') === Str::slug($issue)) selected @endif>{{ $issue }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{ ('Order Number') }} <span class="text-secondary fs-11">({{ ('Optional') }})</span></label>
                                    <select style="border-radius: 5px !important;" class="form-control" name="related" id="related">
                                        <option value="">{{ ('Select order') }}</option>
                                        @foreach ($orders as $order)
                                            <option value="{{ $order->id }}">#{{ $order->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{ ('Message') }} <span class="text-danger">*</span></label>
                                    <textarea type="text" style="border-radius: 5px !important;" class="form-control mb-3" rows="3" name="details" placeholder="{{ ('Enter message') }}" required></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{ ('Photo') }}</label>
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ ('Browse') }}</div>
                                        </div>
                                        <div style="border-radius: 0 !important;" class="form-control file-amount">{{ ('Choose File') }}</div>
                                        <input type="hidden" name="attachments" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right mt-2">
                            <button type="submit" class="btn btn-primary">{{ ('Create') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        $('#order_details').on('hidden.bs.modal', function () {
            // location.reload();
        });

        function showTicketModal(order_id) {
            $('#related').val(order_id);
            $('#ticket_modal').modal('show');
        }
    </script>

@endsection
