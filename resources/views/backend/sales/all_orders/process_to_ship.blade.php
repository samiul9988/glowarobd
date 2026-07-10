@extends('backend.layouts.app')

@section('content')
<form action="{{ route('orders.shipping.process.save') }}" id="scanbarcode_form" method="POST">
    @csrf
    <input type="hidden" name="orderIDs" id="orderIDs">
    <input type="hidden" name="status" value="picked_up">
    <div class="row">
        <div class="col-6 mx-auto">
            <div class="form-group">
                <label for="shipping_method">Select Shipping Method</label>
                <select class="form-control aiz-selectpicker" name="shipping_method" id="shipping_method" required>
                    <option value="">{{ ('Select Shipping Method')}}</option>
                    @foreach($shipping_methods as $method)
                        <option value="{{ $method->id }}" {{ $loop->index === 0 ? 'selected' : ''}}>{{ ($method->name)}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="barcode">Scan the barcode</label>
                <input type="text" name="barcode" class="form-control" id="barcode" placeholder="Scan the barcode" />
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ ('Process Orders For Shipping') }}</h5>
            </div>
        </div>
        <div class="card-body">
            @if(session()->has('failedOrders'))
                <div class="message card text-white bg-danger mb-3" id="messageCard1">
                    <div class="card-header flex justify-content-between">
                        <h4>Failed Messages</h4>
                        <button class="close-button" data-card="messageCard1">Close</button>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Failed To Process Orders</h5>
                        <ul>
                            @foreach(session()->get('failedOrders') as $failtedItem)
                                <li><strong>{{ $failtedItem['reason'] }}</strong></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            @if(session()->has('success'))
                <div class="message card text-white bg-success mb-3" id="messageCard2">
                    <div class="card-header flex justify-content-between">
                        <h4>Success</h4>
                        <button class="close-button" data-card="messageCard2">Close</button>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">All orders processed successfully</h5>
                    </div>
                </div>
            @endif
            <h6 class="mb-3">Total Processed: <strong id="totalCount">0</strong></h6>
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Order Code</th>
                        <th data-breakpoints="md">Shipping Info</th>
                        <th data-breakpoints="md">Amount</th>
                        <th data-breakpoints="md">Payment Method</th>
                        <th data-breakpoints="md">Payment Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="orderlisttoprocess">

                </tbody>
            </table>
            <button type="submit" id="processform" class="btn btn-sm btn-soft-primary mt-4 text-center">Process To Ship</button>
        </div>
    </div>
</form>
@endsection

@section('script')
    <script type="text/javascript">

        let ordersIDs = [];
        var count = 0;
        let loading = false;

        $('#barcode').keypress(function(event){
            event.preventDefault();

            var keycode = (event.keyCode ? event.keyCode : event.which);
            let orderID =  $(this).val();

            if(keycode == '13' && !loading){
                if (loading) return; // Prevent multiple simultaneous requests

                loading = true;
                // All variables are non-empty and non-null
                let url = "{{ route('orders.lookup', ':id') }}".replace(':id', orderID);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: url,
                    type: 'GET',
                    cache: false,
                    success: function (response) {
                        loading = false;
                        if(response.status) {
                            let data = response.order;
                            let exist = ordersIDs.includes(data.id) ? true: false;
                            if(exist){
                                AIZ.plugins.notify('danger', `Order Code ${data.code} Already Scanned`);
                                return false;
                            }else{
                                count++;
                                ordersIDs.push(data.id);
                                $("#orderIDs").val(ordersIDs);
                                $("#orderlisttoprocess").append(`<tr data-order-id="${data.id}">
                                    <td class="sl-number">${count}</td>
                                    <td>
                                        <a href="${data.url}" target="_blank">${data.code}</a>
                                    </td>
                                    <td>
                                        ${data.shipping_address?.name} <br> ${data.shipping_address?.phone}
                                    </td>
                                    <td>${data.grand_total}</td>
                                    <td>${data.payment_type}</td>
                                    <td>${data.payment_status}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-soft-danger btn-icon btn-circle btn-sm remove-order" data-order-id="${data.id}" title="Remove">
                                            <i class="las la-trash"></i>
                                        </button>
                                    </td>
                                </tr>`);
                            }
                            $("#barcode").val('');
                            $('#totalCount').text(count);
                        }else{
                            AIZ.plugins.notify('danger', 'Something Went Wrong! Order Not Found');
                        }
                    }
                });
            }
        });

        // Remove order from the list
        $(document).on('click', '.remove-order', function() {

            let orderId = $(this).data('order-id');
            let row = $(this).closest('tr');

            // Confirmation
            Swal.fire({
                title: 'Are You Sure?',
                text: 'You want to remove this order from the list?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Remove It!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Remove from ordersIDs array
                    ordersIDs = ordersIDs.filter(id => id !== orderId);
                    $("#orderIDs").val(ordersIDs);

                    // Remove the row
                    row.remove();

                    // Update count
                    count--;
                    $('#totalCount').text(count);

                    // Re-number the serial numbers
                    $('#orderlisttoprocess tr').each(function(index) {
                        $(this).find('.sl-number').text(index + 1);
                    });

                    AIZ.plugins.notify('success', 'Order removed from list');
                }
            });
        });

        $('#processform').click(function(event){
            event.preventDefault();

            let orderIDs =  $('#orderIDs').val();

            if (orderIDs.length == 0) {
                AIZ.plugins.notify('danger', 'No orders added for shipping');
                return false;
            }

            let shipMethod =  $('#shipping_method').val();

            if(!shipMethod) {
                AIZ.plugins.notify('danger', 'Please select a shipping method');
                return false;
            }
            $("#scanbarcode_form").submit();
        });

        document.addEventListener("DOMContentLoaded", function() {
            var messageCards = document.querySelectorAll(".message");
            var closeButtons = document.querySelectorAll(".close-button");

            function closeMessageCard(card) {
                if (card.style.display !== "none") {
                    card.style.display = "none";
                }
            }

            messageCards.forEach(function(card) {
                var timeout = 10000; // Default timeout of 10 seconds

                if (card.id === "messageCard1") {
                    timeout = 20000; // Special timeout of 20 seconds for messageCard1
                }

                setTimeout(function() {
                    closeMessageCard(card);
                }, timeout);
            });

            closeButtons.forEach(function(button) {
                button.addEventListener("click", function() {
                    var cardId = button.getAttribute("data-card");
                    var card = document.getElementById(cardId);
                    closeMessageCard(card);
                });
            });
        });
    </script>
@endsection
