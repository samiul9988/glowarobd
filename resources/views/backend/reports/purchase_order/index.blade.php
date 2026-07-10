@extends('backend.layouts.app')

@section('content')

<div class="card">
    <form class="" action="" id="sort_orders" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('All Purchase Order') }}</h5>
            </div>

            {{--<div class="dropdown mb-2 mb-md-0">
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
            </div>--}}


            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="user_id" name="user_id" onchange="sort_products()">
                    <option value="">{{ ('All Sellers') }}</option>
                        @foreach (App\Models\User::where('user_type', '=', 'admin')->orWhere('user_type', '=', 'seller')->get() as $key => $seller)
                        <option value="{{ $seller->id }}"  @if ($seller->id == $seller_id) selected @endif>{{ $seller->name }}</option>
                        @endforeach
                </select>
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

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control" value="{{ $date }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>
            </div>

            <div class="col-lg-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ ('Type Order code') }}">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                </div>
            </div>
        </div>

        <div class="card-body">

            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{ ('#') }}</th>
                        <th data-breakpoints="md">{{ ('Date') }}</th>
                        <th data-breakpoints="md">{{ ('Product Name') }}</th>
                        <th data-breakpoints="md">{{ ('Variant') }}</th>
                        <th data-breakpoints="md">{{ ('Qty') }}</th>
                        <th data-breakpoints="md">{{ ('Unit Price') }}</th>
                        <th data-breakpoints="md">{{ ('Total price') }}</th>
                        <th data-breakpoints="md">{{ ('Seller Name') }}</th>
                        <th data-breakpoints="md">{{ ('PO Number') }}</th>
                        <th data-breakpoints="md">{{ ('Supplier Name') }}</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase_order_reports as $key => $item)
                    <tr>
                        <td>{{$key + 1}}</td>
                        <td>{{ date('d-m-Y', $item->purchase_date )}}</td>
                        <td width="150px">{{$item->product->name}}</td>
                        <td>
                            {{@$item->product_stock->variant}}
                        </td>
                        <td>{{$item->qty}}</td>
                        <td class="text-center">{{single_price($item->price)}}</td>
                        <td class="text-center">{{single_price($item->total_price)}}</td>
                        <td>{{@$item->sellername->name}}</td>
                        <td>{{$item->po_number}}</td>
                        <td>{{@$item->supplier->name}}</td>


                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $purchase_order_reports->appends(request()->input())->links() }}
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
