@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h1 class="mb-0 h6">{{ ('Edit Product') }}</h5>
</div>
<div class="">
    <form class="form form-horizontal mar-top" action="{{route('products.update', $product->id)}}" method="POST" enctype="multipart/form-data" id="choice_form">
        <div class="row gutters-5">
            <div class="col-lg-8">
                <input name="_method" type="hidden" value="POST">
                <input type="hidden" name="id" value="{{ $product->id }}">
                <input type="hidden" name="lang" value="{{ $lang }}">
                @csrf

                <!-- Product Information -->
                <div class="card">
                    <ul class="nav nav-tabs nav-fill border-light">
                        @foreach (\App\Models\Language::all() as $key => $language)
                        <li class="nav-item">
                            <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3" href="{{ route('products.admin.edit', ['id'=>$product->id, 'lang'=> $language->code] ) }}">
                                <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                                <span>{{$language->name}}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Product Name')}} <i class="las la-language text-danger" title="{{ ('Translatable')}}"></i></label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" id="name" name="name" placeholder="{{ ('Product Name')}}" value="{{ $product->name }}" required>
                            </div>
                        </div>
                        <div class="form-group row" id="category">
                            <label class="col-lg-3 col-from-label">{{ ('Category')}}</label>
                            <div class="col-lg-8">
                                <select class="form-control aiz-selectpicker" name="category_id" id="category_id" data-selected="{{ $product->category_id }}" data-live-search="true" required>
                                    @foreach ($categories as $category)
                                        <option data-catattribute="{{ $category->variation_attributes }}" data-catcolor="{{ $category->variation_color }}" value="{{ $category->id }}">{{ $category->name }}</option>
                                        @foreach ($category->childrenCategories as $childCategory)
                                            @include('categories.child_category', ['child_category' => $childCategory])
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" id="brand">
                            <label class="col-lg-3 col-from-label">{{ ('Brand')}}</label>
                            <div class="col-lg-8">
                                <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id" data-live-search="true">
                                    <option value="">{{ ('Select Brand') }}</option>
                                    @foreach ($brands as $id => $name)
                                        <option value="{{ $id }}" @if($product->brand_id == $id) selected @endif>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Unit')}} <i class="las la-language text-danger" title="{{ ('Translatable')}}"></i> </label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="unit" placeholder="{{ ('Unit (e.g. KG, Pc etc)') }}" value="{{ $product->unit }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Minimum Purchase Qty')}}</label>
                            <div class="col-lg-8">
                                <input type="number" lang="en" class="form-control" name="min_qty" value="@if($product->min_qty <= 1){{1}}@else{{$product->min_qty}}@endif" min="1" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Maximum Purchase Qty')}}</label>
                            <div class="col-lg-8">
                                <input type="number" lang="en" class="form-control" name="max_qty" value="@if($product->max_qty < 1){{0}}@else{{$product->max_qty}}@endif" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Tags')}}</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control aiz-tag-input" name="tags[]" id="tags" value="{{ $product->tags }}" placeholder="{{ ('Type to add a tag') }}" data-role="tagsinput">
                            </div>
                        </div>

                        @if (addon_is_activated('pos_system'))
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Barcode')}}</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" id="barcode" name="barcode" placeholder="{{ ('Barcode') }}" value="{{ $product->barcode }}">
                            </div>
                        </div>
                        @endif

                        @if (addon_is_activated('refund_request'))
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Refundable')}}</label>
                            <div class="col-lg-8">
                                <label class="aiz-switch aiz-switch-success mb-0" style="margin-top:5px;">
                                    <input type="checkbox" name="refundable" @if ($product->refundable == 1) checked @endif>
                                    <span class="slider round"></span></label>
                                </label>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Product Images -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Product Images')}}</h5>
                    </div>
                    <div class="card-body">

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Gallery Images')}}</label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="photos" value="{{ $product->photos }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Thumbnail Image')}} <small>(290x300)</small></label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="thumbnail_img" value="{{ $product->thumbnail_img }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
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
                                    <input type="hidden" name="faq_img" class="selected-files" value="{{ $product->faq_img }}">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                                <small
                                    class="text-muted">This image is visible in FAQ section in product details page. Use 500x500 sizes image.</small>
                            </div>
                        </div>
                        {{-- <div class="form-group row">
                                                    <label class="col-lg-3 col-from-label">{{ ('Gallery Images')}}</label>
                        <div class="col-lg-8">
                            <div id="photos">
                                @if(is_array(json_decode($product->photos)))
                                @foreach (json_decode($product->photos) as $key => $photo)
                                <div class="col-md-4 col-sm-4 col-xs-6">
                                    <div class="img-upload-preview">
                                        <img loading="lazy"  src="{{ uploaded_asset($photo) }}" alt="" class="img-responsive">
                                            <input type="hidden" name="previous_photos[]" value="{{ $photo }}">
                                            <button type="button" class="btn btn-danger close-btn remove-files"><i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>
                    </div> --}}
                        {{-- <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Thumbnail Image')}} <small>(290x300)</small></label>
                            <div class="col-lg-8">
                                <div id="thumbnail_img">
                                    @if ($product->thumbnail_img != null)
                                    <div class="col-md-4 col-sm-4 col-xs-6">
                                        <div class="img-upload-preview">
                                            <img loading="lazy"  src="{{ uploaded_asset($product->thumbnail_img) }}" alt="" class="img-responsive">
                                            <input type="hidden" name="previous_thumbnail_img" value="{{ $product->thumbnail_img }}">
                                            <button type="button" class="btn btn-danger close-btn remove-files"><i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>

                <!-- Product Videos -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Product Videos')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Video Provider')}}</label>
                            <div class="col-lg-8">
                                <select class="form-control aiz-selectpicker" name="video_provider" id="video_provider">
                                    <option value="youtube" <?php if ($product->video_provider == 'youtube') echo "selected"; ?> >{{ ('Youtube')}}</option>
                                    <option value="dailymotion" <?php if ($product->video_provider == 'dailymotion') echo "selected"; ?> >{{ ('Dailymotion')}}</option>
                                    <option value="vimeo" <?php if ($product->video_provider == 'vimeo') echo "selected"; ?> >{{ ('Vimeo')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Video Link')}}</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="video_link" value="{{ $product->video_link }}" placeholder="{{ ('Video Link') }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Video Aspect Ratio')}}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="video_aspect_ratio"  value="{{ $product->video_aspect_ratio }}" placeholder="{{ ('Video Aspect Ratio') }}">
                                <small class="text-muted">{{ ("Use proper ratio. E.G: 1:1, 2:3, 3:2, 4:3, 16:9")}}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Variation -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Product Variation')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row gutters-5">
                            <div class="col-lg-3">
                                <input type="text" class="form-control" value="{{ ('Colors')}}" disabled>
                            </div>
                            <div class="col-lg-8">
                                <select class="form-control aiz-selectpicker" data-live-search="true" data-selected-text-format="count" name="colors[]" id="colors" multiple>
                                    @foreach (\App\Models\Color::orderBy('name', 'asc')->get() as $key => $color)
                                    <option
                                        value="{{ $color->code }}"
                                        data-content="<span><span class='size-15px d-inline-block mr-2 rounded border' style='background:{{ $color->code }}'></span><span>{{ $color->name }}</span></span>"
                                        <?php if (in_array($color->code, json_decode($product->colors))) echo 'selected' ?>
                                        ></option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-1">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" type="checkbox" name="colors_active" <?php if (count(json_decode($product->colors)) > 0) echo "checked"; ?> >
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row gutters-5">
                            <div class="col-lg-3">
                                <input type="text" class="form-control" value="{{ ('Attributes')}}" disabled>
                            </div>
                            <div class="col-lg-8">
                                <select name="choice_attributes[]" id="choice_attributes" data-selected-text-format="count" data-live-search="true" class="form-control aiz-selectpicker" multiple data-placeholder="{{ ('Choose Attributes') }}">
                                    @foreach (\App\Models\Attribute::all() as $key => $attribute)
                                    <option value="{{ $attribute->id }}" @if($product->attributes != null && in_array($attribute->id, json_decode($product->attributes, true))) selected @endif>{{ $attribute->getTranslation('name') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="">
                            <p>{{ ('Choose the attributes of this product and then input values of each attribute') }}</p>
                            <br>
                        </div>
                        <div class="customer_choice_options" id="customer_choice_options">
                            @foreach (json_decode($product->choice_options) as $key => $choice_option)
                            <div class="form-group row">
                                <div class="col-lg-3">
                                    <input type="hidden" name="choice_no[]" value="{{ $choice_option->attribute_id }}">
                                    <input type="text" class="form-control" name="choice[]" value="{{ optional(\App\Models\Attribute::find($choice_option->attribute_id))->getTranslation('name') }}" placeholder="{{ ('Choice Title') }}" disabled>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_{{ $choice_option->attribute_id }}[]" multiple>
                                        @foreach (\App\Models\AttributeValue::where('attribute_id', $choice_option->attribute_id)->get() as $row)
                                        <option value="{{ $row->value }}" @if( in_array($row->value, $choice_option->values)) selected @endif>
                                            {{ $row->value }}
                                        </option>
                                        @endforeach
                                    </select>
                                    {{-- <input type="text" class="form-control aiz-tag-input" name="choice_options_{{ $choice_option->attribute_id }}[]" placeholder="{{ ('Enter choice values') }}" value="{{ implode(',', $choice_option->values) }}" data-on-change="update_sku"> --}}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Product Price + Stock -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Product price + stock')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Unit price')}}</label>
                            <div class="col-lg-6">
                                <input type="text" placeholder="{{ ('Unit price')}}" name="unit_price" class="form-control" value="{{$product->unit_price}}" required>
                            </div>
                        </div>

                        @php
                          $start_date = date('d-m-Y H:i:s', $product->discount_start_date);
                          $end_date = date('d-m-Y H:i:s', $product->discount_end_date);
                        @endphp

                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="start_date">{{ ('Discount Date Range')}}</label>
                            <div class="col-sm-9">
                              <input type="text" class="form-control aiz-date-range" @if($product->discount_start_date && $product->discount_end_date) value="{{ $start_date.' to '.$end_date }}" @endif name="date_range" placeholder="{{ ('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Discount')}}</label>
                            <div class="col-lg-6">
                                <input type="number" lang="en" min="0" step="0.01" placeholder="{{ ('Discount')}}" name="discount" class="form-control" value="{{ $product->discount }}" required>
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control aiz-selectpicker" name="discount_type" required>
                                    <option value="amount" <?php if ($product->discount_type == 'amount') echo "selected"; ?> >{{ ('Flat')}}</option>
                                    <option value="percent" <?php if ($product->discount_type == 'percent') echo "selected"; ?> >{{ ('Percent')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Minimum Order Amount')}}</label>
                            <div class="col-lg-9">
                                <input type="number" lang="en" min="0" step="0.01" placeholder="{{ ('Minimum Order Amount')}}" name="min_order_amount" class="form-control" value="{{ $product->min_order_amount }}" required>
                            </div>

                        </div>

                        @if(addon_is_activated('club_point'))
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">
                                    {{ ('Set Point')}}
                                </label>
                                <div class="col-md-6">
                                    <input type="number" lang="en" min="0" value="{{ $product->earn_point }}" step="1" placeholder="{{ ('1') }}" name="earn_point" class="form-control">
                                </div>
                            </div>
                        @endif

                        <div id="show-hide-div">
                            <div class="form-group row d-none" id="quantity">
                                <label class="col-lg-3 col-from-label">{{ ('Quantity')}}</label>
                                <div class="col-lg-6">
                                    <input type="number" lang="en" value="{{ optional($product->stocks->first())->qty }}" step="1" placeholder="{{ ('Quantity')}}" name="current_stock" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">
                                    {{ ('SKU')}}
                                </label>
                                <div class="col-md-6">
                                    <input type="text" placeholder="{{ ('SKU') }}" value="{{ optional($product->stocks->first())->sku }}" name="sku" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">
                                {{ ('External link')}}
                            </label>
                            <div class="col-md-9">
                                <input type="text" placeholder="{{ ('External link') }}" name="external_link" value="{{ $product->external_link }}" class="form-control">
                                <small class="text-muted">{{ ('Leave it blank if you do not use external site link')}}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">
                                {{ ('External link button text')}}
                            </label>
                            <div class="col-md-9">
                                <input type="text" placeholder="{{ ('External link button text') }}" name="external_link_btn" value="{{ $product->external_link_btn }}" class="form-control">
                                <small class="text-muted">{{ ('Leave it blank if you do not use external site link')}}</small>
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
                        <h5 class="mb-0 h6">{{ ('Product Price Break Down')}}</h5>
                    </div>
                    <div class="card-body audit-form">
                        <small class="text-danger">Note: Price Breakdown will not work for subscription products.</small>
                        <table class="table table-bordered table-striped mobile_no_border" style="margin-bottom: 5px;margin-top: 15px;">
                            <tbody class="appendAuditTr">
                                @php
                                    if(count($product->productprices)>0):
                                    foreach($product->productprices as $price):
                                @endphp
                                <tr>
                                    <td>
                                        <input type="text" placeholder="{{ ('Start Quantity')}}" name="start_qty[]" class="form-control" value="{{ $price->start_qty }}">
                                    </td>
                                    <td>
                                        <input type="text" placeholder="{{ ('End Quantity')}}" name="end_qty[]" class="form-control" value="{{ $price->end_qty }}">
                                    </td>
                                    <td>
                                        <input type="text" placeholder="{{ ('Price')}}" name="price[]" class="form-control" value="{{ $price->price }}">
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>
                                    </td>
                                </tr>
                                @php
                                    endforeach;
                                else:
                                @endphp
                                <tr>
                                    <td>
                                        <input type="text" placeholder="{{ ('Start Quantity')}}" name="start_qty[]" class="form-control" value="">
                                    </td>
                                    <td>
                                        <input type="text" placeholder="{{ ('End Quantity')}}" name="end_qty[]" class="form-control" value="">
                                    </td>
                                    <td>
                                        <input type="text" placeholder="{{ ('Price')}}" name="price[]" class="form-control" value="">
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>
                                    </td>
                                </tr>
                                @php
                                endif;
                                @endphp
                            </tbody>
                        </table>
                        <div style="text-align: right; float: right;">
                            <button type="button" class="btn btn-primary btn-xs addmore-audit"><i class="fa fa-plus"></i> Add More</button>
                        </div>
                    </div>
                </div>

                <!-- App Price Configs -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('App Price Configuration')}}</h5>
                    </div>
                    @php
                        $app_p_start_date = date('d-m-Y H:i:s', $product->app_discount_start_date);
                        $app_p_end_date = date('d-m-Y H:i:s', $product->app_discount_end_date);
                    @endphp
                    <div class="card-body">
                        <div class="form-group row">
	                        <label class="col-sm-3 control-label" for="start_date">{{ ('App Discount Date Range')}}</label>
	                        <div class="col-sm-9">
                                <input type="text" class="form-control aiz-date-range" @if($product->app_discount_start_date && $product->app_discount_end_date) value="{{ $app_p_start_date.' to '.$app_p_end_date }}" @endif name="app_discount_date_range" placeholder="{{ ('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
	                        </div>
	                    </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('App Discount')}} <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input type="number" lang="en" min="0" step="0.01" placeholder="{{ ('Discount')}}" name="app_discount" class="form-control" value="{{ $product->app_discount ?? 0 }}" required>
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control aiz-selectpicker" name="app_discount_type" required>
                                    <option value="amount" <?php if ($product->app_discount_type == 'amount') echo "selected"; ?> >{{ ('Flat')}}</option>
                                    <option value="percent" <?php if ($product->app_discount_type == 'percent') echo "selected"; ?> >{{ ('Percent')}}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Description -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Product Description')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Short Description')}} <i class="las la-language text-danger" title="{{ ('Translatable')}}"></i></label>
                            <div class="col-lg-9">
                                <textarea class="aiz-text-editor" name="short_description">{{ $product->getTranslation('short_description', $lang) }}</textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Description')}} <i class="las la-language text-danger" title="{{ ('Translatable')}}"></i></label>
                            <div class="col-lg-9">
                                <textarea class="aiz-text-editor" name="description">{{ $product->getTranslation('description', $lang) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('PDF Specification')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('PDF Specification')}}</label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="pdf" value="{{ $product->pdf }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('SEO Meta Tags')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Meta Title')}}</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control" name="meta_title" value="{{ $product->meta_title }}" placeholder="{{ ('Meta Title')}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-from-label">{{ ('Description')}}</label>
                            <div class="col-lg-8">
                                <textarea name="meta_description" rows="8" class="form-control">{{ $product->meta_description }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Meta Images')}}</label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="meta_img" value="{{ $product->meta_img }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ ('Slug')}}</label>
                            <div class="col-md-8">
                                <input type="text" placeholder="{{ ('Slug')}}" id="slug" name="slug" value="{{ $product->slug }}" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label class="col-from-label">{{ ('Rewrite Url')}}</label>
                            </div>
                            <div class="col-sm-9">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" id="rewrite_url" name="rewrite_url" type="checkbox">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                @if (count($customFields))
                    <!-- Product Custom Fields -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h6">{{ ('Custom Fields') }}</h5>
                        </div>
                        <div class="card-body">
                            @foreach ($customFields as $key => $field)
                                @php
                                    if(is_array(json_decode(data_get($field, 'fields_data.value', ''), true))){
                                        // $selected = implode(',',json_decode(data_get($field, 'fields_data.value', ''), true));
                                        $selected = data_get($field, 'fields_data.value', '');
                                    }else{
                                        $selected = json_decode(data_get($field, 'fields_data.value', ''), true);
                                    }
                                @endphp
                                <div class="form-group row">
                                    <label class="col-md-3 col-from-label">{{ ($field['name']) }}</label>
                                    <div class="col-md-7">
                                        @if (strtolower($field['type']) == 'html_box')
                                            <input type="hidden" id="{{ str_replace(' ','_',$field['name']).'_'.$key.'_meta' }}" name="{{ str_replace(' ','_',$field['name']).'_meta' }}" value="{{ data_get($field, 'fields_data.meta_object.id') }}">
                                            <textarea class="aiz-text-editor" name="{{ str_replace(' ','_',$field['name']).'_dynamic' }}" id="{{ str_replace(' ','_',$field['name']).'_'.$key }}">{{ $selected }}</textarea>
                                        @elseif(strtolower($field['type']) == 'single_text_box')
                                            <input type="hidden" id="{{ str_replace(' ','_',$field['name']).'_'.$key.'_meta' }}" name="{{ str_replace(' ','_',$field['name']).'_meta' }}" value="{{ data_get($field, 'fields_data.meta_object.id') }}">
                                            <input type="text" class="form-control" name="{{ str_replace(' ','_',$field['name']).'_dynamic' }}" id="{{ str_replace(' ','_',$field['name']).'_'.$key }}" value="{{ $selected }}">
                                        @elseif(strtolower($field['type']) == 'multi_text_box')
                                            <input type="hidden" id="{{ str_replace(' ','_',$field['name']).'_'.$key.'_meta' }}" name="{{ str_replace(' ','_',$field['name']).'_meta' }}" value="{{ data_get($field, 'fields_data.meta_object.id') }}">
                                            <textarea name="{{ str_replace(' ','_',$field['name']).'_dynamic' }}" id="{{ str_replace(' ','_',$field['name']).'_'.$key }}" class="form-control" rows="3">{{ $selected }}</textarea>
                                        @elseif(strtolower($field['type']) == 'single_select')
                                            <input type="hidden" id="{{ str_replace(' ','_',$field['name']).'_'.$key.'_meta' }}" name="{{ str_replace(' ','_',$field['name']).'_meta' }}" value="{{ data_get($field, 'fields_data.meta_object.id') }}">
                                            <select class="form-control aiz-selectpicker" name="{{ str_replace(' ','_',$field['name']).'_dynamic' }}" data-live-search="true" id="{{ str_replace(' ','_',$field['name']).'_'.$key }}" @if(filled($selected)) data-selected="{{ $selected }}" @endif>
                                                @if(data_get($field, 'fields_data.meta_object.id') && count(data_get($field, 'fields_data.meta_object.items', [])))
                                                    @foreach (data_get($field, 'fields_data.meta_object.items', []) as $option)
                                                        <option value="{{ $option['id'] }}">{{ $option['title'] }}</option>
                                                    @endforeach
                                                @else
                                                    <option value="" disabled>Connect to a meta group</option>
                                                @endif
                                            </select>
                                        @elseif(strtolower($field['type']) == 'multi_select')
                                            <input type="hidden" id="{{ str_replace(' ','_',$field['name']).'_'.$key.'_meta' }}" name="{{ str_replace(' ','_',$field['name']).'_meta' }}" value="{{ data_get($field, 'fields_data.meta_object.id') }}">
                                            <select class="form-control aiz-selectpicker" name="{{ str_replace(' ','_',$field['name']).'_dynamic' }}[]" id="{{ str_replace(' ','_',$field['name']).'_'.$key }}" @if(filled($selected)) data-selected="{{ $selected }}" @endif data-live-search="true" multiple>
                                                @if(data_get($field, 'fields_data.meta_object.id') && count(data_get($field, 'fields_data.meta_object.items', [])))
                                                    @foreach (data_get($field, 'fields_data.meta_object.items', []) as $option)
                                                        <option value="{{ $option['id'] }}">{{ $option['title'] }}</option>
                                                    @endforeach
                                                @else
                                                    <option value="" disabled>Connect to a meta group</option>
                                                @endif
                                            </select>
                                        @endif
                                    </div>
                                    <div class="col-md-2">
                                        @if (strtolower($field['type']) == 'single_select' || strtolower($field['type']) == 'multi_select')
                                            <span role="button" id="{{ str_replace(' ','_',$field['name']).'_'.$key.'_'.'Icon' }}" class="connect btn @if(data_get($field, 'fields_data.meta_object.id') && count(data_get($field, 'fields_data.meta_object.items', []))) text-success @endif" data-connected="@if(data_get($field, 'fields_data.meta_object.id') && count(data_get($field, 'fields_data.meta_object.items', []))) true @else false @endif" title="@if(data_get($field, 'fields_data.meta_object.id') && count(data_get($field, 'fields_data.meta_object.items', []))) Connected To {{ data_get($field, 'fields_data.meta_object.name') }} @else Connect To Meta @endif" data-ref="{{ str_replace(' ','_',$field['name']).'_'.$key }}">
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

                {{-- Product Note --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Product Note')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="name">
                                {{ ('Note About This Product')}}
                            </label>
                            <textarea name="note" rows="2" class="form-control">{{ $product->note }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Pre-order Configurations --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">
                            {{ ('Pre-order Configuration')}}
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-4 col-from-label">{{ ('Pre-Order')}}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" name="pre_order" value="1" @if($product->pre_order == 1) checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        @php
                          $preorderstart_date = date('d-m-Y H:i:s', $product->preorder_start_date);
                          $preorderend_date = date('d-m-Y H:i:s', $product->preorder_end_date);
                        @endphp

                        <div class="form-group row">
                            <label class="col-md-4 control-label" for="start_date">{{ ('Pre-order Date Range')}}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control aiz-date-range" name="preorder_date_range" @if($product->preorder_start_date && $product->preorder_end_date) value="{{ $preorderstart_date.' to '.$preorderend_date }}" @endif placeholder="{{ ('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-from-label">{{ ('Max Pre-Order Qty')}} <span class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <input type="number" lang="en" class="form-control" name="maxpreorderqty" value="{{ $product->preorder_max_qty }}" min="0" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-from-label">{{ ('Pre-Order Qty Left')}}</label>
                            <div class="col-md-8">
                                <input type="number" lang="en" class="form-control" name="maxpreorderqtyleft" value="{{ $product->preorder_max_qty - preorder_product_count($product) }}" min="0" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6" class="dropdown-toggle" data-toggle="collapse" data-target="#collapse_2">
                            {{ ('Shipping Configuration')}}
                        </h5>
                    </div>
                    <div class="card-body collapse show" id="collapse_2">
                        @if (get_setting('shipping_type') == 'product_wise_shipping')
                        <div class="form-group row">
                            <label class="col-lg-6 col-from-label">{{ ('Free Shipping')}}</label>
                            <div class="col-lg-6">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="radio" name="shipping_type" value="free" @if($product->shipping_type == 'free') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-6 col-from-label">{{ ('Flat Rate')}}</label>
                            <div class="col-lg-6">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="radio" name="shipping_type" value="flat_rate" @if($product->shipping_type == 'flat_rate') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="flat_rate_shipping_div" style="display: none">
                            <div class="form-group row">
                                <label class="col-lg-6 col-from-label">{{ ('Shipping cost')}}</label>
                                <div class="col-lg-6">
                                    <input type="number" lang="en" min="0" value="{{ $product->shipping_cost }}" step="0.01" placeholder="{{ ('Shipping cost') }}" name="flat_shipping_cost" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-6 col-from-label">{{ ('Is Product Quantity Mulitiply')}}</label>
                            <div class="col-md-6">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" name="is_quantity_multiplied" value="1" @if($product->is_quantity_multiplied == 1) checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        @else
                        <p>
                            {{ ('Product wise shipping cost is disable. Shipping cost is configured from here') }}
                            <a href="{{route('shipping_configuration.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping_configuration.index','shipping_configuration.edit','shipping_configuration.update'])}}">
                                <span class="aiz-side-nav-text">{{ ('Shipping Configuration')}}</span>
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
                            <input type="number" name="usage_duration" value="{{ $product->usage_duration ?? '' }}" min="0" step="1" class="form-control" placeholder="{{ ('Enter Usage Duration in days') }}">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Low Stock Quantity Warning')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="name">
                                {{ ('Quantity')}}
                            </label>
                            <input type="number" name="low_stock_quantity" value="{{ $product->low_stock_quantity }}" min="0" step="1" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">
                            {{ ('Stock Visibility State')}}
                        </h5>
                    </div>

                    <div class="card-body">

                        <div class="form-group row">
                            <label class="col-md-6 col-from-label">{{ ('Show Stock Quantity')}}</label>
                            <div class="col-md-6">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="radio" name="stock_visibility_state" value="quantity" @if($product->stock_visibility_state == 'quantity') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-6 col-from-label">{{ ('Show Stock With Text Only')}}</label>
                            <div class="col-md-6">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="radio" name="stock_visibility_state" value="text" @if($product->stock_visibility_state == 'text') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-6 col-from-label">{{ ('Hide Stock')}}</label>
                            <div class="col-md-6">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="radio" name="stock_visibility_state" value="hide" @if($product->stock_visibility_state == 'hide') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-6 col-from-label">{{ ('Continue selling when out of stock')}}</label>
                            <div class="col-md-6">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" name="allow_out_Of_stock_purchases" value="1" @if($product->allow_stock_out_purchases == 1) checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Cash On Delivery')}}</h5>
                    </div>
                    <div class="card-body">
                        @if (get_setting('cash_payment') == '1')
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-md-6 col-from-label">{{ ('Status')}}</label>
                                    <div class="col-md-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="checkbox" name="cash_on_delivery" value="1" @if($product->cash_on_delivery == 1) checked @endif>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                            <p>
                                {{ ('Cash On Delivery option is disabled. Activate this feature from here') }}
                                <a href="{{route('activation.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping_configuration.index','shipping_configuration.edit','shipping_configuration.update'])}}">
                                    <span class="aiz-side-nav-text">{{ ('Cash Payment Activation')}}</span>
                                </a>
                            </p>
                        @endif
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Subscription')}}</h5>
                    </div>
                    <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-6 col-from-label">{{ ('Status')}}</label>
                                <div class="col-md-6">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" name="subscription" value="1" @if($product->subscription == 1) checked @endif>
                                        <span></span>
                                    </label>
                                </div>
                            </div>

                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Featured')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-md-6 col-from-label">{{ ('Status')}}</label>
                                    <div class="col-md-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="checkbox" name="featured" value="1" @if($product->featured == 1) checked @endif>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Todays Deal')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-md-6 col-from-label">{{ ('Status')}}</label>
                                    <div class="col-md-6">
                                        <label class="aiz-switch aiz-switch-success mb-0">
                                            <input type="checkbox" name="todays_deal" value="1" @if($product->todays_deal == 1) checked @endif>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Flash Deal')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="name">
                                {{ ('Add To Flash')}}
                            </label>
                            <select class="form-control aiz-selectpicker" name="flash_deal_id" id="video_provider">
                                <option value="">Choose Flash Title</option>
                                @foreach(\App\Models\FlashDeal::where("status", 1)->get() as $flash_deal)
                                    <option value="{{ $flash_deal->id}}" @if($product->flash_deal_product && $product->flash_deal_product->flash_deal_id == $flash_deal->id) selected @endif>
                                        {{ $flash_deal->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="name">
                                {{ ('Discount')}}
                            </label>
                            <input type="number" name="flash_discount" value="{{$product->flash_deal_product ? $product->flash_deal_product->discount : '0'}}" min="0" step="1" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="name">
                                {{ ('Discount Type')}}
                            </label>
                            <select class="form-control aiz-selectpicker" name="flash_discount_type" id="">
                                <option value="">Choose Discount Type</option>
                                <option value="amount" @if($product->flash_deal_product && $product->flash_deal_product->discount_type == 'amount') selected @endif>
                                    {{ ('Flat')}}
                                </option>
                                <option value="percent" @if($product->flash_deal_product && $product->flash_deal_product->discount_type == 'percent') selected @endif>
                                    {{ ('Percent')}}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Estimate Shipping Time')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="name">
                                {{ ('Shipping Days')}}
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="est_shipping_days" value="{{ $product->est_shipping_days }}" min="1" step="1" placeholder="{{ ('Shipping Days')}}">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="inputGroupPrepend">{{ ('Days')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('VAT & Tax')}}</h5>
                    </div>
                    <div class="card-body">
                        @foreach(\App\Models\Tax::where('tax_status', 1)->get() as $tax)
                        <label for="name">
                            {{$tax->name}}
                            <input type="hidden" value="{{$tax->id}}" name="tax_id[]">
                        </label>

                        @php
                        $tax_amount = 0;
                        $tax_type = '';
                        foreach($tax->product_taxes as $row) {
                            if($product->id == $row->product_id) {
                                $tax_amount = $row->tax;
                                $tax_type = $row->tax_type;
                            }
                        }
                        @endphp

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <input type="number" lang="en" min="0" value="{{ $tax_amount }}" step="0.01" placeholder="{{ ('Tax') }}" name="tax[]" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <select class="form-control aiz-selectpicker" name="tax_type[]">
                                    <option value="amount" @if($tax_type == 'amount') selected @endif>
                                        {{ ('Flat')}}
                                    </option>
                                    <option value="percent" @if($tax_type == 'percent') selected @endif>
                                        {{ ('Percent')}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="col-12">
                <div class="mb-3 text-right">
                    <button type="submit" name="button" class="btn btn-info">{{ ('Update Product') }}</button>
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
                if (e.which === 13) {
                    e.preventDefault();
                    if ($(this).val().length > 0) {
                        console.log('Barcode scanned:', $(this).val());
                    }
                }
            });
        });
    </script>
    {{-- Barcode Scanner Script End --}}

<script type="text/javascript">
    let old_slug = "{{ $product->slug }}";

    $('#name').on('input', function() {
        let name = $(this).val().trim();
        if (name) {
            $('#slug').val(name.toLowerCase().replace(/[^a-z0-9]+/g, '-'));
        } else {
            $('#slug').val('');
        }

        if (old_slug != $('#slug').val()) {
            $('#rewrite_url').prop('checked', true);
        } else {
            $('#rewrite_url').prop('checked', false);
        }
    });

    $('#slug').on('keyup', function() {
        var slug = $(this).val();
        if (old_slug != slug) {
            $('#rewrite_url').prop('checked', true);
        }else{
            $('#rewrite_url').prop('checked', false);
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
                    var html = ``;
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
    $(document).ready(function (){
        var rowCount = $('.audit-form >table >tbody >tr').length;
        if(rowCount==1){
          $( ".audit-form >table >tbody >tr:first >td:last" ).html('');
      }else{
        $( ".audit-form >table >tbody >tr:first >td:last" ).html('<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>');
        $( ".audit-form >table >tbody >tr >td:last" ).html('<a href="javascript:;" id="" class="btn btn-xs btn-danger removeTr"><i class="las la-trash"></i></a>');
    }
        show_hide_shipping_div();
    });

    $("[name=shipping_type]").on("change", function (){
        show_hide_shipping_div();
    });

    function show_hide_shipping_div() {
        var shipping_val = $("[name=shipping_type]:checked").val();

        $(".flat_rate_shipping_div").hide();

        if(shipping_val == 'flat_rate'){
            $(".flat_rate_shipping_div").show();
        }
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
        if(!$('input[name="colors_active"]').is(':checked')){
            $('#colors').prop('disabled', true);
            AIZ.plugins.bootstrapSelect('refresh');
        }
        else{
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

    function delete_row(em){
        $(em).closest('.form-group').remove();
        update_sku();
    }

    function delete_variant(em){
        $(em).closest('.variant').remove();
    }

    function update_sku(){
        $.ajax({
           type:"POST",
           url:'{{ route('products.sku_combination_edit') }}',
           data:$('#choice_form').serialize(),
           success: function(data){
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

    AIZ.plugins.tagify();

    $(document).ready(function(){
        update_sku();

        $('.remove-files').on('click', function(){
            $(this).parents(".col-md-4").remove();
        });
    });

    $('#choice_attributes').on('change', function() {
        $.each($("#choice_attributes option:selected"), function(j, attribute){
            flag = false;
            $('input[name="choice_no[]"]').each(function(i, choice_no) {
                if($(attribute).val() == $(choice_no).val()){
                    flag = true;
                }
            });
            if(!flag){
                add_more_customer_choice_option($(attribute).val(), $(attribute).text());
            }
        });

        var str = @php echo $product->attributes @endphp;

        $.each(str, function(index, value){
            flag = false;
            $.each($("#choice_attributes option:selected"), function(j, attribute){
                if(value == $(attribute).val()){
                    flag = true;
                }
            });
            if(!flag){
                $('input[name="choice_no[]"][value="'+value+'"]').parent().parent().remove();
            }
        });

        update_sku();
    });

    $('#category_id').on('change', function() {
        var product_choice_option = '{{count(json_decode($product->choice_options))}}';
        if(product_choice_option==0){
        var cat_attribute = $("#category_id option:selected").attr('data-catattribute');
        var cat_color = $("#category_id option:selected").attr('data-catcolor');
        $('#customer_choice_options').html(null);
        $('input[name="colors_active"]').removeAttr('checked')
        $('#colors').prop('disabled', true);
        $('input[name="colors_active"]').trigger('change');
        $("#choice_attributes option").removeAttr('selected');
        make_category_variation(cat_attribute, cat_color);
        AIZ.plugins.bootstrapSelect('refresh');
        }
    });
function make_category_variation(cat_attribute, cat_color){
    if(cat_attribute!=''){
            var idarray = cat_attribute.split(',');
           //console.log(jQuery.inArray(2, idarray));
            //alert(idarray);
        $.each($("#choice_attributes option"), function(){
           if(jQuery.inArray($(this).val(), idarray)!==-1){
                $(this).attr('selected','selected');
           }
        });
        $('#choice_attributes').trigger('change');
        AIZ.plugins.bootstrapSelect('refresh');
        //update_sku();
    }
    if(cat_color==1){
            $('input[name="colors_active"]').attr('checked','checked')
            $('#colors').prop('disabled', false);
            $('input[name="colors_active"]').trigger('change');
            AIZ.plugins.bootstrapSelect('refresh');
    }
}

</script>

@endsection
