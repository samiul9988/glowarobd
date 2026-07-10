@extends('backend.layouts.app')
@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h6 class="fw-600 mb-0">{{ ('General') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Frontend Website Name') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="website_name">
                                <input type="text" name="website_name" class="form-control"
                                    placeholder="{{ ('Website Name') }}" value="{{ get_setting('website_name') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Site Motto') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="site_motto">
                                <input type="text" name="site_motto" class="form-control"
                                    placeholder="{{ ('Best eCommerce Website') }}"
                                    value="{{ get_setting('site_motto') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Site Icon') }}</label>
                            <div class="col-md-8">
                                <div class="input-group " data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="types[]" value="site_icon">
                                    <input type="hidden" name="site_icon" value="{{ get_setting('site_icon') }}"
                                        class="selected-files">
                                </div>
                                <div class="file-preview box"></div>
                                <small class="text-muted">{{ ('Website favicon. 32x32 .png') }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Website Base Color') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="base_color">
                                <input type="text" name="base_color" class="form-control" placeholder="#377dff"
                                    value="{{ get_setting('base_color') }}">
                                <small class="text-muted">{{ ('Hex Color Code') }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Website Base Hover Color') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="base_hov_color">
                                <input type="text" name="base_hov_color" class="form-control" placeholder="#377dff"
                                    value="{{ get_setting('base_hov_color') }}">
                                <small class="text-muted">{{ ('Hex Color Code') }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Order Lock Duration') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="order_lock_duration">
                                <input type="text" name="order_lock_duration" class="form-control" placeholder="Enter a number in minutes" value="{{ get_setting('order_lock_duration') }}">
                                <small class="text-muted">{{ ('Calculate In Minute/s') }}</small>
                            </div>
                        </div>
                        @if(get_setting('enable_product_expire_date') == 1)
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Expire Products Alert Before (In Days)') }}</label>
                                <div class="col-md-8">
                                    <input type="hidden" name="types[]" value="expire_products_alert_duration">
                                    <input type="text" name="expire_products_alert_duration" class="form-control" placeholder="How many days before you want to get alert e.g. 7" value="{{ get_setting('expire_products_alert_duration') }}">
                                    <small class="text-muted">{{ ('Default 7 Days') }}</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{ ('Show Expire Products Alert') }}</label>
                                <div class="col-md-8">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="hidden" name="types[]" value="enable_expire_products_alert">
                                        <input type="checkbox" name="enable_expire_products_alert"
                                            @if (get_setting('enable_expire_products_alert') == 'on') checked @endif>
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        @endif
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Additional Charge') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="additional_charge">
                                <input type="text" name="additional_charge" class="form-control" placeholder="Enter additional charge in percent e.g. 10 means 10%" value="{{ get_setting('additional_charge') }}">
                                <small class="text-muted">{{ ('This will add additional charge with all purchase products price when print label') }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Enable/Disable Snow Effect') }}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="enable_snow_effect">
                                    <input type="checkbox" name="enable_snow_effect"
                                        @if (get_setting('enable_snow_effect') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Convert Images To Webp') }}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="convert_images_to_webp">
                                    <input type="checkbox" name="convert_images_to_webp"
                                        @if (get_setting('convert_images_to_webp', 0)) checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">
                                Compress Videos
                                @include('components.tooltip', [
                                    'title' => 'If enabled, uploaded videos will be compressed to reduce file size and improve loading times.',
                                ])
                            </label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="compress_videos">
                                    <input type="checkbox" name="compress_videos" value="1"
                                        @if (get_setting('compress_videos', 0)) checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">
                                Delete Original Video <br> After Compression
                                @include('components.tooltip', [
                                    'title' => 'If enabled, the original video will be deleted after compression to save storage space.',
                                ])
                            </label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="delete_original_video_after_compression">
                                    <input type="checkbox" name="delete_original_video_after_compression" value="1"
                                        @if (get_setting('delete_original_video_after_compression', 0)) checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">
                                Max File Size
                                @include('components.tooltip', [
                                    'title' => 'Set the maximum file(images/docs) size for uploads in MB, By default it is 5 MB',
                                ])
                            </label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="max_file_size">
                                <input type="text" name="max_file_size" class="form-control" placeholder="Enter max file size in MB" value="{{ get_setting('max_file_size') }}">
                                <small class="text-muted">This will set the maximum file(images/docs) size for uploads</small>
                            </div>
                        </div>
                        <div class="form-group row" id="product-visit-log-settings">
                            <label class="col-md-3 col-from-label">
                                Max Video Size
                                @include('components.tooltip', [
                                    'title' => 'Set the maximum video size for uploads in MB, By default it is 15 MB',
                                ])
                            </label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="max_video_size">
                                <input type="text" name="max_video_size" class="form-control" placeholder="Enter max video size in MB" value="{{ get_setting('max_video_size') }}">
                                <small class="text-muted">This will set the maximum video size for uploads</small>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="fw-600 mb-0">Product Visit Logs</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">Retain product visit logs forever?</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="retain_product_visit_logs_forever">
                                    <input type="checkbox" name="retain_product_visit_logs_forever" value="1"
                                        @if (get_setting('retain_product_visit_logs_forever', 0) == 1) checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">
                                Retain product visit logs for how many months?
                                @include('components.tooltip', [
                                    'title' => 'Specify the number of months to retain product visit logs if not retaining forever.',
                                ])
                            </label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="retain_product_visit_logs_months">
                                <input type="text" name="retain_product_visit_logs_months" class="form-control" value="{{ get_setting('retain_product_visit_logs_months') }}" placeholder="By default it is 12 months">
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- This card is added for create Predefined Collection Design
            <div class="card">
                <div class="card-header">
                    <h6 class="fw-600 mb-0">{{ ('Collection Design') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('collection-design.store') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Design Name') }}</label>
                            <div class="col-md-8">
                                <input type="text" name="design_name" class="form-control"
                                    placeholder="{{ ('Design Name') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Design Image') }}</label>
                            <div class="col-md-8">
                                <div class="input-group " data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="design_image" value="{{ get_setting('site_icon') }}"
                                        class="selected-files">
                                </div>
                                <div class="file-preview box"></div>
                                <small class="text-muted">{{ ('Design Image. 32x32 .png') }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Design File Name') }}</label>
                            <div class="col-md-8">
                                <input type="text" name="design_file_name" class="form-control"
                                    placeholder="Filename" required>
                                <small
                                    class="text-muted">{{ ('Blade file name of the design component') }}</small>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Create') }}</button>
                        </div>
                    </form>
                </div>
            </div> --}}


            <div class="card">
                <div class="card-header">
                    <h6 class="fw-600 mb-0">{{ ('Global SEO') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Meta Title') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="meta_title">
                                <input type="text" class="form-control" placeholder="{{ ('Title') }}"
                                    name="meta_title" value="{{ get_setting('meta_title') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Meta description') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="meta_description">
                                <textarea class="resize-off form-control" placeholder="{{ ('Description') }}" name="meta_description">{{ get_setting('meta_description') }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Keywords') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="meta_keywords">
                                <textarea class="resize-off form-control" placeholder="{{ ('Keyword, Keyword') }}" name="meta_keywords">{{ get_setting('meta_keywords') }}</textarea>
                                <small class="text-muted">{{ ('Separate with coma') }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Meta Image') }}</label>
                            <div class="col-md-8">
                                <div class="input-group " data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="types[]" value="meta_image">
                                    <input type="hidden" name="meta_image" value="{{ get_setting('meta_image') }}"
                                        class="selected-files">
                                </div>
                                <div class="file-preview box"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="fw-600 mb-0">{{ ('Cookies Agreement') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Cookies Agreement Text') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="cookies_agreement_text">
                                <textarea name="cookies_agreement_text" rows="4" class="aiz-text-editor form-control"
                                    data-buttons='[["font", ["bold"]],["insert", ["link"]]]'>{{ get_setting('cookies_agreement_text') }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Show Cookies Agreement?') }}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="show_cookies_agreement">
                                    <input type="checkbox" name="show_cookies_agreement"
                                        @if (get_setting('show_cookies_agreement') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="fw-600 mb-0">Website Popup</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Show Web popup?') }}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="show_website_popup">
                                    <input type="checkbox" name="show_website_popup"
                                        @if (get_setting('show_website_popup') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Popup Content For') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="web_popup_content_for">
                                <select name="web_popup_content_for" id="content_for_web" class="form-control content_for_web">
                                    <option value="">Select Any</option>
                                    <option @if (get_setting('web_popup_content_for') == 'Product') selected @endif value="Product">Product
                                    </option>
                                    <option @if (get_setting('web_popup_content_for') == 'Category') selected @endif value="Category">Category
                                    </option>
                                    <option @if (get_setting('web_popup_content_for') == 'Brand') selected @endif value="Brand">Brand</option>
                                    <option @if (get_setting('web_popup_content_for') == 'Flash Deal') selected @endif value="Flash Deal">Flash Deal
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row product-web" style="display: none;">
                            @php
                                $webPopupProductId = get_setting('web_popup_product_id')
                            @endphp
                            <label class="col-md-3 col-from-label">Product</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="web_popup_product_id">
                                <select class="form-control aiz-selectpicker" name="web_popup_product_id"
                                    id="web_popup_product_id" data-live-search="true">
                                    <option value="">{{ ('Select Product') }}</option>
                                    @foreach ($products as $pid => $pname)
                                        <option @if ($webPopupProductId == $pid) selected @endif
                                            value="{{ $pid }}">{{ $pname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row category-web" style="display: none;">
                            <label class="col-md-3 col-from-label">{{ ('Category') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="web_popup_category_id">
                                <select class="select2 form-control aiz-selectpicker" name="web_popup_category_id"
                                    data-toggle="select2" data-placeholder="Choose ..." data-live-search="true">
                                    <option value="">{{ ('Select Category') }}</option>
                                    @foreach ($categories as $category)
                                        <option @if (get_setting('web_popup_category_id') == $category->id) selected @endif
                                            value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                        @foreach ($category->childrenCategories as $childCategory)
                                            @include('categories.child_category', [
                                                'child_category' => $childCategory,
                                            ])
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row brand-web" style="display: none;">
                            @php
                                $webPopupBrandId = get_setting('web_popup_brand_id');
                            @endphp
                            <label class="col-md-3 col-from-label">Brand</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="web_popup_brand_id">
                                <select class="form-control aiz-selectpicker" name="web_popup_brand_id"
                                    id="web_popup_brand_id" data-live-search="true">
                                    <option value="">{{ ('Select Brand') }}</option>
                                    @foreach ($brands as $bid => $bname)
                                        <option @if ($webPopupBrandId == $bid) selected @endif
                                            value="{{ $bid }}">{{ $bname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row flash_deal-web" style="display:none;">
                            @php
                                $webPopupFlashDealId = get_setting('web_popup_flash_deal_id');
                            @endphp
                            <label class="col-md-3 col-from-label">Flash Deal</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="web_popup_flash_deal_id">
                                <select class="form-control aiz-selectpicker" name="web_popup_flash_deal_id"
                                    id="web_popup_flash_deal_id" data-live-search="true">
                                    <option value="">{{ ('Select Flash Deal') }}</option>
                                    @foreach ($flash_deals as $fid => $ftitle)
                                        <option @if ($webPopupFlashDealId == $fid) selected @endif
                                            value="{{ $fid }}">{{ $ftitle }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Content Image') }}</label>
                            <div class="col-md-8">
                                <div class="input-group " data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="types[]" value="web_popup_image">
                                    <input type="hidden" name="web_popup_image"
                                        value="{{ get_setting('web_popup_image') }}" class="selected-files">
                                </div>
                                <div class="file-preview box"></div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Show Subscriber form?') }}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="show_subscribe_form">
                                    <input type="checkbox" name="show_subscribe_form"
                                        @if (get_setting('show_subscribe_form') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="fw-600 mb-0">{{ ('App Popup') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Show App popup?') }}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="app_popup_show">
                                    <input type="checkbox" name="app_popup_show"
                                        @if (get_setting('app_popup_show') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Popup Content For') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="app_popup_content_for">
                                <select name="app_popup_content_for" id="" class="form-control content_for">
                                    <option value="">Select Any</option>
                                    <option @if (get_setting('app_popup_content_for') == 'Product') selected @endif value="Product">Product
                                    </option>
                                    <option @if (get_setting('app_popup_content_for') == 'Category') selected @endif value="Category">Category
                                    </option>
                                    <option @if (get_setting('app_popup_content_for') == 'Brand') selected @endif value="Brand">Brand</option>
                                    <option @if (get_setting('app_popup_content_for') == 'Flash Deal') selected @endif value="Flash Deal">Flash Deal
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row product" style="display: none;">
                            @php
                                $appPopupProductId = get_setting('app_popup_product_id')
                            @endphp
                            <label class="col-md-3 col-from-label">Product</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="app_popup_product_id">
                                <select class="form-control aiz-selectpicker" name="app_popup_product_id"
                                    id="app_popup_product_id" data-live-search="true">
                                    <option value="">{{ ('Select Product') }}</option>
                                    @foreach ($products as $pid => $pname)
                                        <option @if ($appPopupProductId == $pid) selected @endif
                                            value="{{ $pid }}">{{ $pname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row category" style="display: none;">
                            <label class="col-md-3 col-from-label">{{ ('Category') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="app_popup_category_id">
                                <select class="select2 form-control aiz-selectpicker" name="app_popup_category_id"
                                    data-toggle="select2" data-placeholder="Choose ..." data-live-search="true">
                                    <option value="">{{ ('Select Category') }}</option>
                                    @foreach ($categories as $category)
                                        <option @if (get_setting('app_popup_category_id') == $category->id) selected @endif
                                            value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                        @foreach ($category->childrenCategories as $childCategory)
                                            @include('categories.child_category', [
                                                'child_category' => $childCategory,
                                            ])
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row brand" style="display: none;">
                            @php
                                $appPopupBrandId = get_setting('app_popup_brand_id');
                            @endphp
                            <label class="col-md-3 col-from-label">Brand</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="app_popup_brand_id">
                                <select class="form-control aiz-selectpicker" name="app_popup_brand_id"
                                    id="app_popup_brand_id" data-live-search="true">
                                    <option value="">{{ ('Select Brand') }}</option>
                                    @foreach ($brands as $bid => $bname)
                                        <option @if ($appPopupBrandId == $bid) selected @endif
                                            value="{{ $bid }}">{{ $bname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row flash_deal" style="display:none;">
                            @php
                                $appPopupFlashDealId = get_setting('app_popup_flash_deal_id');
                            @endphp
                            <label class="col-md-3 col-from-label">Flash Deal</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="app_popup_flash_deal_id">
                                <select class="form-control aiz-selectpicker" name="app_popup_flash_deal_id"
                                    id="app_popup_flash_deal_id" data-live-search="true">
                                    <option value="">{{ ('Select Flash Deal') }}</option>
                                    @foreach ($flash_deals as $fid => $ftitle)
                                        <option @if ($appPopupFlashDealId == $fid) selected @endif
                                            value="{{ $fid }}">{{ $ftitle }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Content Image') }}</label>
                            <div class="col-md-8">
                                <div class="input-group " data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="types[]" value="app_popup_image">
                                    <input type="hidden" name="app_popup_image"
                                        value="{{ get_setting('app_popup_image') }}" class="selected-files">
                                </div>
                                <div class="file-preview box"></div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Show Subscriber form?') }}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="app_popup_show_subscribe_form">
                                    <input type="checkbox" name="app_popup_show_subscribe_form"
                                        @if (get_setting('app_popup_show_subscribe_form') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="fw-600 mb-0">{{ ('Custom Script') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label
                                class="col-md-3 col-from-label">{{ ('Header custom script - before </head>') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="header_script">
                                <textarea name="header_script" rows="4" class="form-control" placeholder="<script>
                                    & #10;...&# 10;
                                </script>">{{ get_setting('header_script') }}</textarea>
                                <small>{{ ('Write script with <script> tag') }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label
                                class="col-md-3 col-from-label">{{ ('Footer custom script - before </body>') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="footer_script">
                                <textarea name="footer_script" rows="4" class="form-control" placeholder="<script>
                                    & #10;...&# 10;
                                </script>">{{ get_setting('footer_script') }}</textarea>
                                <small>{{ ('Write script with <script> tag') }}</small>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            var content = $('.content_for').val();
            check_popup_content(content);
            var content_web = $('#content_for_web').val();
            check_popup_content(content_web, '-web');
        });
        $(document).on('change', '.content_for_web', function() {
            var content = $(this).val();
            check_popup_content(content, '-web');
        });
        $(document).on('change', '.content_for', function() {
            var content = $(this).val();
            check_popup_content(content);
        });

        function check_popup_content(content, type = '') {
            console.log(type, content);
            if (content == 'Product') {
                $('.product' + type).show();
                $('.category' + type).hide();
                $('.brand' + type).hide();
                $('.flash_deal' + type).hide();
            } else if (content == 'Category') {
                $('.product' + type).hide();
                $('.category' + type).show();
                $('.brand' + type).hide();
                $('.flash_deal' + type).hide();
            } else if (content == 'Flash Deal') {
                $('.product' + type).hide();
                $('.category' + type).hide();
                $('.brand' + type).hide();
                $('.flash_deal' + type).show();
            } else if (content == 'Brand') {
                $('.product' + type).hide();
                $('.category' + type).hide();
                $('.brand' + type).show();
                $('.flash_deal' + type).hide();
            } else {
                $('.product' + type).hide();
                $('.category' + type).hide();
                $('.flash_deal' + type).hide();
                $('.brand' + type).hide();
            }
        }
    </script>
@endsection
