@extends('backend.layouts.app')

@section('content')
<?php
$notification_settings = get_setting('notification_status');
?>
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">

            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0 h6">{{ ('Send Notification')}}</h5>
                {{-- <a href="javascript:void(0)" onclick="reviewSettingsModalShow()" style="font-size:medium;"><i class="las la-cogs"></i> Notification Settings</a> --}}
            </div>

            <form class="form-horizontal" action="{{ route('user-notification.store') }}" method="POST" enctype="multipart/form-data">
            	@csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Notification Type')}}</label>
                        <div class="col-md-9">
                            <select name="type" class="form-control aiz-selectpicker mb-2 mb-md-0 change_link_type" required>
                                <option value="">{{ ('Select Type')}}</option>
                                <option value="product">{{ ('Product')}}</option>
                                <option value="category">{{ ('Category')}}</option>
                                <option value="brand">{{ ('Brand')}}</option>
                                <option value="custom">{{ ('Custom')}}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row product_box d-none">
                        <label class="col-md-3 col-form-label">{{ ('Product')}}</label>
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
                        <label class="col-md-3 col-form-label">{{ ('Category')}}</label>
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
                        <label class="col-md-3 col-form-label">{{ ('Brand')}}</label>
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

                    <div class="form-group row d-none">
                        <label class="col-md-3 col-form-label">{{ ('Custom Link')}}</label>
                        <div class="col-md-9">
                            <div class="box_cus_shadow">
                                <input type="text" placeholder="{{ ('Link')}}" id="link" name="url" class="form-control">
                            </div>
                        </div>
                    </div>



                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="title">{{ ('Title')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Title')}}" id="title" name="title" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="message">{{ ('Message')}}</label>
                        <div class="col-sm-9">
                            <textarea name="message" id="message" class="form-control" required></textarea>
                        </div>
                    </div>



                    <div class="form-group row">
	                    <label class="col-md-3 col-from-label">{{ ('Image') }}</label>
						<div class="col-md-8">
		                    <div class=" input-group " data-toggle="aizuploader" data-type="image">
		                        <div class="input-group-prepend">
		                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
		                        </div>
		                        <div class="form-control file-amount">{{ ('Choose File') }}</div>
		                        <input type="hidden" name="image" class="selected-files">
		                    </div>
		                    <div class="file-preview"></div>
						</div>
	                </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{ ('Send')}}</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.change_link_type').on('change',function(){
                $('input[name="url"]').val('');
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
            });
            $(document).on('change', 'select[name="product_id"], select[name="brand_id"], select[name="category_id"]', function(){
                $('input[name="url"]').val($(this).val());
            });
        });
    </script>
@endsection
