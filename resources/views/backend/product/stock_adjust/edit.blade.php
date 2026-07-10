@extends('backend.layouts.app')

@section('content')
<style>
    .filter-option-inner-inner{
        text-transform: capitalize !important;
    }
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ ('Edit Stock Adjust')}}</h5>
</div>
<div class="">
    <form class="form form-horizontal mar-top" action="{{route('stock-adjust.update',$stock_adjust->id)}}" method="POST" enctype="multipart/form-data" id="choice_form">
        <div class="row gutters-5">
            <div class="col-lg-12">
                @csrf
                <input type="hidden" name="added_by" value="admin">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Adjustment Type & Date')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="sa_type">{{ ('Type')}}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" name="sa_type" id="sa_type" data-live-search="true" required>
                                    <option value="">Select Adjustment Type</option>
                                    <option value="damage" @if($stock_adjust->sa_type == 'damage')selected @endif>Damage</option>
                                    <option value="returned" @if($stock_adjust->sa_type == 'returned')selected @endif>Increase</option>
                                    <option value="others" @if($stock_adjust->sa_type == 'others')selected @endif>Decrease</option>
                                </select>
                                <span class="text-danger">N.B: If you choose "Damage" then stock will decrease</span>
                                <span class="d-block text-danger" id="sa_type_error"></span>
                            </div>
                        </div>
                        <div class="form-group row">
	                        <label class="col-md-3 control-label" for="sa_date">{{ ('Date')}}</label>
	                        <div class="col-md-8">
	                          <input type="date" class="form-control" name="sa_date" id="sa_date" placeholder="{{ ('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" value="{{ date('Y-m-d',$stock_adjust->sa_date)}}">
	                        </div>
	                    </div>
                        <div class="form-group row">
	                        <label class="col-md-3 control-label" for="note">{{ ('Note')}} <span class="text-danger">*</span></label>
	                        <div class="col-md-8">
                              <textarea class="form-control" name="note" id="note" cols="30" rows="5" placeholder="Please specify any note about this adjustment..." required>{{ $stock_adjust->note }}</textarea>
                              <span class="text-danger" id="note_error"></span>
	                        </div>
	                    </div>
                        <div class="form-group row" id="damage_photo" @if($stock_adjust->sa_type != 'damage') style="display: none;" @endif>
                            <label class="col-md-3 col-form-label" for="">{{ ('Attachments (Images)') }} <span class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">
                                            {{ ('Browse') }}
                                        </div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="photos" value="{{ $stock_adjust->attachments }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm" id="damage_photo_preview"></div>
                                <small class="text-muted">{{ ('Upload damaged product images') }}</small>
                                <span class="d-block text-danger" id="damage_photo_error"></span>
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
                            <div class="col-md-6 varient_data" style="display: none;">
                                <div class="form-group">
                                    <select class="form-control aiz-selectpicker" name="varient_id" id="varient_id" data-live-search="true">
                                        <option value="">{{ ('Select Variant')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="quantity" name="quantity" placeholder="{{ ('Quantity') }}">
                                </div>
                            </div>
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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="appendstockdata">
                                @if(count($stock_adjust->stockAdjustDetails)>0)
                                @php
                                    $i=0;
                                @endphp
                                    @foreach ($stock_adjust->stockAdjustDetails as $stock_adjust_item)
                                    @php
                                    $i++;
                                    @endphp
                                        <tr class="remove-{{ $i }}">
                                            <input type="hidden" name="item_id[]" value="{{$stock_adjust_item->id}}">
                                            <td><input type="hidden" name="stock_product_id[]" value="{{$stock_adjust_item->product_id}}">{{@$stock_adjust_item->product->name}}</td>
                                            <td><input type="hidden" name="stock_varient_id[]" value="{{$stock_adjust_item->variant}}">{{@$stock_adjust_item->product_stock->variant!=''?@$stock_adjust_item->product_stock->variant:'N/A'}}</td>
                                            <td>
                                                <input type="text" class="form-control" id="" name="stock_quantity[]" value="{{$stock_adjust_item->qty}}">

                                                @if(@$stock_adjust_item->product_stock->qty < $stock_adjust_item->qty)
                                               <i style="color: red;">N.B.: Item quantity is greater than product stock quantity. This item quantity will not be updated or deleted.</i>
                                                @endif
                                            </td>
                                            <td><a href="javascript:;" id="" data-itemid="{{$stock_adjust_item->id}}" class="btn btn-xs btn-danger confirm-delete" data-removeclass="remove-{{ $i }}"><i class="las la-trash"></i></a></td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <div class="col-12">
                <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">

                    <div class="btn-group mr-2" role="group" aria-label="Third group">
                        <a href="{{ route('stock-adjust.index') }}" class="btn btn-primary">{{ ('Cancel') }}</a>
                    </div>
                    <div class="btn-group" role="group" aria-label="Second group">
                        <button type="submit" name="button" value="publish" class="btn btn-success">{{ ('Update') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
@section('modal')
    @include('modals.purchase_delete_modal')
@endsection
@section('script')

<script type="text/javascript">
    $(document).ready(function(){
        $('#barcodeInput').on('click', function() {
            $(this).select().focus();
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

    $('#sa_type').on('change', function(){
        var sa_type = $(this).val();
        $('#sa_type_error').text('');
        if(sa_type == 'damage'){
            $('#damage_photo').fadeIn();
        }else{
            $('#damage_photo').fadeOut();
            // $('#damage_photo_preview').html('');
            // $('#damage_photo_error').text('');
            // $('input[name="photos"]').val('');
        }
    });

    $(document).on('click', '.addproduct', function(){
        var product_id = $('#product_id').val();
        var product_name = $('#product_id option:selected').html();
        var varient_id = $('#varient_id').val();
        var varient_name = $('#varient_id option:selected').html();
        var quantity = $('#quantity').val();
        var trlength = $('.appendstockdata tr').length;
        trlength = parseInt(trlength)+1;
        if(varient_id==''){
            varient_name = 'N/A';
        }
        var tabledata = `<tr class="remove-`+parseInt(trlength)+`">
        <td><input type="hidden" name="item_id[]" value="0"><input type="hidden" name="stock_product_id[]" value="`+product_id+`">`+product_name+`</td>
        <td><input type="hidden" name="stock_varient_id[]" value="`+varient_id+`">`+varient_name+`</td>
        <td><input type="text" class="form-control" id="" name="stock_quantity[]" value="`+quantity+`"></td>
        <td><a href="javascript:;" id="" class="btn btn-xs btn-danger remove_stock" data-removeclass="remove-`+parseInt(trlength)+`"><i class="las la-trash"></i></a></td>
        </tr>`;

        if(parseInt(quantity)>0){
            $('.appendstockdata').append(tabledata);
            var product_id = $('#product_id').val('');
            var varient_id = $('#varient_id').val('');
            var quantity = $('#quantity').val('');
            AIZ.plugins.bootstrapSelect('refresh');
        }else{
            AIZ.plugins.notify('warning', '{{ ('Qty must be greater than 0') }}');
        }

    });
    $(document).on('click', '.remove_stock', function(){
        $('.'+$(this).attr('data-removeclass')).remove();
    });
    $(document).on('click', '.confirm-delete', function(){
        $('.remove_item').attr('data-itemid', $(this).attr('data-itemid'));
        $('.remove_item').attr('data-removeclass', $(this).attr('data-removeclass'));
    });
    $(document).on('click', '.remove_item', function(){
        var item_id = $(this).attr('data-itemid');
        var this_obj = $(this);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            url:'{{ route('stock-adjust.delete_item') }}',
            data:{
                item_id: item_id
            },
            success: function(data) {
                if(data.status==true){
                    $('.'+this_obj.attr('data-removeclass')).remove();
                    $('#delete-modal').modal('hide');
                    AIZ.plugins.notify('success', data.msg);
                }else{
                    $('#delete-modal').modal('hide');
                    AIZ.plugins.notify('warning', data.msg);
                }
            }
        });
        //$('.'+$(this).attr('data-removeclass')).remove();
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
        console.log(1)
        // Disable the submit button while evaluating if the form should be submitted
        $("button[type='submit']").prop('disabled', true);

        var valid = true;

        if($('#note').val() == ''){
            $('#note').focus();
            $('#note_error').text('Note is required');
            valid = false;
        }
        var sa_type = $('#sa_type').val();
        if(sa_type == ''){
            $('#sa_type').focus();
            $('#sa_type_error').text('Type is required');
            valid = false;
        }
        if(sa_type == 'damage'){
            var damage_photo = $('input[name="photos"]').val();
            if(damage_photo == ''){
                $('#damage_photo_error').text('Attachment is required');
                valid = false;
            }
        }
        if($('.appendstockdata tr').length <= 0){
            valid = false;
        }

        if (!valid) {
            e.preventDefault();

            // Reactivate the button if the form was not submitted
            $("button[type='submit']").prop('disabled', false);

            AIZ.plugins.notify('warning', '{{ ('Minimum 1 product need to be add.') }}');
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
            url:'{{ route('purchaseorder.getproductvarient') }}',
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
            url:'{{ route('products.add-more-choice-option') }}',
            data:{
               attribute_id: i
            },
            success: function(data) {
                var obj = JSON.parse(data);
                $('#customer_choice_options').append('\
                <div class="form-group row">\
                    <div class="col-md-3">\
                        <input type="hidden" name="choice_no[]" value="'+i+'">\
                        <input type="text" class="form-control" name="choice[]" value="'+name+'" placeholder="{{ ('Choice Title') }}" readonly>\
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
           url:'{{ route('products.sku_combination') }}',
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

</script>

@endsection
