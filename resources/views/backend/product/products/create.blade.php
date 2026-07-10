@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <h5 class="mb-0 h6">{{ ('Add New Product') }}</h5>
    </div>
    <div class="">
        <form class="form form-horizontal mar-top" action="{{ route('products.store') }}" method="POST"
            enctype="multipart/form-data" id="choice_form">
            <div class="row gutters-5">
                <div class="col-lg-8">
                    @csrf
                    <input type="hidden" name="added_by" value="admin">

                    <!-- Product Information -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Product Information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Product Name') }} <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="name"
                                        placeholder="{{ ('Product Name') }}" onchange="update_sku()" required>
                                </div>
                            </div>
                            <div class="form-group row" id="category">
                                <label class="col-md-3 col-from-label">{{ ('Category') }} <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    <select class="form-control aiz-selectpicker" name="category_id" id="category_id"
                                        data-live-search="true" required>
                                        @foreach ($categories as $category)
                                            <option data-catattribute="{{ $category->variation_attributes }}"
                                                data-catcolor="{{ $category->variation_color }}"
                                                value="{{ $category->id }}">{{ $category->name }}</option>
                                            @foreach ($category->childrenCategories as $childCategory)
                                                @include('categories.child_category', [
                                                    'child_category' => $childCategory,
                                                ])
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row" id="brand">
                                <label class="col-md-3 col-from-label">{{ ('Brand') }}</label>
                                <div class="col-md-8">
                                    <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id"
                                        data-live-search="true">
                                        <option value="">{{ ('Select Brand') }}</option>
                                        @foreach ($brands as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Unit') }}</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="unit"
                                        placeholder="{{ ('Unit (e.g. KG, Pc etc)') }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Minimum Purchase Qty') }} <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    <input type="number" lang="en" class="form-control" name="min_qty" value="1"
                                        min="1" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Tags') }} <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control aiz-tag-input" name="tags[]"
                                        placeholder="{{ ('Type and hit enter to add a tag') }}">
                                    <small
                                        class="text-muted">{{ ('This is used for search. Input those words by which cutomer can find this product.') }}</small>
                                </div>
                            </div>

                            @if (addon_is_activated('pos_system'))
                                <div class="form-group row">
                                    <label class="col-md-3 col-from-label">{{ ('Barcode') }}</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="barcode" name="barcode"
                                            placeholder="{{ ('Barcode') }}">
                                    </div>
                                </div>
                            @endif

                            @if (addon_is_activated('refund_request'))
                                <div class="form-group row">
                                    <label class="col-md-3 col-from-label">{{ ('Refundable') }}</label>
                                    <div class="col-md-8">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="checkbox" name="refundable" checked>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Product Images -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Product Images') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label"
                                    for="signinSrEmail">{{ ('Gallery Images') }} <small>(600x600)</small></label>
                                <div class="col-md-8">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image"
                                        data-multiple="true">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ ('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                        <input type="hidden" name="photos" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                    <small
                                        class="text-muted">{{ ('These images are visible in product details page gallery. Use 600x600 sizes images.') }}</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label"
                                    for="signinSrEmail">{{ ('Thumbnail Image') }}
                                    <small>(300x300)</small></label>
                                <div class="col-md-8">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ ('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                        <input type="hidden" name="thumbnail_img" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                    <small
                                        class="text-muted">{{ ('This image is visible in all product box. Use 300x300 sizes image. Keep some blank space around main object of your image as we had to crop some edge in different devices to make it responsive.') }}</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label"
                                    for="signinSrEmail">Faq Image
                                    <small>(500x500)</small></label>
                                <div class="col-md-8">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ ('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                        <input type="hidden" name="faq_img" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                    <small
                                        class="text-muted">This image is visible in FAQ section in product details page. Use 500x500 sizes image.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Videos -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Product Videos') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Video Provider') }}</label>
                                <div class="col-md-8">
                                    <select class="form-control aiz-selectpicker" name="video_provider"
                                        id="video_provider">
                                        <option value="youtube">{{ ('Youtube') }}</option>
                                        <option value="dailymotion">{{ ('Dailymotion') }}</option>
                                        <option value="vimeo">{{ ('Vimeo') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Video Link') }}</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="video_link"
                                        placeholder="{{ ('Video Link') }}">
                                    <small
                                        class="text-muted">{{ ("Use proper link without extra parameter. Don't use short share link/embeded iframe code.") }}</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Video Aspect Ratio') }}</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="video_aspect_ratio"
                                        placeholder="{{ ('Video Aspect Ratio') }}">
                                    <small
                                        class="text-muted">{{ ('Use proper ratio. E.G: 1:1, 2:3, 3:2, 4:3, 16:9') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Variation -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Product Variation') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row gutters-5">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" value="{{ ('Colors') }}"
                                        disabled>
                                </div>
                                <div class="col-md-8">
                                    <select class="form-control aiz-selectpicker" data-live-search="true"
                                        data-selected-text-format="count" name="colors[]" id="colors" multiple
                                        disabled>
                                        @foreach (\App\Models\Color::orderBy('name', 'asc')->get() as $key => $color)
                                            <option value="{{ $color->code }}"
                                                data-content="<span><span class='size-15px d-inline-block mr-2 rounded border' style='background:{{ $color->code }}'></span><span>{{ $color->name }}</span></span>">
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input value="1" type="checkbox" name="colors_active">
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row gutters-5">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" value="{{ ('Attributes') }}"
                                        disabled>
                                </div>
                                <div class="col-md-8">
                                    <select name="choice_attributes[]" id="choice_attributes"
                                        class="form-control aiz-selectpicker" data-selected-text-format="count"
                                        data-live-search="true" multiple
                                        data-placeholder="{{ ('Choose Attributes') }}">
                                        @foreach (\App\Models\Attribute::all() as $key => $attribute)
                                            <option value="{{ $attribute->id }}">{{ $attribute->getTranslation('name') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <p>{{ ('Choose the attributes of this product and then input values of each attribute') }}
                                </p>
                                <br>
                            </div>

                            <div class="customer_choice_options" id="customer_choice_options">

                            </div>
                        </div>
                    </div>

                    <!-- Product price + stock -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Product price + stock') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Unit price') }} <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <input type="number" lang="en" min="0" value="0" step="0.01"
                                        placeholder="{{ ('Unit price') }}" name="unit_price"
                                        class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 control-label"
                                    for="start_date">{{ ('Discount Date Range') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control aiz-date-range" name="date_range"
                                        placeholder="{{ ('Select Date') }}" data-time-picker="true"
                                        data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Discount') }} <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <input type="number" lang="en" min="0" value="0" step="0.01"
                                        placeholder="{{ ('Discount') }}" name="discount" class="form-control"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control aiz-selectpicker" name="discount_type">
                                        <option value="amount">{{ ('Flat') }}</option>
                                        <option value="percent">{{ ('Percent') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-from-label">{{ ('Minimum Order Amount') }}</label>
                                <div class="col-lg-9">
                                    <input type="number" lang="en" min="0" step="0.01"
                                        placeholder="{{ ('Minimum Order Amount') }}" name="min_order_amount"
                                        class="form-control" value="0" required>
                                </div>

                            </div>

                            @if (addon_is_activated('club_point'))
                                <div class="form-group row">
                                    <label class="col-md-3 col-from-label">
                                        {{ ('Set Point') }}
                                    </label>
                                    <div class="col-md-6">
                                        <input type="number" lang="en" min="0" value="0"
                                            step="1" placeholder="{{ ('1') }}" name="earn_point"
                                            class="form-control">
                                    </div>
                                </div>
                            @endif

                            <div id="show-hide-div">
                                <div class="form-group row d-none">
                                    <label class="col-md-3 col-from-label">{{ ('Quantity') }} <span
                                            class="text-danger">*</span></label>
                                    <div class="col-md-6">
                                        <input type="number" lang="en" min="0" value="0"
                                            step="1" placeholder="{{ ('Quantity') }}"
                                            name="current_stock" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-3 col-from-label">
                                        {{ ('SKU') }}
                                    </label>
                                    <div class="col-md-6">
                                        <input type="text" placeholder="{{ ('SKU') }}" name="sku"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">
                                    {{ ('External link') }}
                                </label>
                                <div class="col-md-9">
                                    <input type="text" placeholder="{{ ('External link') }}"
                                        name="external_link" class="form-control">
                                    <small
                                        class="text-muted">{{ ('Leave it blank if you do not use external site link') }}</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">
                                    {{ ('External link button text') }}
                                </label>
                                <div class="col-md-9">
                                    <input type="text" placeholder="{{ ('External link button text') }}"
                                        name="external_link_btn" class="form-control">
                                    <small
                                        class="text-muted">{{ ('Leave it blank if you do not use external site link') }}</small>
                                </div>
                            </div>
                            <br>
                            <div class="sku_combination" id="sku_combination">

                            </div>
                        </div>
                    </div>

                    <!-- Product Price Break Down -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Product Price Break Down') }}</h5>
                        </div>
                        <div class="card-body audit-form">
                            <small class="text-danger">Note: Price Breakdown will not work for subscription
                                products.</small>
                            <table class="table table-bordered table-striped mobile_no_border"
                                style="margin-bottom: 5px;margin-top: 15px;">
                                <tbody class="appendAuditTr">
                                    <tr>
                                        <td>
                                            <input type="text" placeholder="{{ ('Start Quantity') }}"
                                                name="start_qty[]" class="form-control" value="">
                                        </td>
                                        <td>
                                            <input type="text" placeholder="{{ ('End Quantity') }}"
                                                name="end_qty[]" class="form-control" value="">
                                        </td>
                                        <td>
                                            <input type="text" placeholder="{{ ('Price') }}" name="price[]"
                                                class="form-control" value="">
                                        </td>
                                        <td class="text-center">
                                            <a href="javascript:;" id=""
                                                class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div style="text-align: right; float: right;">
                                <button type="button" class="btn btn-primary btn-xs addmore-audit"><i
                                        class="fa fa-plus"></i> Add More</button>
                            </div>
                        </div>
                    </div>

                    <!-- App Price Configs -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('App Price Configuration') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 control-label"
                                    for="start_date">{{ ('App Discount Date Range') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control aiz-date-range"
                                        name="app_discount_date_range" placeholder="{{ ('Select Date') }}"
                                        data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to "
                                        autocomplete="off">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('App Discount') }} <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <input type="number" lang="en" min="0" value="0" step="0.01"
                                        placeholder="{{ ('Discount') }}" name="app_discount"
                                        class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control aiz-selectpicker" name="app_discount_type">
                                        <option value="amount">{{ ('Flat') }}</option>
                                        <option value="percent">{{ ('Percent') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Description -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Product Description') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Short Description') }}</label>
                                <div class="col-md-8">
                                    <textarea class="aiz-text-editor" name="short_description"></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Description') }}</label>
                                <div class="col-md-8">
                                    <textarea class="aiz-text-editor" name="description"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PDF Specification -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('PDF Specification') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label"
                                    for="signinSrEmail">{{ ('PDF Specification') }}</label>
                                <div class="col-md-8">
                                    <div class="input-group" data-toggle="aizuploader" data-type="document">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ ('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                        <input type="hidden" name="pdf" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Meta Tags -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('SEO Meta Tags') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Meta Title') }}</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="meta_title"
                                        placeholder="{{ ('Meta Title') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Description') }}</label>
                                <div class="col-md-8">
                                    <textarea name="meta_description" rows="8" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label"
                                    for="signinSrEmail">{{ ('Meta Image') }}</label>
                                <div class="col-md-8">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ ('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                        <input type="hidden" name="meta_img" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($customFields->isNotEmpty())
                        <!-- Product Custom Fields -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0 h6">{{ ('Custom Fields') }}</h5>
                            </div>
                            <div class="card-body">
                                @foreach ($customFields as $key => $field)
                                    <div class="form-group row">
                                        <label class="col-md-3 col-from-label">{{ ($field->name) }}</label>
                                        <div class="col-md-7">
                                            @if (strtolower($field->type) == 'html_box')
                                                <input type="hidden" id="{{ str_replace(' ','_',$field->name).'_'.$key.'_meta' }}" name="{{ str_replace(' ','_',$field->name).'_meta' }}" value="">
                                                <textarea class="aiz-text-editor" name="{{ str_replace(' ','_',$field->name).'_dynamic' }}" id="{{ str_replace(' ','_',$field->name).'_'.$key }}"></textarea>
                                            @elseif(strtolower($field->type) == 'single_text_box')
                                                <input type="hidden" id="{{ str_replace(' ','_',$field->name).'_'.$key.'_meta' }}" name="{{ str_replace(' ','_',$field->name).'_meta' }}" value="">
                                                <input type="text" class="form-control" name="{{ str_replace(' ','_',$field->name).'_dynamic' }}" id="{{ str_replace(' ','_',$field->name).'_'.$key }}">
                                            @elseif(strtolower($field->type) == 'multi_text_box')
                                                <input type="hidden" id="{{ str_replace(' ','_',$field->name).'_'.$key.'_meta' }}" name="{{ str_replace(' ','_',$field->name).'_meta' }}" value="">
                                                <textarea name="{{ str_replace(' ','_',$field->name).'_dynamic' }}" id="{{ str_replace(' ','_',$field->name).'_'.$key }}" class="form-control" rows="3"></textarea>
                                            @elseif(strtolower($field->type) == 'single_select')
                                                <input type="hidden" id="{{ str_replace(' ','_',$field->name).'_'.$key.'_meta' }}" name="{{ str_replace(' ','_',$field->name).'_meta' }}" value="">
                                                <select class="form-control aiz-selectpicker"
                                                    name="{{ str_replace(' ','_',$field->name).'_dynamic' }}" id="{{ str_replace(' ','_',$field->name).'_'.$key }}" data-live-search="true">
                                                    <option value="" disabled>Connect to a meta group</option>
                                                </select>
                                            @elseif(strtolower($field->type) == 'multi_select')
                                                <input type="hidden" id="{{ str_replace(' ','_',$field->name).'_'.$key.'_meta' }}" name="{{ str_replace(' ','_',$field->name).'_meta' }}" value="">
                                                <select class="form-control aiz-selectpicker"
                                                    name="{{ str_replace(' ','_',$field->name).'_dynamic' }}[]" id="{{ str_replace(' ','_',$field->name).'_'.$key }}" data-live-search="true" multiple>
                                                    <option value="" disabled>Connect to a meta group</option>
                                                </select>
                                            @endif
                                        </div>
                                        <div class="col-md-2">
                                            @if (strtolower($field->type) == 'single_select' || strtolower($field->type) == 'multi_select')
                                                <span role="button" id="{{ str_replace(' ','_',$field->name).'_'.$key.'_'.'Icon' }}" class="connect btn" data-connected="false" title="Connect To Meta" data-ref="{{ str_replace(' ','_',$field->name).'_'.$key }}">
                                                    <i class="las la-link fs-24"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

                <div class="col-lg-4">

                    <!-- Product Note -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Product Note') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="name">
                                    {{ ('Note About This Product') }}
                                </label>
                                <textarea name="note" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pre-order Configurations -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">
                                {{ ('Pre-order Configuration') }}
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-4 col-from-label">{{ ('Pre-Order') }}</label>
                                <div class="col-md-8">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" name="pre_order" value="1" checked="">
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-4 control-label"
                                    for="start_date">{{ ('Pre-order Date Range') }}</label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control aiz-date-range" name="preorder_date_range"
                                        placeholder="{{ ('Select Date') }}" data-time-picker="true"
                                        data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-4 col-from-label">{{ ('Max Pre-Order Qty') }} <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    <input type="number" lang="en" class="form-control" name="maxpreorderqty"
                                        value="1" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Configuration -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">
                                {{ ('Shipping Configuration') }}
                            </h5>
                        </div>

                        <div class="card-body">
                            @if (get_setting('shipping_type') == 'product_wise_shipping')
                                <div class="form-group row">
                                    <label class="col-md-6 col-from-label">{{ ('Free Shipping') }}</label>
                                    <div class="col-md-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="radio" name="shipping_type" value="free" checked>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-6 col-from-label">{{ ('Flat Rate') }}</label>
                                    <div class="col-md-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="radio" name="shipping_type" value="flat_rate">
                                            <span></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="flat_rate_shipping_div" style="display: none">
                                    <div class="form-group row">
                                        <label class="col-md-6 col-from-label">{{ ('Shipping cost') }}</label>
                                        <div class="col-md-6">
                                            <input type="number" lang="en" min="0" value="0"
                                                step="0.01" placeholder="{{ ('Shipping cost') }}"
                                                name="flat_shipping_cost" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label
                                        class="col-md-6 col-from-label">{{ ('Is Product Quantity Mulitiply') }}</label>
                                    <div class="col-md-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="checkbox" name="is_quantity_multiplied" value="1">
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            @else
                                <p>
                                    {{ ('Product wise shipping cost is disable. Shipping cost is configured from here') }}
                                    <a href="{{ route('shipping_configuration.index') }}"
                                        class="aiz-side-nav-link {{ areActiveRoutes(['shipping_configuration.index', 'shipping_configuration.edit', 'shipping_configuration.update']) }}">
                                        <span class="aiz-side-nav-text">{{ ('Shipping Configuration') }}</span>
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Usage Duration --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Usage Duration') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="usage_duration">
                                    {{ ('Days') }}
                                </label>
                                <input type="number" name="usage_duration" value="1" min="0" step="1" class="form-control" placeholder="{{ ('Enter Usage Duration in days') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Config -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Low Stock Quantity Warning') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="name">
                                    {{ ('Quantity') }}
                                </label>
                                <input type="number" name="low_stock_quantity" value="1" min="0"
                                    step="1" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Stock Visibility -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">
                                {{ ('Stock Visibility State') }}
                            </h5>
                        </div>

                        <div class="card-body">

                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{ ('Show Stock Quantity') }}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="radio" name="stock_visibility_state" value="quantity" checked>
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label
                                    class="col-md-6 col-from-label">{{ ('Show Stock With Text Only') }}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="radio" name="stock_visibility_state" value="text">
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{ ('Hide Stock') }}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="radio" name="stock_visibility_state" value="hide">
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label
                                    class="col-md-6 col-from-label">{{ ('Continue selling when out of stock') }}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" name="allow_out_Of_stock_purchases" value="1">
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cash On Delivery -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Cash On Delivery') }}</h5>
                        </div>
                        <div class="card-body">
                            @if (get_setting('cash_payment') == '1')
                                <div class="form-group row">
                                    <label class="col-md-6 col-from-label">{{ ('Status') }}</label>
                                    <div class="col-md-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="checkbox" name="cash_on_delivery" value="1"
                                                checked="">
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            @else
                                <p>
                                    {{ ('Cash On Delivery option is disabled. Activate this feature from here') }}
                                    <a href="{{ route('activation.index') }}"
                                        class="aiz-side-nav-link {{ areActiveRoutes(['shipping_configuration.index', 'shipping_configuration.edit', 'shipping_configuration.update']) }}">
                                        <span class="aiz-side-nav-text">{{ ('Cash Payment Activation') }}</span>
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Subscription -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Subscription') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{ ('Status') }}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" name="subscription" value="1">
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Featured -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Featured') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{ ('Status') }}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" name="featured" value="1">
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Todays Deal -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Todays Deal') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{ ('Status') }}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" name="todays_deal" value="1">
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flash Deal -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Flash Deal') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="name">
                                    {{ ('Add To Flash') }}
                                </label>
                                <select class="form-control aiz-selectpicker" name="flash_deal_id" id="flash_deal">
                                    <option value="">Choose Flash Title</option>
                                    @foreach (\App\Models\FlashDeal::where('status', 1)->get() as $flash_deal)
                                        <option value="{{ $flash_deal->id }}">
                                            {{ $flash_deal->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="name">
                                    {{ ('Discount') }}
                                </label>
                                <input type="number" name="flash_discount" value="0" min="0"
                                    step="1" class="form-control">
                            </div>
                            <div class="form-group mb-3">
                                <label for="name">
                                    {{ ('Discount Type') }}
                                </label>
                                <select class="form-control aiz-selectpicker" name="flash_discount_type"
                                    id="flash_discount_type">
                                    <option value="">Choose Discount Type</option>
                                    <option value="amount">{{ ('Flat') }}</option>
                                    <option value="percent">{{ ('Percent') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Time -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Estimate Shipping Time') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="name">
                                    {{ ('Shipping Days') }}
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="est_shipping_days" min="1"
                                        step="1" placeholder="{{ ('Shipping Days') }}">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"
                                            id="inputGroupPrepend">{{ ('Days') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vat & Tax -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('VAT & Tax') }}</h5>
                        </div>
                        <div class="card-body">
                            @foreach (\App\Models\Tax::where('tax_status', 1)->get() as $tax)
                                <label for="name">
                                    {{ $tax->name }}
                                    <input type="hidden" value="{{ $tax->id }}" name="tax_id[]">
                                </label>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <input type="number" lang="en" min="0" value="0"
                                            step="0.01" placeholder="{{ ('Tax') }}" name="tax[]"
                                            class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <select class="form-control aiz-selectpicker" name="tax_type[]">
                                            <option value="amount">{{ ('Flat') }}</option>
                                            <option value="percent">{{ ('Percent') }}</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                <!-- Save or Publish -->
                <div class="col-12">
                    <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">
                        <div class="btn-group mr-2" role="group" aria-label="First group">
                            <button type="submit" name="button" value="draft"
                                class="btn btn-warning">{{ ('Save As Draft') }}</button>
                        </div>
                        <div class="btn-group mr-2" role="group" aria-label="Third group">
                            <button type="submit" name="button" value="unpublish"
                                class="btn btn-primary">{{ ('Save & Unpublish') }}</button>
                        </div>
                        <div class="btn-group" role="group" aria-label="Second group">
                            <button type="submit" name="button" value="publish"
                                class="btn btn-success">{{ ('Save & Publish') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@section('modal')
    <div class="modal fade" id="connectMetaObjectModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="connectMetaObjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="connectMetaObjectModalLabel">Connect Meta Object</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="height: 260px !important;">
                    <input type="hidden" id="refId" value="">
                    <div class="form-group mb-3">
                        <label for="status">
                            {{ ('Meta Object') }}
                        </label>
                        <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="metaObjectId" data-live-search="true">
                            <option value="">{{ ('All Meta Objects') }}</option>
                            @foreach (App\Models\MetaObject::active()->get() as $object)
                                <option value="{{ $object->id }}">{{ $object->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">{{ ('Cancel')}}</button>
                    <button type="button" class="btn btn-primary btn-sm" id="connectMetaObjectModalButton">Connect</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    {{-- Barcode Scanner Script --}}
    <script>
        $(document).ready(function() {
            const $barcodeField = $('#barcode');

            $barcodeField.on('click', function() {
                $(this).select().focus();
            });

            $barcodeField.on('keypress', function(e) {
                if (e.which === 13 || e.which === 9) { // Enter or Tab key
                    e.preventDefault();
                    if ($(this).val().length > 0) {
                        // console.log('Barcode scanned:', $(this).val());
                    }
                }
            });
        });
    </script>
    {{-- Barcode Scanner Script End --}}

    <script type="text/javascript">
        $(document).ready(function() {
            var rowCount = $('.audit-form >table >tbody >tr').length;
            if (rowCount == 1) {
                $(".audit-form >table >tbody >tr:first >td:last").html('');
            } else {
                $(".audit-form >table >tbody >tr:first >td:last").html(
                    '<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>'
                    );
                $(".audit-form >table >tbody >tr >td:last").html(
                    '<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>'
                    );
            }
        });

        $(document).on('click', '.connect', function() {
            if ($(this).data('connected')) {
                let ref = $(this).data('ref');
                let metaId = $(`#${ref}_meta`).val();
                $('#metaObjectId').val(metaId).selectpicker('refresh');
            }else{
                $('#metaObjectId').val('').selectpicker('refresh');
            }
            $('#refId').val($(this).data('ref'));
            $('#connectMetaObjectModal').modal('show');
        });

        $(document).on('click', '#connectMetaObjectModalButton', async function(){
            var metaObjectId = $('#metaObjectId').val();
            var refId = $('#refId').val();
            if(metaObjectId){
                await getMetaObjectValue(metaObjectId, refId);
            }
        });

        async function getMetaObjectValue(id, refId){
            $('#connectMetaObjectModalButton').attr('disabled', true);
            await $.ajax({
                type: "GET",
                url: `{{ route('meta-objects.show', ':id') }}`.replace(':id', id),
                success: function(response) {
                    $('#connectMetaObjectModalButton').attr('disabled', false);
                    if(response.success){
                        var data = response.data;
                        var html = `<option value="" disabled>Select ${response.metaName}</option>`;
                        data.forEach(element => {
                            html += `<option value="${element.id}">${element.title}</option>`;
                        });
                        // console.log(response);
                        $(`#${refId}`).html(html);
                        $(`#${refId}`).val('').selectpicker('refresh');
                        $(`#${refId}_Icon`).addClass('text-success');
                        $(`#${refId}_Icon`).data('connected', true);
                        $(`#${refId}_Icon`).attr('title', `Connected To ${response.metaName}`);
                        $(`#${refId}_meta`).val(response.metaId);
                        $('#connectMetaObjectModal').modal('hide');
                        $('#metaObjectId').val('').selectpicker('refresh');
                        $('#refId').val('');

                        AIZ.plugins.notify('success', 'Meta Object Connected');
                    }else{
                        AIZ.plugins.notify('danger', response.message);
                    }
                },
                error: function(data) {
                    $('#connectMetaObjectModalButton').attr('disabled', false);
                    AIZ.plugins.notify('danger', response.message);
                }
            })
        }

        $(document).on('click', '.addmore-audit', function() {
            var row = $('.appendAuditTr tr:first-child').clone().find('input:text').val('').end();
            row.appendTo('.appendAuditTr');
            var rowCount = $('.audit-form >table >tbody >tr').length;

            if (rowCount == 1) {
                $(".audit-form >table >tbody >tr:first >td:last").html('');
            } else {
                $(".audit-form >table >tbody >tr:first >td:last").html(
                    '<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>'
                    );
                $(".audit-form >table >tbody >tr >td:last").html(
                    '<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>'
                    );
            }
        })

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

        /* $('form').bind('submit', function (e) {
            // Disable the submit button while evaluating if the form should be submitted
            $("button[type='submit']").prop('disabled', true);

            var valid = true;

            if (!valid) {
                e.preventDefault();

                // Reactivate the button if the form was not submitted
                $("button[type='submit']").button.prop('disabled', false);
            }
        }); */

        $("[name=shipping_type]").on("change", function() {
            $(".flat_rate_shipping_div").hide();

            if ($(this).val() == 'flat_rate') {
                $(".flat_rate_shipping_div").show();
            }

        });

        function add_more_customer_choice_option(i, name) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: '{{ route('products.add-more-choice-option') }}',
                data: {
                    attribute_id: i
                },
                success: function(data) {
                    var obj = JSON.parse(data);
                    $('#customer_choice_options').append('\
                    <div class="form-group row">\
                        <div class="col-md-3">\
                            <input type="hidden" name="choice_no[]" value="' + i + '">\
                            <input type="text" class="form-control" name="choice[]" value="' + name +
                        '" placeholder="{{ ('Choice Title') }}" readonly>\
                        </div>\
                        <div class="col-md-8">\
                            <select class="form-control aiz-selectpicker attribute_choice" required="required" data-live-search="true" name="choice_options_' + i + '[]" multiple>\
                                ' + obj + '\
                            </select>\
                        </div>\
                    </div>');
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            });
        }

        $('input[name="colors_active"]').on('change', function() {
            if (!$('input[name="colors_active"]').is(':checked')) {
                $('#colors').prop('disabled', true);
                AIZ.plugins.bootstrapSelect('refresh');
            } else {
                $('#colors').prop('disabled', false);
                AIZ.plugins.bootstrapSelect('refresh');
            }
            update_sku();
        });

        $(document).on("change", ".attribute_choice", function() {
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

        function delete_row(em) {
            $(em).closest('.form-group row').remove();
            update_sku();
        }

        function delete_variant(em) {
            $(em).closest('.variant').remove();
        }

        function update_sku() {
            $.ajax({
                type: "POST",
                url: '{{ route('products.sku_combination') }}',
                data: $('#choice_form').serialize(),
                success: function(data) {
                    $('#sku_combination').html(data);
                    AIZ.uploader.previewGenerate();
                    AIZ.plugins.fooTable();
                    if (data.length > 1) {
                        $('#show-hide-div').hide();
                    } else {
                        $('#show-hide-div').show();
                    }
                }
            });
        }

        $('#choice_attributes').on('change', function() {
            $('#customer_choice_options').html(null);
            $.each($("#choice_attributes option:selected"), function() {
                add_more_customer_choice_option($(this).val(), $(this).text());
            });
            update_sku();
        });

        $('#category_id').on('change', function() {
            var cat_attribute = $("#category_id option:selected").attr('data-catattribute');
            var cat_color = $("#category_id option:selected").attr('data-catcolor');
            $('#customer_choice_options').html(null);
            $('input[name="colors_active"]').removeAttr('checked')
            $('#colors').prop('disabled', true);
            $('input[name="colors_active"]').trigger('change');
            $("#choice_attributes option").removeAttr('selected');
            $("#choice_attributes").removeAttr('required');
            $("#colors").removeAttr('required');
            $(".attribute_choice").removeAttr('required');
            make_category_variation(cat_attribute, cat_color);
            AIZ.plugins.bootstrapSelect('refresh');
        });

        function make_category_variation(cat_attribute, cat_color) {
            if (cat_attribute != '') {
                var idarray = cat_attribute.split(',');
                //console.log(jQuery.inArray(2, idarray));
                //alert(idarray);
                $("#choice_attributes").attr('required', 'required');
                //$(".attribute_choice").attr('required','required');
                $.each($("#choice_attributes option"), function() {
                    if (jQuery.inArray($(this).val(), idarray) !== -1) {
                        $(this).attr('selected', 'selected');
                    }
                });
                $('#choice_attributes').trigger('change');
                AIZ.plugins.bootstrapSelect('refresh');
                //update_sku();
            }
            if (cat_color == 1) {
                $('input[name="colors_active"]').attr('checked', 'checked')
                $('#colors').prop('disabled', false);
                $('input[name="colors_active"]').trigger('change');
                $("#colors").attr('required', 'required');
                AIZ.plugins.bootstrapSelect('refresh');
            }
        }
    </script>
@endsection
