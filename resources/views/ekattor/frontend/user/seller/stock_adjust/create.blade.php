@extends(config('app.theme').'frontend.layouts.user_panel')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('New Stock Adjust')}}</h5>
</div>
<div class="">
    <form class="form form-horizontal mar-top" action="{{route('stock-adjust.store')}}" method="POST" enctype="multipart/form-data" id="choice_form">
        <div class="row gutters-5">
            <div class="col-lg-12">
                @csrf
                <input type="hidden" name="added_by" value="admin">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Adjustment Type & Date')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('Type')}}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" name="sa_type" id="sa_type" data-live-search="true" required>
                                    <option value="">Select Adjustment Type</option>
                                    <option value="damage">Damage</option>
                                    <option value="returned">Returned</option>
                                    <option value="others">Others</option>
                                </select>
                                <span class="text-danger">N.B: If you choose "Damage" or "Others" then stock will decrease OR if you choose "Returned" the stock will increase.</span>
                            </div>
                        </div>
                        <div class="form-group row">
	                        <label class="col-sm-3 control-label" for="start_date">{{translate('Date')}}</label>
	                        <div class="col-sm-3">
	                          <input type="date" class="form-control" name="sa_date" placeholder="{{translate('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" value="{{ date('Y-m-d')}}">
	                        </div>
	                    </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product Information')}}</h5>
                    </div>
                    <div class="card-body">

                        <div class="col-md-12 row">
                            <div class="col-md-3">
                                <div class="form-group">
                                        <select class="form-control aiz-selectpicker" name="product_id" id="product_id" data-live-search="true">
                                            <option value="">{{translate('Select Product')}}</option>
                                            @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                </div>
                            </div>
                            <div class="col-md-3 varient_data" style="display: none;">
                                <div class="form-group">
                                    <select class="form-control aiz-selectpicker" name="varient_id" id="varient_id" data-live-search="true">
                                        <option value="">{{translate('Select Variant')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="quantity" name="quantity" placeholder="{{ translate('Quantity') }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                                <div class="btn-group mr-2" role="group" aria-label="First group">
                                    <button type="button" name="button" class="btn btn-success addproduct">{{ translate('Add') }}</button>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Stock Adjust Items')}}</h5>
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

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <div class="col-12">
                <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">
                    <div class="btn-group mr-2" role="group" aria-label="Third group">
                        <a href="{{ route('purchaseorder.index') }}" class="btn btn-primary">{{ translate('Cancel') }}</a>
                    </div>
                    <div class="btn-group" role="group" aria-label="Second group">
                        <button type="submit" name="button" value="publish" class="btn btn-success">{{ translate('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('script')

<script type="text/javascript">
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
        <td><input type="hidden" name="stock_product_id[]" value="`+product_id+`">`+product_name+`</td>
        <td><input type="hidden" name="stock_varient_id[]" value="`+varient_id+`">`+varient_name+`</td>
        <td><input type="text" class="form-control" id="" name="stock_quantity[]" value="`+quantity+`"></td>
        <td><a href="javascript:;" id="" class="btn btn-xs btn-danger remove_stock" data-removeclass="remove-`+parseInt(trlength)+`"><i class="las la-trash"></i></a></td>
        </tr>`;

        if(parseInt(quantity)>0){
            $('.appendstockdata').append(tabledata);
            var product_id = $('#product_id').val('');
            var varient_id = $('#varient_id').val('');
            var quantity = $('#quantity').val('');
            var price = $('#price').val('');
            AIZ.plugins.bootstrapSelect('refresh');
        }else{
            AIZ.plugins.notify('warning', '{{ translate('Qty must be greater than 0') }}');
        }

    });
    $(document).on('click', '.remove_stock', function(){
        $('.'+$(this).attr('data-removeclass')).remove();
    });
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
            AIZ.plugins.notify('warning', '{{ translate('Minimum 1 product need to be add.') }}');
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

</script>

@endsection
