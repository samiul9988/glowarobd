@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ ('New Purchase Order')}}</h5>
</div>
<div class="">
    <form class="form form-horizontal mar-top" action="{{route('purchaseorder.update',$purchase_order->id)}}" method="POST" enctype="multipart/form-data" id="choice_form">
        <div class="row gutters-5">
            <div class="col-lg-12">
                @csrf
                <input type="hidden" name="added_by" value="admin">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Supplier & Date')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Supplier')}}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" name="supplier_id" id="supplier_id" data-live-search="true" required>
                                    <option value="{{ $purchase_order->supplier_id }}">{{ $purchase_order->supplier->name }} ({{ $purchase_order->supplier->contact_number }})</option>
                                    @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->contact_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
	                        <label class="col-sm-3 control-label" for="start_date">{{ ('Date')}}</label>
	                        <div class="col-sm-3">
	                          <input type="date" class="form-control" name="purchase_date" placeholder="{{ ('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" value="{{ date('Y-m-d',$purchase_order->purchase_date)}}">
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
                                        <select class="form-control aiz-selectpicker" name="product_id" id="product_id" data-live-search="true">
                                            <option value="">{{ ('Select Product')}}</option>
                                            @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="appendstockdata">
                                @if(count($purchase_order->purchaseOrderDetails)>0)
                                @php
                                    $i=0;
                                @endphp
                                    @foreach ($purchase_order->purchaseOrderDetails as $purchase_item)
                                    @php
                                    $i++;
                                    @endphp
                                        <tr class="remove-{{ $i }}">
                                            <input type="hidden" name="item_id[]" value="{{$purchase_item->id}}">
                                            <td><input type="hidden" name="stock_product_id[]" value="{{$purchase_item->product_id}}">{{@$purchase_item->product->name}}</td>
                                            <td><input type="hidden" name="stock_varient_id[]" value="{{$purchase_item->variant}}">{{@$purchase_item->product_stock->variant!=''?@$purchase_item->product_stock->variant:'N/A'}}</td>
                                            <td>
                                                <input type="text" class="form-control" id="" name="stock_quantity[]" value="{{$purchase_item->qty}}">

                                                @if(@$purchase_item->product_stock->qty < $purchase_item->qty)
                                               <i style="color: red;">N.B.: Item quantity is greater than product stock quantity. This item quantity will not be updated or deleted.</i>
                                                @endif
                                            </td>
                                            <td><input type="text" class="form-control" id="" name="stock_price[]" value="{{$purchase_item->price}}"></td>
                                            <td><a href="javascript:;" id="" data-itemid="{{$purchase_item->id}}" class="btn btn-xs btn-danger confirm-delete" data-removeclass="remove-{{ $i }}"><i class="las la-trash"></i></a></td>
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
                        <a href="{{ route('purchaseorder.index') }}" class="btn btn-primary">{{ ('Cancel') }}</a>
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
$(document).on('click', '.addproduct', function(){
var product_id = $('#product_id').val();
var product_name = $('#product_id option:selected').html();
var varient_id = $('#varient_id').val();
var varient_name = $('#varient_id option:selected').html();
var quantity = $('#quantity').val();
var price = $('#price').val();
var trlength = $('.appendstockdata tr').length;
trlength = parseInt(trlength)+1;
if(varient_id==''){
    varient_name = 'N/A';
}
var tabledata = `<tr class="remove-`+parseInt(trlength)+`">
<td><input type="hidden" name="item_id[]" value="0"><input type="hidden" name="stock_product_id[]" value="`+product_id+`">`+product_name+`</td>
<td><input type="hidden" name="stock_varient_id[]" value="`+varient_id+`">`+varient_name+`</td>
<td><input type="text" class="form-control" id="" name="stock_quantity[]" value="`+quantity+`"></td>
<td><input type="text" class="form-control" id="" name="stock_price[]" value="`+price+`"></td>
<td><a href="javascript:;" id="" class="btn btn-xs btn-danger remove_stock" data-removeclass="remove-`+parseInt(trlength)+`"><i class="las la-trash"></i></a></td>
</tr>`;

if(parseInt(quantity)>0 && parseFloat(price)>0){
    $('.appendstockdata').append(tabledata);
    var product_id = $('#product_id').val('');
    var varient_id = $('#varient_id').val('');
    var quantity = $('#quantity').val('');
    var price = $('#price').val('');
    AIZ.plugins.bootstrapSelect('refresh');
}else{
    AIZ.plugins.notify('warning', '{{ ('Qty and price must be greater than 0') }}');
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
        url:'{{ route('purchaseorder.delete_item') }}',
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

        var valid = false;

        if($('.appendstockdata tr').length>0){
            valid = true;
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
