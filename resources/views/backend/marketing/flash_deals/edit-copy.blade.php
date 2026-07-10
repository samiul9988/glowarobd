@extends('backend.layouts.app')

@section('content')
@php
    $products = Cache::remember('flash_deals_products_list', now()->addHour(6), function(){
        return \App\Models\Product::published()->latest()->get();
    });
    $previousSelection = [];
@endphp
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">Edit Flash Deal Information</h5>
            </div>
            <div class="card-body p-0">
              <form class="p-4" action="{{ route('flash_deals.update', $flash_deal->id) }}" method="POST">
                @csrf
                  <input type="hidden" name="_method" value="PATCH">
                  <input type="hidden" name="lang" value="{{ $lang }}">

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">Title</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Title')}}" id="name" name="title" value="{{ $flash_deal->title }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="background_color">{{ ('Background Color')}}<small>(Hexa-code)</small></label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('#0000ff')}}" id="background_color" name="background_color" value="{{ $flash_deal->background_color }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 col-from-label" for="text_color">{{ ('Text Color')}}</label>
                        <div class="col-lg-9">
                            <select name="text_color" id="text_color" class="form-control demo-select2" required>
                                <option value="">Select One</option>
                                <option value="white" @if ($flash_deal->text_color == 'white') selected @endif>{{ ('White')}}</option>
                                <option value="dark" @if ($flash_deal->text_color == 'dark') selected @endif>{{ ('Dark')}}</option>
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
                                <input type="hidden" name="banner" value="{{ $flash_deal->banner }}" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Desktop Banner')}} <small>(1920x500)</small></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="desktopBanner" value="{{ $flash_deal->desktop_banner }}" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>

                    @php
                      $start_date = date('d-m-Y H:i:s', $flash_deal->start_date);
                      $end_date = date('d-m-Y H:i:s', $flash_deal->end_date);
                    @endphp

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="start_date">{{ ('Date')}}</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control aiz-date-range" value="{{ $start_date.' to '.$end_date }}" name="date_range" placeholder="Select Date" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="products">{{ ('Products')}}</label>
                        <div class="col-sm-9">
                            <select name="products[]" id="products" class="form-control aiz-selectpicker" multiple required data-placeholder="{{ ('Choose Products') }}" data-live-search="true" data-selected-text-format="count">
                                @foreach($products as $product)
                                    @php
                                        $flash_deal_product = \App\Models\FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first();
                                        if ($flash_deal_product) {
                                            $previousSelection[] = (string)$product->id;
                                        }
                                    @endphp
                                    <option value="{{$product->id}}" <?php if($flash_deal_product != null) echo "selected";?> >{{ $product->name }}</option>
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
        $(document).ready(function(){
            let previousSelection = @json($previousSelection) || [];

            get_flash_deal_discount();

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
                            get_flash_deal_discount();
                        } else {
                            $('#products').val(previousSelection.filter(id => id != newlySelected[0]));
                            $('#products').selectpicker('refresh');
                            previousSelection = previousSelection.filter(id => id != newlySelected[0]);
                        }
                    });
                } else {
                    get_flash_deal_discount();
                }
            });

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

            function get_flash_deal_discount(){
                var product_ids = $('#products').val();
                if(product_ids.length > 0){
                    $.post('{{ route('flash_deals.product_discount_edit') }}', {_token:'{{ csrf_token() }}', product_ids:product_ids, flash_deal_id:{{ $flash_deal->id }}}, function(data){
                        $('#discount_table').html(data);
                        AIZ.plugins.fooTable();
                    });
                }
                else{
                    $('#discount_table').html(null);
                }
            }
        });
    </script>
@endsection
