@extends('backend.layouts.app')

@section('content')
<div class="alert alert-info text-align-center">
    <i class="las la-info-circle fs-14" style="animation: heartbeat 1.5s infinite;"></i>
    Only orders with delivery status <strong>Picked Up</strong>, <strong>On The Way</strong> or <strong>Delivered</strong> can be returned.
</div>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ ('Return Order')}}</h5>
</div>
<div class="">
    <form class="form form-horizontal mar-top" action="{{ route('return-orders.store') }}" method="POST" enctype="multipart/form-data" id="returnOrderForm">
        <div class="row gutters-5">
            <div class="col-lg-12">
                @csrf
                <div class="row gutters-5 mb-3">
                    <div class="col-md-7">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0 h6">{{ ('Order Information')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="control-label" for="code">{{ ('Order Number')}}</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="code" id="order_number" placeholder="{{ ('Type order number or scan barcode')}}" value="{{ old('code') }}" autocomplete="off" style="border-radius: 5px 0 0 5px;">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-primary" type="button" id="checkOrderBtn" style="border-radius: 0 5px 5px 0;">
                                                        <i class="la la-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <span class="text-danger" id="order_number_error"></span>
                                        </div>
                                        <input type="hidden" name="order_id" id="order_id" value="">
                                        <input type="hidden" name="reason" id="reason" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0 h6">{{ ('Customer Information')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row" id="customer_info">
                                    <div class="col-12">
                                        <div class="text-center my-2">
                                            No Customer Information
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info text-align-center">
                    <i class="las la-info-circle fs-14" style="animation: heartbeat 1.5s infinite;"></i>
                    For a <strong>partial return</strong>, we adjust the quantity of items and the order total. <strong>Any coupon or promotional discounts</strong> from the original order <strong>will not be deducted</strong>.
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Adjust Order Items')}}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped mobile_no_border" style="margin-bottom: 5px;margin-top: 15px;">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    {{-- <th>Variant</th> --}}
                                    <th>Quantity</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="productList">

                            </tbody>
                        </table>
                        <div id="infoMessage" class="alert alert-warning mt-2" style="display: none;">
                            <i class="las la-info-circle fs-14" style="animation: heartbeat 1.5s infinite;"></i>
                            Remove items you don't want to return by clicking the trash icon in the action column. But at least one item is required to proceed.
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-12">
                <div class="btn-toolbar float-right mb-3" role="toolbar">
                    <div class="btn-group mr-2" role="group">
                        <a href="#" class="btn btn-primary">{{ ('Cancel') }}</a>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" id="saveOrderBtn" class="btn btn-success">{{ ('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@if(old('code') || old('products'))
    {{-- @dd(old('code'), old('products')); --}}
@endif
@endsection

@section('script')

