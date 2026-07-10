@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <h5 class="mb-0 h6">Return Product to Supplier</h5>
    </div>
    <div class="">
        <form class="form form-horizontal mar-top" action="{{ route('stock-adjust.return_supplier.store') }}" method="POST"
            enctype="multipart/form-data" id="choice_form">
            <div class="row gutters-5">
                <div class="col-lg-12">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Return Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 control-label" for="date">Date</label>
                                <div class="col-md-8">
                                    <input type="date" class="form-control" name="date" id="date"
                                        placeholder="Select Date" data-time-picker="true"
                                        data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off"
                                        value="{{ old('date', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 control-label" for="supplier">Select Supplier <span class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    <select class="form-control aiz-selectpicker" name="supplier" id="supplier" data-live-search="true">
                                        <option value="">Select Supplier</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">
                                                {{ $supplier->name . ' (' . $supplier->phone . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" id="supplier_error"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 control-label" for="note">Note <span class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    <textarea class="form-control" name="note" id="note" rows="4"
                                        placeholder="Please specify any reason for the return..." value="{{ old('note') }}" required></textarea>
                                    <span class="text-danger" id="note_error"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">Product Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 row gutters-5">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_id">Select Product <span class="text-danger">*</span></label>
                                        <select class="form-control aiz-selectpicker" name="product_id" id="product_id"
                                            data-live-search="true">
                                            <option value="">Select Product</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    data-price="{{ $product->lastPurchaseOrderItem?->price ?: 0 }}"
                                                    data-barcode="{{ $product->barcode ?? '' }}">
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="barcodeInput">Or Scan Barcode</label>
                                        <input type="text" id="barcodeInput" class="form-control"
                                            placeholder="Click here and scan barcode" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-6 varient_data" style="display: none;">
                                    <div class="form-group">
                                        <label for="varient_id">Select Variant</label>
                                        <select class="form-control aiz-selectpicker" name="varient_id" id="varient_id"
                                            data-live-search="true">
                                            <option value="">Select Variant</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="quantity">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="quantity" name="quantity"
                                            placeholder="Quantity" step="1" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="purchase_price">Last Purchase Price <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="purchase_price"
                                            name="purchase_price" placeholder="Last Purchase Price" step="1"
                                            min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                                    <div class="btn-group mr-2" role="group" aria-label="First group">
                                        <button type="button" name="button"
                                            class="btn btn-success addproduct">{{ 'Add' }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ 'Stock Adjust Items' }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="errors">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <table class="table table-bordered table-striped mobile_no_border"
                                style="margin-bottom: 5px;margin-top: 15px;">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Variant</th>
                                        <th>Quantity</th>
                                        <th>Purchase Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="appendstockdata">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <div class="col-12">
                    <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">
                        <div class="btn-group mr-2" role="group" aria-label="Third group">
                            <a href="{{ route('stock-adjust.return_supplier.index') }}" class="btn btn-primary">{{ 'Cancel' }}</a>
                        </div>
                        <div class="btn-group" role="group" aria-label="Second group">
                            <button type="submit" name="button" value="publish"
                                class="btn btn-success">{{ 'Save' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#barcodeInput').on('click', function() {
                $(this).select().focus();
            });

            const barcodeMap = {};
            const purchasePriceMap = {};
            $('#product_id option').each(function() {
                const barcode = $(this).data('barcode');
                const purchasePrice = $(this).data('price');

                if (barcode) {
                    barcodeMap[barcode] = $(this).val();
                }
                if (purchasePrice) {
                    purchasePriceMap[$(this).val()] = purchasePrice;
                }
            });

            $('#product_id').on('change', function() {
                const selectedProductId = $(this).val();
                if (purchasePriceMap[selectedProductId]) {
                    $('#purchase_price').val(purchasePriceMap[selectedProductId]);
                } else {
                    $('#purchase_price').val(0);
                }
            });

            $('#barcodeInput').on('keydown', function(e) {
                // Check if the pressed key is Enter or Tab
                if (e.which === 13 || e.which === 9) {
                    e.preventDefault();
                    const barcode = $(this).val().trim();
                    if (barcode && barcodeMap[barcode]) {
                        $('#product_id').val(barcodeMap[barcode]);
                        AIZ.plugins.bootstrapSelect('refresh');
                        getproductvarient(barcodeMap[barcode]);
                    } else if (barcode) {
                        $('#product_id').val('');
                        AIZ.plugins.bootstrapSelect('refresh');
                        alert('Product not found!');
                    }

                    $(this).val('').focus();
                }
            });
        });

        $(document).on('click', '.addproduct', function() {
            let product_id = $('#product_id').val();
            if (product_id == '') {
                AIZ.plugins.notify('warning', 'Please select a product.');
                $('#product_id').focus();
                return;
            }
            let quantity = parseInt($('#quantity').val() || 0);
            if (isNaN(quantity) || quantity <= 0) {
                AIZ.plugins.notify('warning', 'Please enter a valid quantity.');
                $('#quantity').focus();
                return;
            }
            let purchase_price = parseFloat($('#purchase_price').val() || 0);
            if (isNaN(purchase_price) || purchase_price <= 0) {
                AIZ.plugins.notify('warning', 'Please enter a valid purchase price for supplier return.');
                $('#purchase_price').focus();
                return;
            }
            let product_name = $('#product_id option:selected').html();
            let varient_id = $('#varient_id').val();
            let varient_name = varient_id == '' ? 'N/A' : $('#varient_id option:selected').html();
            let trlength = $('.appendstockdata tr').length;
            trlength = parseInt(trlength) + 1;

            const tabledata = `<tr class="remove-` + parseInt(trlength) + `">
            <td><input type="hidden" name="products[]" value="` + product_id + `">` + product_name + `</td>
            <td><input type="hidden" name="variants[]" value="` + varient_id + `">` + varient_name + `</td>
            <td><input type="number" min="0" step="1" class="form-control" name="quantities[]" value="` + quantity + `"></td>
            <td><input type="number" min="0" step="0.01" class="form-control" name="purchase_prices[]" value="` + purchase_price + `"></td>
            <td><a href="javascript:;" id="" class="btn btn-xs btn-danger remove_stock" data-removeclass="remove-` +
                    parseInt(trlength) + `"><i class="las la-trash"></i></a></td>
            </tr>`;

            $('.appendstockdata').append(tabledata);
            $('#product_id').val('');
            $('#varient_id').val('');
            $('#quantity').val('');
            $('#purchase_price').val('');
            AIZ.plugins.bootstrapSelect('refresh');
        });

        $(document).on('click', '.remove_stock', function() {
            $('.' + $(this).attr('data-removeclass')).remove();
        });

        $(document).on('click', '.removeTr', function() {
            if (confirm("Are you sure you want to delete this row?")) {
                $(this).parent().parent().remove();
            } else {
                return false;
            }
            var rowCount = $('.audit-form >table >tbody >tr').length;
            if (rowCount == 1) {
                $(".audit-form >table >tbody >tr:first >td:last").html('');
            } else {
                $(".audit-form >table >tbody >tr >td:last").html(
                    '<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>'
                    );
            }
        })


        $('form').bind('submit', function(e) {
            // Disable the submit button while evaluating if the form should be submitted
            $("button[type='submit']").prop('disabled', true);
            let valid = true;

            if ($('#supplier').val() == '') {
                $('#supplier').focus();
                $('#supplier_error').text('Supplier is required');
                valid = false;
            } else {
                $('#supplier').focus();
            }

            if ($('#note').val() == '') {
                $('#note').focus();
                $('#note_error').text('Note is required');
                valid = false;
            } else {
                $('#note_error').text('');
            }
            if ($('.appendstockdata tr').length <= 0) {
                AIZ.plugins.notify('warning', 'Minimum 1 product need to be add.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
                // Reactivate the button if the form was not submitted
                $("button[type='submit']").prop('disabled', false);
            }
        });

        $("[name=product_id]").on("change", function() {
            getproductvarient($(this).val());
        });

        function getproductvarient(productid) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: '{{ route('purchaseorder.getproductvarient') }}',
                data: {
                    productid: productid
                },
                success: function(data) {
                    if (data.status == true) {
                        if (data.varient_status == 1) {
                            $('.varient_data').show();
                            $('#varient_id').html(data.varientdata);
                            AIZ.plugins.bootstrapSelect('refresh');
                        } else {
                            $('.varient_data').hide();
                            $('#varient_id').html(data.varientdata);
                            AIZ.plugins.bootstrapSelect('refresh');
                        }
                    }
                }
            });
        }
    </script>
@endsection
