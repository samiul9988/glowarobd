@extends('backend.layouts.app')
@section('content')
@php
    $products = Cache::remember('flash_deals_products_list', now()->addHour(6), function(){
        return \App\Models\Product::with('latestStock:id,product_id,qty')
            ->published()
            ->whereHas('latestStock', function($query){
                $query->where('qty', '>', 0);
            })
            ->latest()
            ->select('id', 'name')
            ->get();
    });
@endphp

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Flash Deal Information')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('flash_deals.store') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-3 control-label" for="name">{{ ('Title')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Title')}}" id="name" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 control-label" for="background_color">{{ ('Background Color')}} <small>(Hexa-code)</small></label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('#FFFFFF')}}" id="background_color" name="background_color" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 control-label" for="name">{{ ('Text Color')}}</label>
                        <div class="col-lg-9">
                            <select name="text_color" id="text_color" class="form-control aiz-selectpicker" required>
                                <option value="">{{ ('Select One')}}</option>
                                <option value="white">{{ ('White')}}</option>
                                <option value="dark">{{ ('Dark')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Banner')}} <small>(1920x500)</small></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="banner" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                            <span class="small text-muted">{{ ('This image is shown as cover banner in flash deal details page.') }}</span>
                        </div>
                    </div>
                    {{-- Desktop Banner --}}
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Desktop Banner')}} <small>(1920x500)</small></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="desktopBanner" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                            <span class="small text-muted">{{ ('This image is shown as cover banner in flash deal details page in desktop view.') }}</span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 control-label" for="start_date">{{ ('Date')}}</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control aiz-date-range" name="date_range" placeholder="Select Date" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" required>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 control-label" for="products">{{ ('Products')}}</label>
                        <div class="col-sm-9">
                            <select name="products[]" id="products" class="form-control aiz-selectpicker" multiple required data-placeholder="{{ ('Choose Products') }}" data-live-search="true" data-selected-text-format="count">
                                @foreach($products as $product)
                                    <option value="{{$product->id}}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-danger">
                        {{ ('If any product has discount or exists in another flash deal, the discount will be replaced by this discount & time limit.') }}
                    </div>
                    <br>

                    <div class="form-group" id="discount_table">

                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ ('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        let previousSelection = [];
        $(document).ready(function(){
            $('#products').on('change', async function(){
                let product_ids = $('#products').val() || [];
                let newlySelected = product_ids.filter(id => !previousSelection.includes(id));
                previousSelection = [...product_ids];

                let dealTitle = await isExistInAnyDeal(newlySelected[0]);
                if (dealTitle !== false) {
                    Swal.fire({
                        title: "Product Already in Another Deal!",
                        text: "This product is currently part of '"+dealTitle+"' deal. Adding it here will automatically remove it from the existing deal and the remaining quantity will be added to this deal. Do you want to continue?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, Continue!",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            addToFlashDeal();
                        } else {
                            $('#products').val(previousSelection.filter(id => id != newlySelected[0]));
                            $('#products').selectpicker('refresh');
                            previousSelection = previousSelection.filter(id => id != newlySelected[0]);
                        }
                    });
                } else {
                    addToFlashDeal();
                }
            });

            function addToFlashDeal(){
                let product_ids = $('#products').val() || [];
                if(product_ids.length > 0){
                    $.post('{{ route('flash_deals.product_discount') }}', {_token:'{{ csrf_token() }}', product_ids:product_ids}, function(data){
                        $('#discount_table').html(data);
                        AIZ.plugins.fooTable();
                    });
                }
                else{
                    $('#discount_table').html(null);
                }
            }

            async function isExistInAnyDeal(product_id){
                if (!product_id) {
                    return false;
                }
                let result = false;
                await $.ajax({
                    url: '{{ route('flash_deals.is_exist_in_any_deals', ':id') }}'.replace(':id', product_id),
                    type: 'GET',
                    success: function(response){
                        if(response.success && response.exist){
                            result = response.title;
                        }
                    },
                    error: function(error){
                        console.error('Error checking deal existence:', error);
                        result = false;
                    }
                });
                return result;
            }
        });
    </script>
@endsection
