@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Edit Advertisement')}}</h5>
            </div>

            <div class="card-body p-0">
                <form class="p-4" action="{{ route('ads.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$advertizement->id}}">

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Ads Type')}}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <select name="ads_type" class="form-control aiz-selectpicker mb-2 mb-md-0 change_ads_type" required>
                                <option {{$advertizement->ads_type == 'web' ? 'selected' : ''}} value="web">{{ ('Web')}}</option>
                                <option {{$advertizement->ads_type == 'app' ? 'selected' : ''}} value="app">{{ ('App')}}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Position')}}<span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select name="position" class="form-control aiz-selectpicker mb-2 mb-md-0" required>

                            </select>
                            @error('position')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Link Type')}}</label>
                        <div class="col-md-9">
                            <select name="link_type" class="form-control aiz-selectpicker mb-2 mb-md-0 change_link_type">
                                <option value="">{{ ('Select Link Type')}}</option>
                                <option {{$advertizement->link_type == 'product' ? 'selected' : ''}} value="product">{{ ('Product')}}</option>
                                <option {{$advertizement->link_type == 'category' ? 'selected' : ''}} value="category">{{ ('Category')}}</option>
                                <option {{$advertizement->link_type == 'brand' ? 'selected' : ''}} value="brand">{{ ('Brand')}}</option>
                                <option {{$advertizement->link_type == 'tag' ? 'selected' : ''}} value="tag">{{ ('Tag')}}</option>
                                <option {{$advertizement->link_type == 'custom' ? 'selected' : ''}} value="custom">{{ ('Custom')}}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row product_box d-none">
                        <label class="col-md-3 col-form-label">{{ ('Product Link')}}
                            <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <div class="box_cus_shadow">
                                <select name="product_id"  class="form-control aiz-selectpicker mb-2 mb-md-0" data-live-search="true">
                                    <option value="">{{ ('Select Product')}}</option>
                                    @foreach ($products as $product)
                                        <option
                                        @if ($advertizement->link_type == 'product' && $advertizement->link == $product->id)
                                            {{'selected'}}
                                        @endif
                                        value="{{$product->id}}">{{$product->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row category_box d-none">
                        <label class="col-md-3 col-form-label">{{ ('Category Link')}}
                            <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <div class="box_cus_shadow">
                                <select class="form-control aiz-selectpicker" name="category_id" id="category_id" data-live-search="true" data-selected="{{ $advertizement->link }}">
                                    <option value="">{{ ('Select Category')}}</option>
                                    @foreach ($categories as $category)
                                        <option data-catattribute="{{ $category->variation_attributes }}" data-catcolor="{{ $category->variation_color }}"
                                            value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                        @foreach ($category->childrenCategories as $childCategory)
                                            @include('categories.child_category', ['child_category' => $childCategory])
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row brand_box d-none">
                        <label class="col-md-3 col-form-label">{{ ('Brand Link')}}
                            <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <div class="box_cus_shadow">
                                <select class="form-control aiz-selectpicker" name="brand_id" data-live-search="true">
                                    <option value="">{{ ('Select Brand') }}</option>
                                    @foreach ($brands as $brand)
                                        <option
                                            @if ($advertizement->link_type == 'brand' && $advertizement->link == $brand->id)
                                                {{'selected'}}
                                            @endif
                                        value="{{ $brand->id }}">{{ $brand->getTranslation('name') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row tag_box d-none">
                        <label class="col-md-3 col-form-label">{{ ('Custom Tag')}}
                            <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <div class="box_cus_shadow">
                                <input type="text" value="{{$advertizement->link_type == 'tag' ? $advertizement->link : ''}}" name="tag" class="form-control" placeholder="{{ ('Tag')}}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row custom_box d-none">
                        <label class="col-md-3 col-form-label">{{ ('Custom Link')}}
                            <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <div class="box_cus_shadow">
                                <input type="text" value="{{$advertizement->link_type == 'custom' ? $advertizement->link : ''}}" name="link" class="form-control" placeholder="{{ ('Link')}}">
                            </div>
                        </div>
                    </div>



                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Image')}}<span>(960 X 420)</span></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="image" class="selected-files" value="{{ $advertizement->image }}">
                            </div>
                            @error('image')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Code')}}</label>
                        <div class="col-md-9">
                            <textarea name="code" rows="5" class="form-control" placeholder="{{ ('Embeded link or code')}}">{{$advertizement->code}}</textarea>
                        </div>
                    </div>

                    @php
                        $start_date = $advertizement->start_date!=0 ? date('d-m-Y H:i:s', strtotime($advertizement->start_date)) : date('d-m-Y H:i:s');
                        $end_date = $advertizement->end_date!=0 ? date('d-m-Y H:i:s', strtotime($advertizement->end_date)) : date('d-m-Y H:i:s');
                    @endphp

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Date Range')}}<span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control aiz-date-range" name="date_range" placeholder="{{ ('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" required value="{{ $start_date.' to '.$end_date }}">

                            @error('date_range')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ ('Update')}}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<div class="web_position d-none">
    <option value="">{{ ('Select Any')}}</option>
    @foreach ($web_positions as $key => $item)
        <option
        @if ($advertizement->ads_type == 'web' && $advertizement->position == $key)
            {{'selected'}}
        @endif
        value="{{$key}}">{{$item}}</option>
    @endforeach
</div>
<div class="app_position d-none">
    <option value="">{{ ('Select Any')}}</option>
    @foreach ($app_positions as $key => $item)
        <option
        @if ($advertizement->ads_type == 'app' && $advertizement->position == $key)
            {{'selected'}}
        @endif
        value="{{$key}}">{{$item}}</option>
    @endforeach
</div>

@endsection


@section('script')
    <script type="text/javascript">
        $(document).ready(function() {

            // change ads type
            // var web_position = $('.web_position').html()
            // $('[name="position"]').html(web_position)

            //change ads type
            let add_type_value = "{{$advertizement->ads_type}}"
            change_ads_type(add_type_value)

            $('.change_ads_type').on('change',function(){
                let value = $(this).val();
                change_ads_type(value)
            })
            function change_ads_type(value){
                if( value == 'web'){
                    var web_position = $('.web_position').html()
                    $('[name="position"]').html(web_position)

                } else if(value == 'app'){
                    var app_position = $('.app_position').html()
                    $('[name="position"]').html(app_position)
                }
                AIZ.plugins.bootstrapSelect('refresh');
            }

            // AIZ.plugins.bootstrapSelect('refresh');

            // change link type
            let editValue = "{{$advertizement->link_type}}"
            change_type_fn(editValue)
            $('.change_link_type').on('change',function(){
                let value = $(this).val();
                change_type_fn(value)
            })
            $('.change_link_type').on('change',function(){
                let value = $(this).val();
                change_type_fn(value)
            })
            function change_type_fn(value){
                if( value == 'product'){
                    $('.product_box').removeClass('d-none')
                    $('.category_box').addClass('d-none')
                    $('.custom_box').addClass('d-none')
                    $('.brand_box').addClass('d-none')
                    $('.tag_box').addClass('d-none')

                } else if(value == 'category'){
                    $('.category_box').removeClass('d-none')
                    $('.product_box').addClass('d-none')
                    $('.custom_box').addClass('d-none')
                    $('.brand_box').addClass('d-none')
                    $('.tag_box').addClass('d-none')

                } else if(value == 'brand'){
                    $('.brand_box').removeClass('d-none')
                    $('.custom_box').addClass('d-none')
                    $('.category_box').addClass('d-none')
                    $('.product_box').addClass('d-none')
                    $('.tag_box').addClass('d-none')

                } else if(value == 'tag'){
                    $('.tag_box').removeClass('d-none')
                    $('.custom_box').addClass('d-none')
                    $('.category_box').addClass('d-none')
                    $('.product_box').addClass('d-none')
                    $('.brand_box').addClass('d-none')

                } else if(value == 'custom'){
                    $('.custom_box').removeClass('d-none')
                    $('.category_box').addClass('d-none')
                    $('.product_box').addClass('d-none')
                    $('.brand_box').addClass('d-none')
                    $('.tag_box').addClass('d-none')
                }
            }



        });
    </script>
@endsection

@push('cus_css')

@endpush