<script type="text/javascript">
    let orderDetails = [];
    $(document).ready(function(){
        $('#order_number').focus();
        renderProduct({{ json_encode(old('products', [])) }});

        $('#order_number').on('keydown', function(e) {
            // Check if the pressed key is Enter or Tab
            if (e.which === 13 || e.which === 9) {
                e.preventDefault();
                $('#checkOrderBtn').click();
            }
        });

        $('#checkOrderBtn').on('click', function() {
            const code = $('#order_number').val().trim();
            if (code !== '') {
                getOrderInfo(code);
            } else {
                $('#order_number').focus();
                $('#order_number_error').text('Order number is required');
            }
        });

        function getOrderInfo(order_number){
            $('#checkOrderBtn').prop('disabled', true).html('<i class="la la-spinner la-spin"></i>');
            $.ajax({
                type:"GET",
                url:'{{ route('getOrderInfo') }}',
                data:{
                    code: order_number
                },
                success: function(response) {
                    $('#checkOrderBtn').prop('disabled', false).html('<i class="la la-search"></i>');
                    if(response.success) {
                        console.log('Order Info:', response.data);
                        $('#order_id').val(response.data.order_id);
                        orderDetails = response.data.products;
                        renderCustomerInfo(response.data.customer);
                        renderProduct(response.data.products);
                        $('#order_number_error').text('');
                        $('#order_number').focus(false);
                    }else{
                        AIZ.plugins.notify('danger', response.message);
                        $('#order_number').focus();
                    }
               },
                error: function(xhr, status, err) {
                    $('#checkOrderBtn').prop('disabled', false).html('<i class="la la-search"></i>');
                    AIZ.plugins.notify('danger', xhr.responseJSON.message || 'Something went wrong');
                    $('#order_number').focus();
                    console.log(err);
                }
            });
        }

        function renderCustomerInfo(customer = null) {
            let html = ``;
            if(customer){
                let infoDivs = '';
                let addressDivs = '';
                if(customer.name){
                    infoDivs += `<div><strong>Name:</strong> ${customer.name}</div>`;
                }
                if(customer.email){
                    infoDivs += `<div><strong>Email:</strong> ${customer.email}</div>`;
                }
                if(customer.phone){
                    infoDivs += `<div><strong>Phone:</strong> <a href="tel:${customer.phone.replaceAll(' ', '')}">${customer.phone.replaceAll(' ', '')}</a></div>`;
                }
                addressDivs += `<div><strong>Address:</strong> ${customer.address}</div>`;
                addressDivs += `<div>`;
                if(customer.area){
                    addressDivs += `${customer.area}, `;
                }
                if(customer.city){
                    addressDivs += `${customer.city}, `;
                }
                if(customer.state){
                    addressDivs += `${customer.state}, `;
                }
                if(customer.country){
                    addressDivs += `${customer.country}`;
                }
                addressDivs += `</div>`;

                html += `<div class="col-md-6">
                            ${infoDivs}
                        </div>`;
                html += `<div class="col-md-6">
                            ${addressDivs}
                        </div>`;
            }else{
                html = `<div class="col-12">
                            <div class="text-center my-2">
                                No Customer Information
                            </div>
                        </div>`;
            }
            $('#customer_info').html(html);
        }

        function renderProduct(products = []) {
            console.log('Products:', products);
            let html = ``;
            products.forEach((product, index) => {
                if(product.quantity <= 0) {
                    return; // Skip this iteration if quantity is 0 or less
                }
                html += `<tr class="remove-${index+1}">
                    <td>
                        <input type="hidden" name="products[${index}][item_id]" value="${product.item_id}">
                        <input type="hidden" name="products[${index}][id]" value="${product.id}">
                        ${product.name}
                    </td>
                    <td>
                        <input type="text"
                            class="form-control order-product-quantity"
                            name="products[${index}][quantity]"
                            value="${product.quantity}"
                            data-quantity="${product.quantity}"
                            min="1"
                            max="${product.quantity}"
                            step="1">
                        <span class="text-danger qty-error"></span>
                    </td>
                    <td class="text-center">
                        <a href="javascript:;"
                        class="btn btn-xs btn-danger remove_item"
                        data-removeclass="remove-${index+1}">
                            <i class="las la-trash"></i>
                        </a>
                    </td>
                </tr>`;
            });
            $('.productList').html(html);
            if(products.length > 0){
                $('#infoMessage').show();
            }else{
                $('#infoMessage').hide();
                $('#order_number').focus();
            }
        }

        $(document).on('input', '.order-product-quantity', function(){
            const maxQty = parseInt($(this).data('quantity'));
            let currentQty = parseInt($(this).val());
            let qtyErrorElem = $(this).siblings('.qty-error');
            qtyErrorElem.text('');
            if(currentQty > maxQty){
                qtyErrorElem.text('Quantity cannot exceed '+maxQty);
                $(this).val(maxQty);
            }else if(currentQty < 1){
                qtyErrorElem.text('Quantity must be at least 1');
                $(this).val(1);
            }else if(isNaN(currentQty)){
                qtyErrorElem.text('Invalid quantity');
                $(this).val('');
            }
        });
    });



    $(document).on('click', '.remove_item', function(){
        $('.'+$(this).attr('data-removeclass')).remove();
    });

    $('#saveOrderBtn').on('click', async function(){
        const options = {
            title: 'Why are you returning this order?',
            type: 'return'
        };
        const reason = await takeReason(options);
        if (!reason) {
            e.preventDefault();
            AIZ.plugins.notify('danger', '{{ ('Reason is required') }}');
            return;
        }
        $('#reason').val(reason);
        $('#returnOrderForm').submit();
    });

    $('form').bind('submit', function (e) {
        $("#saveOrderBtn").prop('disabled', true);
        let valid = true;
        if($('#order_id').val() == ''){
            valid = false;
            $('#order_number_error').text('Order number is required');
            AIZ.plugins.notify('danger', 'Order number is required');
        }
        if($('.productList tr').length <= 0){
            valid = false;
            AIZ.plugins.notify('danger', 'No product found');
        }

        $('.order-product-quantity').each(function(){
            const qty = parseInt($(this).data('quantity') || 0);
            const current_qty = parseInt($(this).val() || 0)
            $(this).siblings('.qty-error').text('');
            if(current_qty == ''){
                valid = false;
                $(this).siblings('.qty-error').text('Quantity is required');
            }else if(current_qty <= 0){
                valid = false;
                $(this).siblings('.qty-error').text('Minimum quantity is 1');
            }else if(current_qty > qty){
                valid = false;
                $(this).siblings('.qty-error').text('Quantity cannot exceed '+qty);
            }else if(isNaN(current_qty)){
                valid = false;
                $(this).siblings('.qty-error').text('Invalid quantity');
            }
        });

        if (!valid) {
            e.preventDefault();
            $("#saveOrderBtn").prop('disabled', false);
        }
    });

</script>

@endsection
