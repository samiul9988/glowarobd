@extends('backend.layouts.app')

@section('content')
@php
    $banks = \App\Models\ACCBank::all();
    $jsBanks = $banks->toJson();
    $beforedebts = \App\Models\AccTransaction::where('head', 'like', '%Cash In Hand%')->sum('debit');
    $beforecreds = \App\Models\AccTransaction::where('head', 'like', '%Cash In Hand%')->sum('credit');
    $cashbalance = $beforedebts - $beforecreds;
@endphp
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ ('New Purchase Order')}}</h5>
</div>
<div class="">
    <form id="purchase-order-form" class="form form-horizontal mar-top" action="{{route('purchaseorder.store')}}" method="POST" enctype="multipart/form-data" id="choice_form">
        <div class="row gutters-5">
            <div class="col-lg-12">
                @csrf
                <input type="hidden" name="added_by" value="admin">
                <input type="hidden" name="template_id" id="template" value="">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Supplier & Date')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Supplier')}}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" name="supplier_id" id="supplier_id" data-live-search="true" required>
                                    @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" data-template="{{ $supplier->template?->id ?? '' }}">{{ $supplier->name }} ({{ $supplier->contact_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
	                        <label class="col-sm-3 control-label" for="start_date">{{ ('Date')}}</label>
	                        <div class="col-sm-3">
	                          <input type="date" class="form-control" name="purchase_date" placeholder="{{ ('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" value="{{ date('Y-m-d')}}">
	                        </div>
	                    </div>
                        <div class="form-group row">
						    <label class="col-sm-3 control-label" for="attachement">{{ ('Attachement')}}</label>
                            <div class="col-sm-8">
                                <div class="input-group" data-toggle="aizuploader">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="attachement" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Product Information')}}</h5>
                    </div>
                    <div class="card-body">

                        <div class="col-md-12 row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ ('Select Product')}}</label>
                                            <select class="form-control aiz-selectpicker" name="product_id" id="product_id" data-live-search="true">
                                                <option value="">{{ ('Select Product')}}</option>
                                                @foreach ($products as $product)
                                                <option value="{{ $product->id }}" data-barcode="{{ $product->barcode ?? '' }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ ('Or Scan Barcode')}}</label>
                                            <input type="text" id="barcodeInput" class="form-control" placeholder="Click here and scan barcode" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 varient_data" style="display: none;">
                                <div class="form-group">
                                        <select class="form-control aiz-selectpicker" name="varient_id" id="varient_id" data-live-search="true">
                                            <option value="">{{ ('Select Variant')}}</option>
                                        </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="quantity" name="quantity" placeholder="{{ ('Quantity') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="number" lang="en" class="form-control" name="price" id="price" placeholder="Unit Price">
                                </div>
                            </div>
                            @if(get_setting('enable_product_expire_date') == 1)
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="date" lang="en" class="form-control" name="expire_date" id="expire_date" placeholder="Expire Date" value="">
                                    <small class="text-muted">{{ ('* Expire Date') }}</small>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="col-12">
                            <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                                <div class="btn-group mr-2" role="group" aria-label="First group">
                                    <button type="button" name="button" class="btn btn-success addproduct">{{ ('Add') }}</button>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Purchase Order Items')}}</h5>
                    </div>
                    <div class="card-body">

                        <table class="table table-bordered table-striped mobile_no_border" style="margin-bottom: 5px;margin-top: 15px;">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Variant</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    @if(get_setting('enable_product_expire_date') == 1)
                                        <th>Expire</th>
                                    @endif
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="appendstockdata">

                            </tbody>
                            <tfoot x-data="supplierPayModal()">
                                <tr>
                                    <th colspan="3" class="text-right">Grand Total :</th>
                                    <th class="text-right" colspan="2" id="result">0</th>
                                    <input type="hidden" value="0" name="total_amount" id="total_amount" />
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right">Total Payments :</th>
                                    <th class="text-right" colspan="2" id="paytotal">0</th>
                                    <input type="hidden" value="0" name="total_pay" id="total_pay" />
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right">Due Amount :</th>
                                    <th class="text-right" colspan="2" id="duetotal">0</th>
                                    <input type="hidden" value="0" name="total_due" id="total_due" />
                                </tr>
                                <tr x-show="hasBalance" id="has_balance" style="display: none;">
                                    <th colspan="3" class="text-right">Balance :</th>
                                    <th class="text-right" colspan="2" id="balancetotal">0</th>
                                    <input type="hidden" value="0" name="total_balance" id="total_balance" />
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @include('modals.supplier_pay_modal')

            <div class="col-12">
                <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">
                    <div class="btn-group mr-2" role="group" aria-label="Third group">
                        <button type="button" onclick="paynow()" class="btn btn-success">{{ ('Pay Now') }}</button>
                    </div>
                    <div class="btn-group mr-2" role="group" aria-label="Third group">
                        <a href="{{ route('purchaseorder.index') }}" class="btn btn-primary">{{ ('Cancel') }}</a>
                    </div>
                    <div class="btn-group" role="group" aria-label="Second group">
                        <button id="save-purchase-order" type="button" name="button" value="publish" class="btn btn-success">{{ ('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('script')

<script type="text/javascript">
    $(document).ready(function () {
        $('#save-purchase-order').on('click', function(e){
            e.preventDefault();
            // $('#purchase-order-form').submit();

            const formData = new FormData(document.getElementById('purchase-order-form'));

            $('.loading-overlay').css('display', 'flex');
            $.ajax({
                url: "{{ route('purchaseorder.store') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('.loading-overlay').css('display', 'none');
                    if (response.success) {
                        $('#purchase-order-form')[0].reset();
                        $('.appendstockdata').empty();
                        showAlert('success', response.message, response.redirect_base);
                        @if(get_setting('enable_product_expire_date') == 1)
                            if(response.redirect_print) {
                                window.open(response.redirect_print, '_blank');
                            }
                        @endif
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function(xhr) {
                    $('.loading-overlay').css('display', 'none');
                    showAlert('error', 'Something went wrong. Please try again.');
                }
            });
        });
        // Focus barcode input when clicked
        $('#barcodeInput').on('click', function() {
            $(this).select().focus();
        });

        $('#supplier_id').on('change', function() {
            const templateId = $(this).find(':selected').data('template');
            $('#template').val(templateId);
        });

        const barcodeMap = {};
        $('#product_id option').each(function() {
            const barcode = $(this).data('barcode');
            if (barcode) {
                barcodeMap[barcode] = $(this).val();
            }
        });

        $('#barcodeInput').on('keydown', function(e) {
            // Check if the pressed key is Enter or Tab
            if (e.which === 13 || e.which === 9) {
                e.preventDefault();
                const barcode = $(this).val().trim();
                if (barcode && barcodeMap[barcode]) {
                    $('#product_id').val(barcodeMap[barcode]);
                    $('.aiz-selectpicker').selectpicker('refresh');
                    getproductvarient(barcodeMap[barcode]);
                } else if (barcode) {
                    $('#product_id').val('');
                    $('.aiz-selectpicker').selectpicker('refresh');
                    alert('Product not found!');
                }

                $(this).val('').focus();
            }
        });
    });
    $(document).on('click', '.addproduct', function(){
        var product_id = $('#product_id').val();
        var product_name = $('#product_id option:selected').html();
        var varient_id = $('#varient_id').val() ?? '';
        var varient_name = $('#varient_id option:selected').html() ?? 'N/A';
        var quantity = $('#quantity').val();
        var price = $('#price').val();
        @if(get_setting('enable_product_expire_date') == 1)
            var expire_date = $('#expire_date').val() ?? '';
        @endif
        var trlength = $('.appendstockdata tr').length;
        trlength = parseInt(trlength)+1;
        if(product_id==''){
            AIZ.plugins.notify('warning', '{{ ("Please select a product") }}');
            return;
        }else if(quantity==''){
            AIZ.plugins.notify('warning', '{{ ("Please enter quantity") }}');
            return;
        }else if(price==''){
            AIZ.plugins.notify('warning', '{{ ("Please enter price") }}');
            return;
        }
        else if(!parseInt(quantity) > 0 && !parseFloat(price) > 0) {
            AIZ.plugins.notify('warning', '{{ ("Qty and price must be greater than 0") }}');
            return;
        }
        // return;
        if(varient_id==''){
            varient_name = 'N/A';
        }
        var tabledata = `<tr class="remove-`+parseInt(trlength)+`">
        <td><input type="hidden" name="stock_product_id[]" value="`+product_id+`">`+product_name+`</td>
        <td><input type="hidden" name="stock_varient_id[]" value="`+varient_id+`">`+varient_name+`</td>
        <td><input type="text" class="form-control" id="" name="stock_quantity[]" value="`+quantity+`" oninput="calculateTotal()"></td>
        <td><input type="text" class="form-control" id="" name="stock_price[]" value="`+price+`" oninput="calculateTotal()"></td>`;
        @if(get_setting('enable_product_expire_date') == 1)
            tabledata += `<td><input type="date" class="form-control" id="" name="stock_expire_date[]" value="`+expire_date+`"></td>`;
        @endif
        tabledata += `<td><a href="javascript:;" id="" class="btn btn-xs btn-danger remove_stock" data-removeclass="remove-`+parseInt(trlength)+`"><i class="las la-trash"></i></a></td>
        </tr>`;

        if(parseInt(quantity)>0 && parseFloat(price)>0){
            $('.appendstockdata').append(tabledata);
            var product_id = $('#product_id').val('');
            var varient_id = $('#varient_id').val('');
            var quantity = $('#quantity').val('');
            var price = $('#price').val('');
            @if(get_setting('enable_product_expire_date') == 1)
                var expire_date = $('#expire_date').val('');
            @endif
            AIZ.plugins.bootstrapSelect('refresh');
            calculateTotal();
            $("#duetotal").text(supplierPayModal().getDueTotal());
        }else{
            AIZ.plugins.notify('warning', '{{ ("Qty and price must be greater than 0") }}');
        }
    });

    $(document).on('click', '.remove_stock', function(){
        $('.'+$(this).attr('data-removeclass')).remove();
        calculateTotal();
    });

    $(document).ready(function(){
        var rowCount = $('.audit-form >table >tbody >tr').length;
            if(rowCount==1){
            $( ".audit-form >table >tbody >tr:first >td:last" ).html('');
        }else{
            $( ".audit-form >table >tbody >tr:first >td:last" ).html('<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>');
            $( ".audit-form >table >tbody >tr >td:last" ).html('<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>');
        }
    });

    $(document).on('click','.addmore-audit',function(){
        var row = $('.appendAuditTr tr:first-child').clone().find('input:text').val('').end();
        row.appendTo('.appendAuditTr');
        var rowCount = $('.audit-form >table >tbody >tr').length;

        if(rowCount==1){
            $(".audit-form >table >tbody >tr:first >td:last").html('');
        }
        else{
            $( ".audit-form >table >tbody >tr:first >td:last" ).html('<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>');
            $(".audit-form >table >tbody >tr >td:last" ).html('<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>');
        }
    })

    $(document).on('click','.removeTr',function(){
        if(confirm("Are you sure you want to delete this row?")){
            $(this).parent().parent().remove();
        }
        else{
            return false;
        }
        var rowCount = $('.audit-form >table >tbody >tr').length;
        if(rowCount==1){
            $( ".audit-form >table >tbody >tr:first >td:last" ).html('');
        }else{
            $( ".audit-form >table >tbody >tr >td:last" ).html('<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>');
        }
    })

    $('form').bind('submit', function (e) {
        // Disable the submit button while evaluating if the form should be submitted
        $("button[type='submit']").prop('disabled', true);

        var valid = false;

        if($('.appendstockdata tr').length>0){
            valid = true;
        }


        if (!valid) {
            e.preventDefault();

            // Reactivate the button if the form was not submitted
            $("button[type='submit']").prop('disabled', false);

            AIZ.plugins.notify('warning', '{{ ("Minimum 1 product need to be add.") }}');
        }
    });

    $("[name=shipping_type]").on("change", function (){
        $(".flat_rate_shipping_div").hide();

        if($(this).val() == 'flat_rate'){
            $(".flat_rate_shipping_div").show();
        }

    });

    $("[name=product_id]").on("change", function (){
        getproductvarient($(this).val());
    });

    function getproductvarient(productid){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            url:'{{ route("purchaseorder.getproductvarient") }}',
            data:{
                productid: productid
            },
            success: function(data) {
                if(data.status==true){
                    if(data.varient_status==1){
                        $('.varient_data').show();
                        $('#varient_id').html(data.varientdata);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }else{
                        $('.varient_data').hide();
                        $('#varient_id').html(data.varientdata);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
           }
       });
    }

    function add_more_customer_choice_option(i, name){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            url:'{{ route("products.add-more-choice-option") }}',
            data:{
               attribute_id: i
            },
            success: function(data) {
                var obj = JSON.parse(data);
                $('#customer_choice_options').append('\
                <div class="form-group row">\
                    <div class="col-md-3">\
                        <input type="hidden" name="choice_no[]" value="'+i+'">\
                        <input type="text" class="form-control" name="choice[]" value="'+name+'" placeholder="{{ ("Choice Title") }}" readonly>\
                    </div>\
                    <div class="col-md-8">\
                        <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_'+ i +'[]" multiple>\
                            '+obj+'\
                        </select>\
                    </div>\
                </div>');
                AIZ.plugins.bootstrapSelect('refresh');
           }
       });
    }

    $('input[name="colors_active"]').on('change', function() {
        if(!$('input[name="colors_active"]').is(':checked')) {
            $('#colors').prop('disabled', true);
            AIZ.plugins.bootstrapSelect('refresh');
        }
        else {
            $('#colors').prop('disabled', false);
            AIZ.plugins.bootstrapSelect('refresh');
        }
        update_sku();
    });

    $(document).on("change", ".attribute_choice",function() {
        update_sku();
    });

    $('#colors').on('change', function() {
        update_sku();
    });

    $('input[name="unit_price"]').on('keyup', function() {
        update_sku();
    });

    $('input[name="name"]').on('keyup', function() {
        update_sku();
    });

    function delete_row(em){
        $(em).closest('.form-group row').remove();
        update_sku();
    }

    function delete_variant(em){
        $(em).closest('.variant').remove();
    }

    function update_sku(){
        $.ajax({
           type:"POST",
           url:'{{ route("products.sku_combination") }}',
           data:$('#choice_form').serialize(),
           success: function(data) {
                $('#sku_combination').html(data);
                AIZ.uploader.previewGenerate();
                AIZ.plugins.fooTable();
                if (data.length > 1) {
                   $('#show-hide-div').hide();
                }
                else {
                    $('#show-hide-div').show();
                }
           }
       });
    }

    $('#choice_attributes').on('change', function() {
        $('#customer_choice_options').html(null);
        $.each($("#choice_attributes option:selected"), function(){
            add_more_customer_choice_option($(this).val(), $(this).text());
        });

        update_sku();
    });

    function calculateTotal() {
        // Get all input fields with name "stock_quantity[]" and "stock_price[]"
        var quantityInputs = document.getElementsByName('stock_quantity[]');
        var priceInputs = document.getElementsByName('stock_price[]');

        // Initialize total quantity and total price
        var totalQuantity = 0;
        var totalPrice = 0;

        // Loop through each input field and add up the values
        for (var i = 0; i < quantityInputs.length; i++) {
            let thisQty = parseFloat(quantityInputs[i].value);
            let thisPrice = parseFloat(priceInputs[i].value) * thisQty;
            totalQuantity += parseFloat(quantityInputs[i].value) || 0;
            totalPrice += thisPrice || 0;
        }

        $("#total_due").val(Math.abs(totalPrice) - $("#total_pay").val());
        $("#duetotal").text(Math.abs(totalPrice) - $("#total_pay").val());

        // Display the result
        $('#result').text(totalPrice);
        $('#total_amount').val(totalPrice);

        return totalPrice;
    }

    function paynow(e) {

        var valid = false;
        if($('.appendstockdata tr').length>0){
            valid = true;
        }

        if (!valid) {
            AIZ.plugins.notify('warning', '{{ ("Minimum 1 product need to be add.") }}');
        }else{
            var url = $(this).data("href");
            $("#supplier-pay-modal").modal("show");
            $("#supplier-payment-link").attr("href", url);
            $("#total_amount").val(calculateTotal());
        }
    }

    function supplierPayModal(){
        return {
            hasBalance: false,
            showModal: false,
            banks: JSON.parse('{!! addslashes($jsBanks) !!}'),
            buttonDisabled: false,
            payRows: [{
                method: '',
                bank_type: '',
                bank: '',
                amount: 0,
                showBankType: false,
                showBank: false,
                filteredBanks: JSON.parse('{!! addslashes($jsBanks) !!}')
            }],
            addRow() {
                this.payRows.push({
                    method: '',
                    bank_type: '',
                    bank: '',
                    amount: 0,
                    showBankType: false,
                    showBank: false,
                    filteredBanks: this.banks
                });
            },
            toggleShowBankType(el, index) {
                let method = el.value;
                this.payRows[index].showBankType = (method == 'bank') ? true : false;
            },
            toggleShowBank(el, index) {
                let type = el.value;
                this.payRows[index].showBank = this.payRows[index].showBankType;
                if (type == 'General Bank') {
                    this.payRows[index].filteredBanks = this.banks.filter((bank) => bank.type == 'General Bank');
                } else if (type == 'Mobile Bank') {
                    this.payRows[index].filteredBanks = this.banks.filter((bank) => bank.type == 'Mobile Bank');
                } else {
                    this.payRows[index].filteredBanks = this.banks;
                }
            },

            removeRow(index) {
                if (this.payRows.length > 1) {
                    this.payRows.splice(index, 1);
                } else {
                    return;
                }
            },

            async savePayments(el) {
                this.buttonDisabled = true;
                // const sum = this.payRows.reduce((sum, row) => sum + parseFloat(row.amount || 0), 0);
                await setTimeout(() => {
                    this.buttonDisabled = false;

                    let paytotal = this.getPaymentTotal();
                    let duetotal = this.getDueTotal();

                    $("#total_amount").val(calculateTotal());
                    $("#paytotal").text(paytotal);
                    $("#total_pay").val(paytotal);
                    $("#duetotal").text(duetotal);
                    $("#total_due").val(duetotal);

                    $("#supplier-pay-modal").modal("hide");
                }, [1000]);
            },

            getBankName(id){
                let findbank = this.banks.filter((bank) => bank.id == parseInt(id));
                return findbank.bank_name;
            },

            getPaymentTotal(){
                return this.payRows.reduce((sum, row) => sum + parseFloat(row.amount || 0), 0);
            },

            getDueTotal(){
                let total = parseFloat(calculateTotal() - this.getPaymentTotal());
                if(total < 0){
                    this.hasBalance = true;
                    $("#balancetotal").text(Math.abs(total));
                    $("#total_balance").val(Math.abs(total));
                    $("#has_balance").show();
                    return 0;
                }else{
                    this.hasBalance = false;
                    $("#balancetotal").text(0);
                    $("#total_balance").val(0);
                    $("#has_balance").hide();
                    return total;
                }
            }
        }
    }

    function getPayments(){
        return supplierPayModal().payRows;
    }

</script>

@endsection
