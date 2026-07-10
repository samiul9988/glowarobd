@extends('backend.layouts.app')
@php
    $role = Auth::user()->staff?->role?->getTranslation('name') ?? '';
    $delivery_status = $order->delivery_status;
    $payment_status = $order->payment_status;
    $shipping_methods = \App\Models\ShippingMethod::where('status', 1)->get();
@endphp
@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row w-100">
                                <div class="col-4 d-flex flex-wrap align-items-center">
                                    <div class="d-flex justify-content-start align-items-center">
                                        <a href="{{ route('all_orders.status', $order->delivery_status) }}"
                                            class="mr-2 btn btn-soft-secondary btn-icon btn-circle btn-sm"
                                            title="Go To {{ ucfirst($order->delivery_status) }} Orders">
                                            <i class="las la-long-arrow-alt-left"></i>
                                        </a>
                                        <h1 class="h2 fs-16 mb-0">{{ ('Order Details') }}</h1>
                                    </div>
                                </div>
                                <div class="col-4 d-flex justify-content-center flex-wrap align-items-center">
                                    <span class="h2 fs-16 mb-0">
                                        <strong>{{ group_identity($order->user_id) }}</strong>
                                    </span>
                                </div>
                                <div class="col-4 p-0">
                                    @if (in_array(strtolower($order->delivery_status), ['packaging']))
                                        <div class="d-flex justify-content-end align-items-center">
                                            <h5 id="timer" class="mb-0">00:00:00</h5>
                                            <button id="unlockAndExit"
                                                class="ml-2 btn btn-soft-secondary btn-icon btn-circle btn-sm"
                                                title="Unlock And Exit">
                                                <i class="las la-lock-open"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row gutters-5">
                                        <div class="col-md-7 text-left">
                                            <div
                                                class="h-100 d-flex justify-content-center justify-content-md-start align-items-center">
                                                {!! order_payment_status($order) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-5 ml-auto">
                                            <label for=update_delivery_status"">{{ ('Delivery Status') }}</label>
                                            <select class="form-control aiz-selectpicker"
                                                data-minimum-results-for-search="Infinity" id="update_delivery_status">
                                                <option value="{{ $delivery_status }}" selected>
                                                    {{ ucfirst(translate($delivery_status)) }}</option>
                                                @foreach (statusWiseOrderStatuses($delivery_status) as $status)
                                                    <option value="{{ $status }}">{{ ucfirst(translate($status)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row gutters-5 mt-5">
                                        <div class="col text-center text-md-left">
                                            <address>
                                                <strong class="text-main">
                                                    {{ @json_decode($order->shipping_address)->name }}
                                                    {{-- {!! group_identity($order->user_id, 'image')!!} --}}
                                                </strong>
                                                <br>
                                                <a
                                                    href="tel:{{ @json_decode($order->shipping_address)->phone }}">{{ @json_decode($order->shipping_address)->phone }}</a><br>
                                                @if (@json_decode($order->shipping_address)->email != '')
                                                    Email: {{ @json_decode($order->shipping_address)->email }}<br>
                                                @endif
                                                {{ json_decode($order->shipping_address)->address }},

                                                City: {{ @json_decode($order->shipping_address)->city }},
                                                Area: {{ @json_decode($order->shipping_address)->area }},
                                                @if (@json_decode($order->shipping_address)->postal_code != '')
                                                    Postal Code:
                                                    {{ @json_decode($order->shipping_address)->postal_code }}<br>
                                                @endif
                                                {{ @json_decode($order->shipping_address)->country }}
                                            </address>
                                            @if ($order->manual_payment && is_array(json_decode($order->manual_payment_data, true)))
                                                <br>
                                                <strong
                                                    class="text-main">{{ ('Payment Information') }}</strong><br>
                                                {{ ('Name') }}:
                                                {{ json_decode($order->manual_payment_data)->name }},
                                                {{ ('Amount') }}:
                                                {{ single_price(json_decode($order->manual_payment_data)->amount) }},
                                                {{ ('TRX ID') }}:
                                                {{ json_decode($order->manual_payment_data)->trx_id }}
                                                <br>
                                                <a href="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}"
                                                    target="_blank"><img
                                                        src="{{ uploaded_asset(json_decode($order->manual_payment_data)->photo) }}"
                                                        alt="" height="100"></a>
                                            @endif
                                        </div>
                                        <div class="col ml-auto">
                                            <table class="float-right">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-main text-bold">{{ ('Order #') }}</td>
                                                        <td class="text-right text-info text-bold"> {{ $order->code }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-main text-bold">{{ ('Order Status') }}
                                                        </td>
                                                        <td class="text-right">
                                                            @if ($delivery_status == 'delivered')
                                                                <span
                                                                    class="badge badge-inline badge-success">{{ (ucfirst(str_replace('_', ' ', $delivery_status))) }}</span>
                                                            @else
                                                                <span
                                                                    class="badge badge-inline badge-info">{{ (ucfirst(str_replace('_', ' ', $delivery_status))) }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-main text-bold">{{ ('Order Date') }} </td>
                                                        <td class="text-right">{{ date('d-m-Y h:i A', $order->date) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-main text-bold">
                                                            {{ ('Total amount') }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ single_price(get_order_grand_total($order)) }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-main text-bold">{{ ('Payment method') }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-main text-bold">{{ ('Delivery type') }}
                                                        </td>
                                                        <td class="text-right">
                                                            @if ($order->orderDetails[0]->shipping_type != null && $order->orderDetails[0]->shipping_type == 'home_delivery')
                                                                {{ ('Home Delivery') }}
                                                            @elseif ($order->orderDetails[0]->shipping_type == 'pickup_point')
                                                                @if ($order->orderDetails[0]->pickup_point != null)
                                                                    {{ $order->orderDetails[0]->pickup_point->getTranslation('name') }}
                                                                    ({{ ('Pickup Point') }})
                                                                @else
                                                                    {{ ('Pickup Point') }}
                                                                @endif
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @if ($order->delivery_date != '')
                                                        <tr>
                                                            <td class="text-main text-bold">
                                                                {{ ('Delivery Date') }}</td>
                                                            <td class="text-right">
                                                                {{ date('d-m-Y', $order->delivery_date) }}
                                                                ({{ date('l', $order->delivery_date) }})</td>
                                                        </tr>
                                                    @endif
                                                    @if (@$order->orderDetails[0]->shippingMethod->name != '')
                                                        <tr>
                                                            <td class="text-main text-bold">
                                                                {{ ('Shipping method') }}:</td>
                                                            <td class="text-right">
                                                                {{ @$order->orderDetails[0]->shippingMethod->name }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($order->notes)
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0 h6">{{ ('Order Notes') }}</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    @foreach ($order->notes ?? [] as $key => $note)
                                        <li
                                            class="alert alert-warning d-flex justify-content-between align-items-center font-weight-bold">
                                            <span class="me-3">{{ ucfirst(strip_tags($note['message'])) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h1 class="h2 fs-16 mb-0">{{ ('Product Details') }}</h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" id="barcode-input" class="form-control mb-3"
                                placeholder="{{ ('Scan barcode here...') }}" autocomplete="off" autofocus>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-lg-12 table-responsive">
                                    <table class="table table-bordered invoice-summary">
                                        <thead>
                                            <tr class="bg-trans-dark">
                                                <th class="min-col">#</th>
                                                <th width="10%" class="min-col text-center text-uppercase">
                                                    {{ ('Photo') }}</th>
                                                <th class="text-uppercase">{{ ('Description') }}</th>
                                                <th class="min-col text-center text-uppercase">
                                                    {{ ('Qty') }}</th>
                                                <th class="min-col text-center text-uppercase">
                                                    {{ ('Scanned Qty') }}</th>
                                                <th class="min-col text-center text-uppercase">
                                                    {{ ('Price') }}</th>
                                                <th class="min-col text-center text-uppercase">
                                                    {{ ('Total') }}</th>
                                                <th class="min-col text-center text-uppercase">
                                                    {{ ('Packaging') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $groupedOrderDetails = $order->orderDetails
                                                    ->filter(fn ($detail) => $detail->product)
                                                    ->groupBy('product_id')
                                                    ->map(function ($items) {
                                                        $first = clone $items->first();

                                                        $first->quantity = $items->sum('quantity');
                                                        $first->price = $items->sum('price');

                                                        return $first;
                                                    })
                                                    ->values();
                                            @endphp
                                            @foreach ($groupedOrderDetails as $key => $orderDetail)
                                                <tr class="{{ $orderDetail->product_type === 'gift' ? 'text-success' : '' }}" id="product-row-{{ $orderDetail->product ? $orderDetail->product->id : 'na-' . $key }}"
                                                    data-product-id="{{ $orderDetail->product ? $orderDetail->product->id : '' }}"
                                                    data-barcode="{{ $orderDetail->product ? $orderDetail->product->barcode : '' }}"
                                                    data-quantity="{{ $orderDetail->quantity }}" data-scanned="0">
                                                    <td>{{ $key + 1 }}</td>
                                                    <td class="text-center">
                                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                                            <a href="{{ to_frontend(route('product', $orderDetail->product->slug)) }}"
                                                                target="_blank"><img height="50"
                                                                    src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}" onerror="this.onerror=null; this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"></a>
                                                        @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                                            <a href="{{ route('auction-product', $orderDetail->product->slug) }}"
                                                                target="_blank"><img height="50"
                                                                    src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}" onerror="this.onerror=null; this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"></a>
                                                        @else
                                                            <strong>{{ ('N/A') }}</strong>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                                            <strong><a
                                                                    href="{{ to_frontend(route('product', $orderDetail->product->slug)) }}"
                                                                    target="_blank"
                                                                    class="{{ $orderDetail->product_type === 'gift' ? 'text-success' : 'text-muted' }}">{{ $orderDetail->product->name }}</a></strong>
                                                            <small>{{ $orderDetail->variation }}</small>
                                                            @if (!app()->environment('production'))
                                                                <span class="d-block">
                                                                    {{ $orderDetail->product->barcode }}
                                                                </span>
                                                            @endif
                                                        @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                                            <strong><a
                                                                    href="{{ route('auction-product', $orderDetail->product->slug) }}"
                                                                    target="_blank"
                                                                    class="{{ $orderDetail->product_type === 'gift' ? 'text-success' : 'text-muted' }}">{{ $orderDetail->product->name }}</a></strong>
                                                        @else
                                                            <strong>{{ ('Product Unavailable') }}</strong>
                                                        @endif
                                                    </td>
                                                    <td class="text-center order-qty">
                                                        {{ $orderDetail->quantity }}
                                                    </td>
                                                    <td class="text-center scanned-qty">
                                                        0
                                                    </td>
                                                    <td class="text-center">
                                                        {{ single_price($orderDetail->price / $orderDetail->quantity) }}</td>
                                                    <td class="text-center">{{ single_price($orderDetail->price) }}</td>
                                                    <td class="text-center packaging-status">
                                                        <i class="las la-times-circle text-danger fs-20"></i>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="6" class="text-right">
                                                    <strong>{{ ('Total Scanned') }}:</strong></td>
                                                <td class="text-center" id="total-scanned">0</td>
                                                <td class="text-center" id="total-packaged">0</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="text-right" id="complete-packaging">
                                {{--  --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fully-scanned {
            background-color: rgba(40, 167, 69, 0.08) !important;
        }

        .partially-scanned {
            background-color: rgba(23, 162, 184, 0.08) !important;
        }

        .packaging-status {
            cursor: pointer;
        }
    </style>
@endsection

@section('modal')
    {{-- Choose Shipping Method Modal Start --}}
    <div id="choose-shipping-method-modal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ ('Shipping Method') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body" style="height: 250px !important;">
                    <div class="form-group mb-3">
                        <label for="shipping_method">Select Shipping Method</label>
                        <select class="form-control aiz-selectpicker" name="shipping_method" id="shipping_method"
                            required>
                            <option value="">{{ ('Select Shipping Method') }}</option>
                            @foreach ($shipping_methods as $method)
                                <option value="{{ $method->id }}" {{ $method->id == 1 ? 'selected' : '' }}>{{ ($method->name) }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger" id="shipping_method_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-group mb-3 text-right">
                        <button type="button" onclick="processToShip()"
                            class="btn btn-primary">{{ ('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Choose Shipping Method Modal End --}}

    {{-- Choose Hold Reason Modal Start --}}
    <div id="choose-hold-reason-modal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ ('Hold Status') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body" style="height: 250px !important;">
                    <div class="form-group mb-3">
                        <label for="hold_status">{{ ('Select Hold Status') }}</label>
                        <select class="form-control aiz-selectpicker" name="hold_status" id="hold_status" required>
                            <option value="">{{ ('Select Hold Status') }}</option>
                            <option value="out_of_stock">{{ ('Out Of Stock') }}</option>
                            <option value="shipment_failed">{{ ('Shipment Failed') }}</option>
                        </select>
                        <div class="text-danger" id="hold_status_error"></div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="note">{{ ('Note') }}</label>
                        <div class="input-group">
                            <textarea type="text" class="form-control" name="hold_note" id="hold_note" placeholder="{{ ('Note') }}" rows="3" required>{{ old('hold_note') }}</textarea>
                        </div>
                        <div class="text-danger" style="display: none" id="hold_note_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-group mb-3 text-right">
                        <button type="button" onclick="changeDeliveryStatus()"
                            class="btn btn-primary">{{ ('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Choose Hold Reason Modal End --}}
@endsection

@section('script')
    <script>
        // Initialize products array
        const orderId = '{{ $order->id }}';
        const orderDetails = @json($groupedOrderDetails);
        let products = orderDetails.flatMap(orderDetail => {
            if (!orderDetail.product) return [];
            const product = orderDetail.product;
            return [
                {
                    id: product.id,
                    name: product.name,
                    barcode: product.barcode,
                    qty: parseInt(orderDetail.quantity),
                    scan_count: 0,
                    packaged: false
                }
            ];
        });

        // Handle barcode scanning
        function handleBarcodeScan(key, searchBy = 'barcode') {
            $('#barcode-input').val('').focus(); // Clear input after scan
            let product = null;
            if(searchBy === 'barcode'){
                product = products.find(p => p.barcode === key);
            }else{
                product = products.find(p => p.id === key);
            }

            if (!product) {
                playSound('error');
                showAlert('error', 'Product not found in this order!');
                return;
            }

            if (product.scan_count >= product.qty) {
                playSound('warning');
                showAlert('error', `Product already scanned! (${product.qty}/${product.qty})`);
                return;
            }

            product.scan_count++;
            updateProductRow(product);
            // Check if all products are fully scanned
            if (!checkOrderCompletion()) {
                playSound('success');
                showAlert('success', `Scanned ${product.name} (${product.scan_count}/${product.qty})`);
            }
        }

        // Update product row in table
        function updateProductRow(product) {
            const row = $(`#product-row-${product.id}`);
            const scannedCell = row.find('.scanned-qty');
            const packagingCell = row.find('.packaging-status');

            // Update scanned quantity
            scannedCell.text(product.scan_count);
            row.attr('data-scanned', product.scan_count);

            // Update packaging status
            if (product.scan_count === product.qty) {
                product.packaged = true;
                packagingCell.html('<i class="las la-check-circle text-success fs-20"></i>');
                if (row.hasClass('partially-scanned')) {
                    row.removeClass('partially-scanned');
                }
                row.addClass('fully-scanned');
            } else if (product.scan_count > 0) {
                packagingCell.html('<i class="las la-sync-alt text-primary fs-20"></i>');
                row.addClass('partially-scanned');
            }

            // Update totals
            updateTotals();
        }

        // Update footer totals
        function updateTotals() {
            const totalScanned = products.reduce((sum, p) => sum + p.scan_count, 0);
            const totalPackaged = products.filter(p => p.packaged).length;

            $('#total-scanned').text(totalScanned);
            $('#total-packaged').text(totalPackaged);
        }

        // Check if order is complete
        function checkOrderCompletion() {
            const allScanned = products.every(p => p.scan_count >= p.qty);
            if (allScanned) {
                playSound('complete');
                showAlert('success', 'All items have been scanned!');
                $('#complete-packaging').html(`
                    <button type="button" id="print-label-btn" class="btn btn-soft-success btn-styled" title="{{ ('Print Label') }}">{{ ('Print Label') }}</button>
                    <button type="button" class="btn btn-soft-primary btn-styled" id="courier-entry-btn" title="{{ ('Courier Entry') }}">{{ ('Courier Entry') }}</button>
                `);
                return 1;
            }
            return 0;
        }

        // Play sound feedback
        function playSound(type) {
            const sounds = {
                'success': '{{ static_asset('assets/audio/success.mp3') }}',
                'error': '{{ static_asset('assets/audio/error.mp3') }}',
                'warning': '{{ static_asset('assets/audio/error.mp3') }}',
                'complete': '{{ static_asset('assets/audio/complete.mp3') }}'
            };
            if (sounds[type]) {
                new Audio(sounds[type]).play();
            }
        }

        async function checkExpireDate(barcode) {
            let url = `{{ route('orders.check_expire_date') }}`;
            url += '?code=' + encodeURIComponent(barcode);
            url += '&order_id='+ encodeURIComponent(orderId);
            await $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    handleResponse(response, barcode);
                },
                error: function(xhr, status, error) {
                    console.error('Error checking expire date:', error);
                    showAlert('error', '{{ ('An unexpected error occurred') }}');
                }
            });
        }

        function handleResponse(response, barcode) {
            if (response.success) {
                handleBarcodeScan(response.product_id, 'id');
            } else {
                if (response.status === 'expiring_soon'){
                    // Show alert for expiring soon with force use option
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: "btn btn-success btn-sm",
                            cancelButton: "btn btn-danger btn-sm mr-2"
                        },
                        buttonsStyling: false
                    });
                    swalWithBootstrapButtons.fire({
                        text: response.message,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Okay",
                        cancelButtonText: "No, Use it!",
                        confirmButtonColor: "#28a745",
                        cancelButtonColor: "#d33",
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#barcode-input').val('').focus();
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // Handle force use
                            swalWithBootstrapButtons.fire({
                                title: "Force Use",
                                text: "You have chosen to use this product despite it expiring soon.",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonText: "Yes, Sure",
                                cancelButtonText: "No, Cancel!",
                                confirmButtonColor: "#28a745",
                                cancelButtonColor: "#d33",
                                reverseButtons: true
                            }).then(async (result) => {
                                if (result.isConfirmed) {
                                    await forcelyMarkAsPackaged(response.order_item_id, barcode, response.product_id);
                                }
                            });
                        }
                    });
                } else if (response.status === 'expired'){
                    showAlert('error', response.message);
                    $('#barcode-input').val('').focus();
                } else if (response.status === 'not_found'){
                    handleBarcodeScan(barcode, 'barcode');
                } else if (response.status === 'not_in_order'){
                    showAlert('error', response.message);
                    $('#barcode-input').val('').focus();
                } else if (response.status === 'already_fulfilled') {
                    handleBarcodeScan(response.product_id, 'id');
                } else {
                    showAlert('error', response.message);
                    $('#barcode-input').val('').focus();
                }
            }
        }

        async function forcelyMarkAsPackaged(order_item_id, barcode, product_id)
        {
            await $.ajax({
                url: `{{ route('orders.forcely_mark_as_packaged') }}`,
                type: 'POST',
                data: {
                    barcode: barcode,
                    order_item_id: order_item_id,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    handleResponse(response, barcode);
                },
                error: function(xhr, status, error) {
                    console.error('Error marking as packaged:', error);
                }
            });
        }

        // Initialize scanning functionality
        $(document).ready(function() {
            // Barcode scanner input
            $('#barcode-input').on('keypress', async function(e) {
                if (e.which === 13) {
                    @if(get_setting('enable_product_expire_date') == 1)
                        await checkExpireDate($(this).val().trim());
                    @else
                        handleBarcodeScan($(this).val().trim());
                    @endif
                }
            });
        });

        $(document).on('click', '#print-label-btn', function() {
            let url = `{{ route('invoice.sticker_label_print') }}`;
            url += '?id={{ $order->id }}';

            $('.print-pdf-iframe').remove(); // Remove any existing iframes
            $.get(url, function(data) {
                // Convert base64 to a Blob
                const byteChars = atob(data.pdf);
                const byteNums = new Array(byteChars.length);
                for (let i = 0; i < byteChars.length; i++) {
                    byteNums[i] = byteChars.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNums);
                const blob = new Blob([byteArray], {
                    type: 'application/pdf'
                });
                const blobUrl = URL.createObjectURL(blob);

                // Create a hidden iframe
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = blobUrl;
                iframe.className = 'print-pdf-iframe';
                document.body.appendChild(iframe);

                // Print when the PDF loads
                iframe.onload = function() {
                    iframe.contentWindow.print();
                };
            });
        });

        $(document).on('click', '#courier-entry-btn', function() {
            $('#choose-shipping-method-modal').modal('show');
        });

        function processToShip() {
            if ($('#shipping_method').val() == '' || $('#shipping_method').val() == null) {
                $('#shipping_method_error').text('Please select a shipping method');
                return false;
            } else {
                $('#shipping_method_error').text('');
            }
            $('#choose-shipping-method-modal').modal('hide');
            let url = `{{ route('orders.shipping.process.save') }}`;
            let orderIDs = '{{ $order->id }}';
            let status = 'picked_up';
            // let shipping_method = '1'; // 1 => 'pathao'
            let shipping_method = $('#shipping_method').val();
            let barcode = '';
            let order_status = '{{ $order->delivery_status }}';
            let redirect_to = `{{ route('all_orders.status', ':status') }}`.replace(':status', order_status);

            $('#shipping_method').val('');
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    orderIDs: orderIDs,
                    status: status,
                    shipping_method: shipping_method,
                    barcode: barcode,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message, redirect_to);
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    var errorResponse = xhr.responseJSON;
                    if (errorResponse && errorResponse.message && errorResponse.hold == 1) {
                        showAlert('error', errorResponse.message, redirect_to);
                    } else if (errorResponse && errorResponse.message) {
                        showAlert('error', errorResponse.message);
                    } else {
                        showAlert('error', 'An unexpected error occurred');
                    }
                }
            });
        }
    </script>
    <script type="text/javascript">
        let deliveryStatus = '{{ $order->delivery_status }}'; // Get the delivery status
        let callDuration = {{ old('duration', 0) }}; // Initialize call duration variable
        let checkInterval;
        let timerInterval;
        let callDurationInterval;
        let remainingTime = {{ $order->unlockIn() }};

        $('#unlockAndExit').on('click', function() {
            $.ajax({
                url: '{{ route('orders.unlock', ':id') }}'.replace(':id', {{ $order->id }}),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect_url;
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function() {
                    showAlert('error', 'Something went wrong');
                }
            });
        });

        // Function to format seconds into HH:MM:SS
        function formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            return [
                hours.toString().padStart(2, '0'),
                minutes.toString().padStart(2, '0'),
                secs.toString().padStart(2, '0')
            ].join(':');
        }

        // Function to update the timer display
        function updateTimer() {
            $('#timer').text(formatTime(remainingTime));

            if (remainingTime <= 60) {
                $('#timer').css('color', 'red');
            } else {
                $('#timer').css('color', 'black');
            }
            if (remainingTime <= 1) {
                extendLock(); // Call extendLock when 1 second remains
            } else {
                remainingTime--;
            }
        }

        // Function to extend the lock
        function extendLock() {
            $.ajax({
                url: '{{ route('orders.extend-lock', ':id') }}'.replace(':id', {{ $order->id }}),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        remainingTime = response.unlock_in;
                        clearInterval(timerInterval);
                        timerInterval = setInterval(updateTimer, 1000);
                    } else {
                        window.location.reload();
                    }
                },
                error: function() {
                    AIZ.plugins.notify('danger', 'Failed to extend lock.');
                    window.location.reload();
                }
            });
        }

        if (deliveryStatus == 'packaging') {
            timerInterval = setInterval(updateTimer, 1000);
            updateTimer();
        }

        $('#update_delivery_status').on('change', function() {
            var status = $('#update_delivery_status').val();
            if (status == 'hold') {
                $('#choose-hold-reason-modal').modal('show');
            } else {
                changeDeliveryStatus();
            }
        });

        function changeDeliveryStatus() {
            var order_id = {{ $order->id }};
            var old_status = '{{ $order->delivery_status }}';
            var status = $('#update_delivery_status').val();
            var url = `{{ route('all_orders.status', ':status') }}`.replace(':status', old_status);
            var hold_status = $('#hold_status').val();
            var hold_note = $('#hold_note').val();
            if (status == 'hold' && hold_status == '') {
                $('#hold_status_error').text('{{ ('Please select a hold status') }}');
                return false;
            } else {
                $('#hold_status_error').text('');
            }
            if (status == 'hold' && hold_note == '') {
                $('#hold_note_error').text('{{ ('Note is required') }}');
                return false;
            } else {
                $('#hold_note_error').text('');
            }
            $('#choose-hold-reason-modal').modal('hide');
            $('#hold_status').val('');
            $.post('{{ route('orders.update_delivery_status') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                status: status,
                hold_status: hold_status,
                hold_note: hold_note
            }, function(data) {
                if (data === 403) {
                    showAlert('error', "You can not edit this order", url);
                } else {
                    showAlert('success', 'Delivery status has been updated', url);
                }
            });
        }
    </script>
@endsection
