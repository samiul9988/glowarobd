@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Add Advertisement')}}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{route('ads.store')}}" method="POST" enctype="multipart/form-data">
                	@csrf

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Ads Type')}}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <select name="ads_type" class="form-control aiz-selectpicker mb-2 mb-md-0 change_ads_type" required>
                                <option value="web">{{ ('Web')}}</option>
                                <option value="app">{{ ('App')}}</option>
                            </select>
                            @error('ads_type')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Position')}}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <select name="position" class="form-control mb-2 mb-md-0 aiz-selectpicker" required>

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
                                        <option value="{{$product->id}}">{{$product->name}}</option>
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
                                <select class="form-control aiz-selectpicker" name="category_id" id="category_id" data-live-search="true">
                                    <option value="">{{ ('Select Category')}}</option>
                                    @foreach ($categories as $category)
                                        <option data-catattribute="{{ $category->variation_attributes }}" data-catcolor="{{ $category->variation_color }}" value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
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
                                        <option value="{{ $brand->id }}">{{ $brand->getTranslation('name') }}</option>
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
                                <input type="text" placeholder="{{ ('Tag')}}" id="tag" name="tag" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row custom_box d-none">
                         <label class="col-md-3 col-form-label">{{ ('Custom Link')}}
                            <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <div class="box_cus_shadow">
                                <input type="text" placeholder="{{ ('Link')}}" id="link" name="link" class="form-control">
                            </div>
                        </div>
                    </div>



                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Image')}} <span>(960 X 420)</span></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="image" class="selected-files">
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
                            <textarea name="code" rows="5" class="form-control" placeholder="{{ ('Embeded link or code')}}"></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Date Range')}}<span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control aiz-date-range" name="date_range" placeholder="{{ ('Date Range')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" required>
                            @error('date_range')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Status')}}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" checked name="status" value="1">
                                <span></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ ('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="web_position d-none">
    <option value="">{{ ('Select Any')}}</option>
    @foreach ($web_positions as $key => $item)
        <option value="{{$key}}">{{$item}}</option>
    @endforeach
</div>
<div class="app_position d-none">
    <option value="">{{ ('Select Any')}}</option>
    @foreach ($app_positions as $key => $item)
        <option value="{{$key}}">{{$item}}</option>
    @endforeach
</div>

<div class="web_link_type d-none">
    <option value="">{{ ('Select Any')}}</option>
    <option value="product">{{ ('Product')}}</option>
    <option value="category">{{ ('Category')}}</option>
    <option value="brand">{{ ('Brand')}}</option>
    <option value="tag">{{ ('Tag')}}</option>
    <option value="custom">{{ ('Custom')}}</option>
</div>
<div class="app_link_type d-none">
    <option value="">{{ ('Select Link Type')}}</option>
    <option value="product">{{ ('Product')}}</option>
    <option value="category">{{ ('Category')}}</option>
    <option value="brand">{{ ('Brand')}}</option>
</div>

@endsection

@push('cus_css')
    <style>
        .box_cus_shadow{
            box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
            padding: 10px;
        }
    </style>
@endpush

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {

            var web_position = $('.web_position').html()
            $('[name="position"]').html(web_position)

            var web_link_type = $('.web_link_type').html()
            $('[name="link_type"]').html(web_link_type)

            //change ads type
            $('.change_ads_type').on('change',function(){
                let value = $(this).val();
                if( value == 'web'){
                    var web_position = $('.web_position').html()
                    $('[name="position"]').html(web_position)

                    var web_link_type = $('.web_link_type').html()
                    $('[name="link_type"]').html(web_link_type)

                } else if(value == 'app'){
                    var app_position = $('.app_position').html()
                    $('[name="position"]').html(app_position)

                    var app_link_type = $('.app_link_type').html()
                    $('[name="link_type"]').html(app_link_type)
                }
                AIZ.plugins.bootstrapSelect('refresh');
            })
            AIZ.plugins.bootstrapSelect('refresh');


            // change link type
            $('.change_link_type').on('change',function(){
                let value = $(this).val();
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
            })
        });
    </script>
@endsection
