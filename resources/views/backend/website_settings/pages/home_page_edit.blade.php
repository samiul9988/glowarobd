@extends('backend.layouts.app')
@section('content')
@php
    $parentCategories = Cache::remember('parent_categories', now()->addHours(3), function () {
        return \App\Models\Category::where('parent_id', 0)->with('childrenCategories')->get();
    });
    $brands = Cache::remember('filter_brands', now()->addHours(3), function () {
        return \App\Models\Brand::pluck('name', 'id')->toArray();
    });
    $collectionDesigns = Cache::remember('collection_designs', now()->addHours(3), function () {
        return \App\Models\CollectionDesign::pluck('title', 'id')->toArray();
    });
    $categories = Cache::remember('filter_categories', now()->addDay(), function () {
        return \App\Models\Category::pluck('name', 'id')->toArray();
    });
@endphp
    <div class="row">
        <div class="col-xl-10 mx-auto">
            <h6 class="fw-600">{{ ('Home Page Settings') }}</h6>

            {{-- Home Banner Settings --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Enable/Disable Features') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="mega_menu_71">
                                <input type="checkbox" name="mega_menu_71[]" value="1"
                                    @if (@intval(json_decode(@get_setting('mega_menu_71'))) == 1) checked="checked" @endif> Mega Menu
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="left_category_71">
                                <input type="checkbox" name="left_category_71[]" value="1"
                                    @if (@intval(json_decode(@get_setting('left_category_71'))) == 1) checked="checked" @endif> Left Category
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="todays_deal_71">
                                <input type="checkbox" name="todays_deal_71[]" value="1"
                                    @if (@intval(json_decode(@get_setting('todays_deal_71'))) == 1) checked="checked" @endif> Today's Deal
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="featured_category_71">
                                <input type="checkbox" name="featured_category_71[]" value="1"
                                    @if (@intval(json_decode(@get_setting('featured_category_71'))) == 1) checked="checked" @endif> Featured Category On Desktop
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="featured_products_71">
                                <input type="checkbox" name="featured_products_71[]" value="1"
                                    @if (@intval(json_decode(@get_setting('featured_products_71'))) == 1) checked="checked" @endif> Featured Products
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="home_featured_videos">
                                <input type="checkbox" name="home_featured_videos[]" value="1"
                                    @if (@intval(json_decode(@get_setting('home_featured_videos'))) == 1) checked="checked" @endif> Featured Videos
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="show_doctors_consultation">
                                <input type="checkbox" name="show_doctors_consultation" value="1"
                                    @if (@intval(json_decode(@get_setting('show_doctors_consultation'))) == 1) checked="checked" @endif> <a href="#doctors-consultation" class="text-dark">Doctor's Consultation</a>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="show_highlighted_items">
                                <input type="checkbox" name="show_highlighted_items" value="1"
                                    @if (@intval(json_decode(@get_setting('show_highlighted_items'))) == 1) checked="checked" @endif> Highlighted Items
                                <a href="{{ route('highlightedProduct.index') }}" target="_blank">
                                    @include('components.tooltip', [
                                        'title' => 'Click here to set highlighted items',
                                        'class' => 'text-info fs-16',
                                        'icon' => 'la-external-link-alt'
                                    ])
                                </a>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="new_arrival_products">
                                <input type="checkbox" name="new_arrival_products" value="1"
                                    @if (@intval(json_decode(@get_setting('new_arrival_products'))) == 1) checked="checked" @endif>
                                    <a href="#new-arrival-products" class="text-dark">New Arrival Products</a>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="show_highlight_brand">
                                <input type="checkbox" name="show_highlight_brand" value="1"
                                    @if (@intval(json_decode(@get_setting('show_highlight_brand'))) == 1) checked="checked" @endif>
                                    <a href="#highlight-brand" class="text-dark">Highlight Brand</a>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="popular_products_71">
                                <input type="checkbox" name="popular_products_71[]" value="1"
                                    @if (@intval(json_decode(@get_setting('popular_products_71'))) == 1) checked="checked" @endif> Popular Products
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="best_selling_products_71">
                                <input type="checkbox" name="best_selling_products_71[]" value="1"
                                    @if (@intval(json_decode(@get_setting('best_selling_products_71'))) == 1) checked="checked" @endif> Best Selling Products
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="best_sellers_71">
                                <input type="checkbox" name="best_sellers_71[]" value="1"
                                    @if (@intval(json_decode(@get_setting('best_sellers_71'))) == 1) checked="checked" @endif> Best Sellers
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="topten_categories_71">
                                <input type="checkbox" name="topten_categories_71[]" value="1"
                                    @if (@intval(json_decode(@get_setting('topten_categories_71'))) == 1) checked="checked" @endif> Top 10 Categories
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="topten_brands_71">
                                <input type="checkbox" name="topten_brands_71[]" value="1"
                                    @if (@intval(json_decode(@get_setting('topten_brands_71'))) == 1) checked="checked" @endif> Top 10 Brands
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="terms_policy_71">
                                <input type="checkbox" name="terms_policy_71[]" value="1"
                                    @if (@intval(json_decode(@get_setting('terms_policy_71'))) == 1) checked="checked" @endif> Terms & Condition and Policy
                                on Footer
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="show_stock_out_products">
                                <input type="checkbox" name="show_stock_out_products[]" value="1"
                                    @if (@intval(json_decode(@get_setting('show_stock_out_products'))) == 1) checked="checked" @endif> Show Stock Out Products
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="show_customer_reviews">
                                <input type="checkbox" name="show_customer_reviews[]" value="1"
                                    @if (@intval(json_decode(@get_setting('show_customer_reviews'))) == 1) checked="checked" @endif> Show Customer Reviews
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Home Slider --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Home Slider') }}</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        {{ ('We have limited banner height to maintain UI. We had to crop from both left & right side in view for different devices to make it responsive. Before designing banner keep these points in mind.') }}
                    </div>
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">

                            <div class="home-slider-target">
                                <input type="hidden" name="types[]" value="home_slider_images">
                                <input type="hidden" name="types[]" value="home_slider_images_mobile">
                                <input type="hidden" name="types[]" value="home_slider_links">
                                @if (get_setting('home_slider_images') != null)
                                    @foreach (json_decode(get_setting('home_slider_images'), true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Web Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]" value="home_slider_images">
                                                        <input type="hidden" name="home_slider_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(get_setting('home_slider_images'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label for="">{{ ('Mobile Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]"
                                                            value="home_slider_images_mobile">
                                                        <input type="hidden" name="home_slider_images_mobile[]"
                                                            class="selected-files"
                                                            value="{{ @json_decode(get_setting('home_slider_images_mobile'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Links') }}</label>
                                                    <input type="hidden" name="types[]" value="home_slider_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_slider_links[]"
                                                        value="{{ json_decode(get_setting('home_slider_links'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Bg color') }}</label>
                                                    <input type="hidden" name="types[]" value="home_slider_bgcolors">
                                                    <input type="color" class="form-control" placeholder="http://"
                                                        name="home_slider_bgcolors[]"
                                                        value="{{ json_decode(get_setting('home_slider_bgcolors'), true)[$key] ?? '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group">
                                                    <br>
                                                    <button type="button"
                                                        class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                        data-toggle="remove-parent" data-parent=".row">
                                                        <i class="las la-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                data-content='
							<div class="row gutters-5">
								<div class="col-md">
									<div class="form-group">
										<label for="">{{ ('Web Image') }}</label>
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
											</div>
											<div class="form-control file-amount">{{ ('Choose File') }}</div>
											<input type="hidden" name="types[]" value="home_slider_images">
											<input type="hidden" name="home_slider_images[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
										<label for="">{{ ('Mobile Image') }}</label>
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
											</div>
											<div class="form-control file-amount">{{ ('Choose File') }}</div>
											<input type="hidden" name="types[]" value="home_slider_images_mobile">
											<input type="hidden" name="home_slider_images_mobile[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
										<label>{{ ('Links') }}</label>
										<input type="hidden" name="types[]" value="home_slider_links">
										<input type="text" class="form-control" placeholder="http://" name="home_slider_links[]">
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
										<label>Bg color</label>
										<input type="hidden" name="types[]" value="home_slider_bgcolors">
										<input type="color" class="form-control" placeholder="http://" name="home_slider_bgcolors[]" value="">
									</div>
								</div>
								<div class="col-md-auto">
									<br>
									<div class="form-group">
										<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
											<i class="las la-times"></i>
										</button>
									</div>
								</div>
							</div>'
                                data-target=".home-slider-target">
                                {{ ('Add New') }}
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
            {{-- End Home Slider --}}

            {{-- Home Banner 1 --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Home Banner 1 (Max 3)') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <!-- <label>{{ ('Banner & Links') }}</label> -->
                            <div class="home-banner1-target">
                                <input type="hidden" name="types[]" value="home_banner1_images">
                                <input type="hidden" name="types[]" value="home_banner1_links">
                                <input type="hidden" name="types[]" value="home_banner1_images_mobile">
                                <input type="hidden" name="types[]" value="home_banner1_links_mobile">
                                @if (get_setting('home_banner1_images') != null)
                                    @foreach (json_decode(get_setting('home_banner1_images'), true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]" value="home_banner1_images">
                                                        <input type="hidden" name="home_banner1_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(get_setting('home_banner1_images'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_banner1_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner1_links[]"
                                                        value="{{ json_decode(get_setting('home_banner1_links'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]" value="home_banner1_images_mobile">
                                                        <input type="hidden" name="home_banner1_images_mobile[]"
                                                            class="selected-files"
                                                            value="{{ @json_decode(get_setting('home_banner1_images_mobile'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_banner1_links_mobile">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner1_links_mobile[]"
                                                        value="{{ @json_decode(get_setting('home_banner1_links_mobile'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group pt-4">
                                                    <button type="button"
                                                        class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                        data-toggle="remove-parent" data-parent=".row">
                                                        <i class="las la-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                data-content='
							<div class="row gutters-5">
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Image') }}</label>
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
											</div>
											<div class="form-control file-amount">{{ ('Choose File') }}</div>
											<input type="hidden" name="types[]" value="home_banner1_images">
											<input type="hidden" name="home_banner1_images[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Link') }}</label>
										<input type="hidden" name="types[]" value="home_banner1_links">
										<input type="text" class="form-control" placeholder="http://" name="home_banner1_links[]">
									</div>
								</div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Image') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div
                                                    class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ ('Browse') }}</div>
                                            </div>
                                            <div class="form-control file-amount">
                                                {{ ('Choose File') }}</div>
                                            <input type="hidden" name="types[]" value="home_banner1_images_mobile">
                                            <input type="hidden" name="home_banner1_images_mobile[]"
                                                class="selected-files"
                                                value="{{ @json_decode(get_setting('home_banner1_images_mobile'), true)[$key] }}">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Link') }}</label>
                                        <input type="hidden" name="types[]" value="home_banner1_links_mobile">
                                        <input type="text" class="form-control" placeholder="http://"
                                            name="home_banner1_links_mobile[]"
                                            value="{{ @json_decode(get_setting('home_banner1_links_mobile'), true)[$key] }}">
                                    </div>
                                </div>
								<div class="col-md-auto">
									<div class="form-group pt-4">
										<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
											<i class="las la-times"></i>
										</button>
									</div>
								</div>
							</div>'
                                data-target=".home-banner1-target">
                                {{ ('Add New') }}
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Home Banner 2 --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Home Banner 2 (Max 3)') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <!-- <label>{{ ('Banner & Links') }}</label> -->
                            <div class="home-banner2-target">
                                <input type="hidden" name="types[]" value="home_banner2_images">
                                <input type="hidden" name="types[]" value="home_banner2_links">
                                <input type="hidden" name="types[]" value="home_banner2_images_mobile">
                                <input type="hidden" name="types[]" value="home_banner2_links_mobile">
                                @if (get_setting('home_banner2_images') != null)
                                    @foreach (json_decode(get_setting('home_banner2_images'), true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]" value="home_banner2_images">
                                                        <input type="hidden" name="home_banner2_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(get_setting('home_banner2_images'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_banner2_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner2_links[]"
                                                        value="{{ json_decode(get_setting('home_banner2_links'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]" value="home_banner2_images_mobile">
                                                        <input type="hidden" name="home_banner2_images_mobile[]"
                                                            class="selected-files"
                                                            value="{{ @json_decode(@get_setting('home_banner2_images_mobile'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_banner2_links_mobile">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner2_links_mobile[]"
                                                        value="{{ @json_decode(get_setting('home_banner2_links_mobile'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group pt-4">
                                                    <button type="button"
                                                        class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                        data-toggle="remove-parent" data-parent=".row">
                                                        <i class="las la-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                data-content='
							<div class="row gutters-5">
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Image') }}</label>
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
											</div>
											<div class="form-control file-amount">{{ ('Choose File') }}</div>
											<input type="hidden" name="types[]" value="home_banner2_images">
											<input type="hidden" name="home_banner2_images[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Link') }}</label>
										<input type="hidden" name="types[]" value="home_banner2_links">
										<input type="text" class="form-control" placeholder="http://" name="home_banner2_links[]">
									</div>
								</div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Image') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div
                                                    class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ ('Browse') }}</div>
                                            </div>
                                            <div class="form-control file-amount">
                                                {{ ('Choose File') }}</div>
                                            <input type="hidden" name="types[]" value="home_banner2_images_mobile">
                                            <input type="hidden" name="home_banner2_images_mobile[]"
                                                class="selected-files"
                                                value="{{ @json_decode(@get_setting('home_banner2_images_mobile'), true)[$key] }}">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Link') }}</label>
                                        <input type="hidden" name="types[]" value="home_banner2_links_mobile">
                                        <input type="text" class="form-control" placeholder="http://"
                                            name="home_banner2_links_mobile[]"
                                            value="{{ @json_decode(get_setting('home_banner2_links_mobile'), true)[$key] }}">
                                    </div>
                                </div>
								<div class="col-md-auto">
									<div class="form-group">
										<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
											<i class="las la-times"></i>
										</button>
									</div>
								</div>
							</div>'
                                data-target=".home-banner2-target">
                                {{ ('Add New') }}
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Home categories --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Home Categories') }}</h6>
                </div>
                <div class="card-body">
                    {{-- <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data"> --}}
                    <form action="{{ route('home_category.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            {{-- <label>{{ ('Categories') }}</label> --}}
                            <div class="row">
                                <div class="col-md-5"><label>{{ ('Categories') }}</label></div>
                                <div class="col-md-5"><label>{{ ('Collection Design') }}</label></div>
                            </div>
                            <div class="home-categories-target">
                                <input type="hidden" name="types[]" value="home_categories">
                                @if (get_setting('home_categories') != null)
                                    @php
                                        $homeCategories = json_decode(get_setting('home_categories'), true) ?? [];
                                    @endphp
                                    @foreach ($homeCategories as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <select class="form-control aiz-selectpicker" name="home_categories[]"
                                                        data-live-search="true" data-selected={{ $value['cid'] }}
                                                        required>
                                                        @foreach ($parentCategories as $category)
                                                            <option value="{{ $category->id }}">
                                                                {{ $category->name }}</option>
                                                            @foreach ($category->childrenCategories as $childCategory)
                                                                @include('categories.child_category', [
                                                                    'child_category' => $childCategory,
                                                                ])
                                                            @endforeach
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <select class="form-control aiz-selectpicker"
                                                        name="collection_designs[]" data-live-search="true"
                                                        data-selected={{ $value['did'] }} required>
                                                        @foreach ($collectionDesigns as $id => $title)
                                                            <option value="{{ $id }}">
                                                                {{ $title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button"
                                                    class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                    data-toggle="remove-parent" data-parent=".row">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                data-content='<div class="row gutters-5">
								<div class="col-md-5">
									<div class="form-group">
										<select class="form-control aiz-selectpicker" name="home_categories[]" data-live-search="true" required>
											@foreach ($categories as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
										</select>
									</div>
								</div>
                                <div class="col-md-5">
									<div class="form-group">
										<select class="form-control aiz-selectpicker" name="collection_designs[]" data-live-search="true" required>
											@foreach ($collectionDesigns as $id => $title)
                                            <option value="{{ $id }}">{{ $title }}</option>
                                            @endforeach
										</select>
									</div>
								</div>
								<div class="col-auto">
									<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
										<i class="las la-times"></i>
									</button>
								</div>
							</div>'
                                data-target=".home-categories-target">
                                {{ ('Add New') }}
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Home categories For App --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Home Categories for Mobile App') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('home_category_app.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-5"><label>{{ ('Categories') }}</label></div>
                                <div class="col-md-5"><label>{{ ('Collection Design') }}</label></div>
                            </div>
                            <div class="home-categories-app-target">
                                <input type="hidden" name="types[]" value="home_categories">
                                @if (get_setting('home_categories_app') != null)
                                    @php
                                        $homeCategoriesApp = json_decode(get_setting('home_categories_app'), true) ?? [];
                                    @endphp
                                    @foreach ($homeCategoriesApp as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <select class="form-control aiz-selectpicker" name="home_categories[]"
                                                        data-live-search="true" data-selected={{ $value['cid'] }}
                                                        required>
                                                        @foreach ($parentCategories as $category)
                                                            <option value="{{ $category->id }}">
                                                                {{ $category->name }}</option>
                                                            @foreach ($category->childrenCategories as $childCategory)
                                                                @include('categories.child_category', [
                                                                    'child_category' => $childCategory,
                                                                ])
                                                            @endforeach
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <select class="form-control aiz-selectpicker"
                                                        name="collection_designs[]" data-live-search="true"
                                                        data-selected={{ $value['did'] }} required>
                                                        @foreach ($collectionDesigns as $id => $title)
                                                            @break($loop->index > 1)
                                                            <option value="{{ $id }}">
                                                                {{ $title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button"
                                                    class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                    data-toggle="remove-parent" data-parent=".row">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                data-content='<div class="row gutters-5">
								<div class="col-md-5">
									<div class="form-group">
										<select class="form-control aiz-selectpicker" name="home_categories[]" data-live-search="true" required>
											@foreach ($categories as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
										</select>
									</div>
								</div>
                                <div class="col-md-5">
									<div class="form-group">
										<select class="form-control aiz-selectpicker" name="collection_designs[]" data-live-search="true" required>
											@foreach ($collectionDesigns as $id => $title)
                                                @break($loop->index > 1)
                                                <option value="{{ $id }}">{{ $title }}</option>
                                            @endforeach
										</select>
									</div>
								</div>
								<div class="col-auto">
									<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
										<i class="las la-times"></i>
									</button>
								</div>
							</div>'
                                data-target=".home-categories-app-target">
                                {{ ('Add New') }}
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>


            {{-- Home Banner 3 --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Home Banner 3 (Max 3)') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <!-- <label>{{ ('Banner & Links') }}</label> -->
                            <div class="home-banner3-target">
                                <input type="hidden" name="types[]" value="home_banner3_images">
                                <input type="hidden" name="types[]" value="home_banner3_links">
                                <input type="hidden" name="types[]" value="home_banner3_images_mobile">
                                <input type="hidden" name="types[]" value="home_banner3_links_mobile">
                                @if (get_setting('home_banner3_images') != null)
                                    @foreach (json_decode(get_setting('home_banner3_images'), true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]" value="home_banner3_images">
                                                        <input type="hidden" name="home_banner3_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(get_setting('home_banner3_images'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_banner3_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner3_links[]"
                                                        value="{{ json_decode(get_setting('home_banner3_links'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]" value="home_banner3_images_mobile">
                                                        <input type="hidden" name="home_banner3_images_mobile[]"
                                                            class="selected-files"
                                                            value="{{ @json_decode(get_setting('home_banner3_images_mobile'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_banner3_links_mobile">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner3_links_mobile[]"
                                                        value="{{ @json_decode(get_setting('home_banner3_links_mobile'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group pt-4">
                                                    <button type="button"
                                                        class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                        data-toggle="remove-parent" data-parent=".row">
                                                        <i class="las la-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                data-content='
							<div class="row gutters-5">
								<div class="col-md-5">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Image') }}</label>
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
											</div>
											<div class="form-control file-amount">{{ ('Choose File') }}</div>
											<input type="hidden" name="types[]" value="home_banner3_images">
											<input type="hidden" name="home_banner3_images[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Link') }}</label>
										<input type="hidden" name="types[]" value="home_banner3_links">
										<input type="text" class="form-control" placeholder="http://" name="home_banner3_links[]">
									</div>
								</div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Image') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div
                                                    class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ ('Browse') }}</div>
                                            </div>
                                            <div class="form-control file-amount">
                                                {{ ('Choose File') }}</div>
                                            <input type="hidden" name="types[]" value="home_banner3_images_mobile">
                                            <input type="hidden" name="home_banner3_images_mobile[]"
                                                class="selected-files"
                                                value="{{ @json_decode(get_setting('home_banner3_images_mobile'), true)[$key] }}">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Link') }}</label>
                                        <input type="hidden" name="types[]" value="home_banner3_links_mobile">
                                        <input type="text" class="form-control" placeholder="http://"
                                            name="home_banner3_links_mobile[]"
                                            value="{{ @json_decode(get_setting('home_banner3_links_mobile'), true)[$key] }}">
                                    </div>
                                </div>
								<div class="col-md-auto">
									<div class="form-group pt-4">
										<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
											<i class="las la-times"></i>
										</button>
									</div>
								</div>
							</div>'
                                data-target=".home-banner3-target">
                                {{ ('Add New') }}
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Top 10 --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Top 10') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-2 col-from-label">{{ ('Top Categories (Max 10)') }}</label>
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="top10_categories">
                                <select name="top10_categories[]" class="form-control aiz-selectpicker" multiple
                                    data-max-options="10" data-live-search="true"
                                    data-selected="{{ get_setting('top10_categories') }}">
                                    @foreach ($parentCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}
                                        </option>
                                        @foreach ($category->childrenCategories as $childCategory)
                                            @include('categories.child_category', [
                                                'child_category' => $childCategory,
                                            ])
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 col-from-label">{{ ('Top Brands (Max 10)') }}</label>
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="top10_brands">
                                <select name="top10_brands[]" class="form-control aiz-selectpicker" multiple
                                    data-max-options="10" data-live-search="true"
                                    data-selected="{{ get_setting('top10_brands') }}">
                                    @foreach ($brands as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Home Advertisement 1 --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Home Ads Banner 1') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <!-- <label>{{ ('Banner & Links') }}</label> -->
                            <div class="home-adsbanner1-target">
                                <input type="hidden" name="types[]" value="home_adsbanner1_images">
                                <input type="hidden" name="types[]" value="home_adsbanner1_links">
                                <input type="hidden" name="types[]" value="home_adsbanner1_images_mobile">
                                <input type="hidden" name="types[]" value="home_adsbanner1_links_mobile">
                                @if (get_setting('home_adsbanner1_images') != null)
                                    @foreach (json_decode(get_setting('home_adsbanner1_images'), true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]"
                                                            value="home_adsbanner1_images">
                                                        <input type="hidden" name="home_adsbanner1_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(get_setting('home_adsbanner1_images'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_adsbanner1_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_adsbanner1_links[]"
                                                        value="{{ json_decode(get_setting('home_adsbanner1_links'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]"
                                                            value="home_adsbanner1_images_mobile">
                                                        <input type="hidden" name="home_adsbanner1_images_mobile[]"
                                                            class="selected-files"
                                                            value="{{ @json_decode(get_setting('home_adsbanner1_images_mobile'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_adsbanner1_links_mobile">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_adsbanner1_links_mobile[]"
                                                        value="{{ @json_decode(get_setting('home_adsbanner1_links_mobile'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group pt-4">
                                                    <button type="button"
                                                        class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                        data-toggle="remove-parent" data-parent=".row">
                                                        <i class="las la-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                data-content='
							<div class="row gutters-5">
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Image') }}</label>
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
											</div>
											<div class="form-control file-amount">{{ ('Choose File') }}</div>
											<input type="hidden" name="types[]" value="home_adsbanner1_images">
											<input type="hidden" name="home_adsbanner1_images[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Link') }}</label>
										<input type="hidden" name="types[]" value="home_adsbanner1_links">
										<input type="text" class="form-control" placeholder="http://" name="home_adsbanner1_links[]">
									</div>
								</div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Image') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div
                                                    class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ ('Browse') }}</div>
                                            </div>
                                            <div class="form-control file-amount">
                                                {{ ('Choose File') }}</div>
                                            <input type="hidden" name="types[]"
                                                value="home_adsbanner1_images_mobile">
                                            <input type="hidden" name="home_adsbanner1_images_mobile[]"
                                                class="selected-files"
                                                value="{{ @json_decode(get_setting('home_adsbanner1_images_mobile'), true)[$key] }}">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Link') }}</label>
                                        <input type="hidden" name="types[]" value="home_adsbanner1_links_mobile">
                                        <input type="text" class="form-control" placeholder="http://"
                                            name="home_adsbanner1_links_mobile[]"
                                            value="{{ @json_decode(get_setting('home_adsbanner1_links_mobile'), true)[$key] }}">
                                    </div>
                                </div>
								<div class="col-md-auto">
									<div class="form-group">
										<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
											<i class="las la-times"></i>
										</button>
									</div>
								</div>
							</div>'
                                data-target=".home-adsbanner1-target">
                                {{ ('Add New') }}
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Home Advertisement 2 --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Home Ads Banner 2') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <!-- <label>{{ ('Banner & Links') }}</label> -->
                            <div class="home-adsbanner2-target">
                                <input type="hidden" name="types[]" value="home_adsbanner2_images">
                                <input type="hidden" name="types[]" value="home_adsbanner2_links">
                                <input type="hidden" name="types[]" value="home_adsbanner2_images_mobile">
                                <input type="hidden" name="types[]" value="home_adsbanner2_links_mobile">
                                @if (get_setting('home_adsbanner2_images') != null)
                                    @foreach (json_decode(get_setting('home_adsbanner2_images'), true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]"
                                                            value="home_adsbanner2_images">
                                                        <input type="hidden" name="home_adsbanner2_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(get_setting('home_adsbanner2_images'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_adsbanner2_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_adsbanner2_links[]"
                                                        value="{{ json_decode(get_setting('home_adsbanner2_links'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]"
                                                            value="home_adsbanner2_images_mobile">
                                                        <input type="hidden" name="home_adsbanner2_images_mobile[]"
                                                            class="selected-files"
                                                            value="{{ @json_decode(get_setting('home_adsbanner2_images_mobile'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_adsbanner2_links_mobile">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_adsbanner2_links_mobile[]"
                                                        value="{{ @json_decode(get_setting('home_adsbanner2_links_mobile'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group pt-4">
                                                    <button type="button"
                                                        class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                        data-toggle="remove-parent" data-parent=".row">
                                                        <i class="las la-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                data-content='
							<div class="row gutters-5">
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Image') }}</label>
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
											</div>
											<div class="form-control file-amount">{{ ('Choose File') }}</div>
											<input type="hidden" name="types[]" value="home_adsbanner2_images">
											<input type="hidden" name="home_adsbanner2_images[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Link') }}</label>
										<input type="hidden" name="types[]" value="home_adsbanner2_links">
										<input type="text" class="form-control" placeholder="http://" name="home_adsbanner2_links[]">
									</div>
								</div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Image') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div
                                                    class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ ('Browse') }}</div>
                                            </div>
                                            <div class="form-control file-amount">
                                                {{ ('Choose File') }}</div>
                                            <input type="hidden" name="types[]"
                                                value="home_adsbanner2_images_mobile">
                                            <input type="hidden" name="home_adsbanner2_images_mobile[]"
                                                class="selected-files"
                                                value="{{ @json_decode(get_setting('home_adsbanner2_images_mobile'), true)[$key] }}">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Link') }}</label>
                                        <input type="hidden" name="types[]" value="home_adsbanner2_links_mobile">
                                        <input type="text" class="form-control" placeholder="http://"
                                            name="home_adsbanner2_links_mobile[]"
                                            value="{{ @json_decode(get_setting('home_adsbanner2_links_mobile'), true)[$key] }}">
                                    </div>
                                </div>
								<div class="col-md-auto">
									<div class="form-group pt-4">
										<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
											<i class="las la-times"></i>
										</button>
									</div>
								</div>
							</div>'
                                data-target=".home-adsbanner2-target">
                                {{ ('Add New') }}
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Home Advertisement 3 --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Home Ads Banner 3') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <!-- <label>{{ ('Banner & Links') }}</label> -->
                            <div class="home-adsbanner3-target">
                                <input type="hidden" name="types[]" value="home_adsbanner3_images">
                                <input type="hidden" name="types[]" value="home_adsbanner3_links">
                                <input type="hidden" name="types[]" value="home_adsbanner3_images_mobile">
                                <input type="hidden" name="types[]" value="home_adsbanner3_links_mobile">
                                @if (get_setting('home_adsbanner3_images') != null)
                                    @foreach (json_decode(get_setting('home_adsbanner3_images'), true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]"
                                                            value="home_adsbanner3_images">
                                                        <input type="hidden" name="home_adsbanner3_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(get_setting('home_adsbanner3_images'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_adsbanner3_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_adsbanner3_links[]"
                                                        value="{{ json_decode(get_setting('home_adsbanner3_links'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div
                                                                class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}</div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]"
                                                            value="home_adsbanner3_images_mobile">
                                                        <input type="hidden" name="home_adsbanner3_images_mobile[]"
                                                            class="selected-files"
                                                            value="{{ @json_decode(get_setting('home_adsbanner3_images_mobile'), true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_adsbanner3_links_mobile">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_adsbanner3_links_mobile[]"
                                                        value="{{ @json_decode(get_setting('home_adsbanner3_links_mobile'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group pt-4">
                                                    <button type="button"
                                                        class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                        data-toggle="remove-parent" data-parent=".row">
                                                        <i class="las la-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                data-content='
							<div class="row gutters-5">
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Image') }}</label>
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
											</div>
											<div class="form-control file-amount">{{ ('Choose File') }}</div>
											<input type="hidden" name="types[]" value="home_adsbanner3_images">
											<input type="hidden" name="home_adsbanner3_images[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Link') }}</label>
										<input type="hidden" name="types[]" value="home_adsbanner3_links">
										<input type="text" class="form-control" placeholder="http://" name="home_adsbanner3_links[]">
									</div>
								</div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Image') }}</label>
                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div
                                                    class="input-group-text bg-soft-secondary font-weight-medium">
                                                    {{ ('Browse') }}</div>
                                            </div>
                                            <div class="form-control file-amount">
                                                {{ ('Choose File') }}</div>
                                            <input type="hidden" name="types[]"
                                                value="home_adsbanner3_images_mobile">
                                            <input type="hidden" name="home_adsbanner3_images_mobile[]"
                                                class="selected-files"
                                                value="{{ @json_decode(get_setting('home_adsbanner3_images_mobile'), true)[$key] }}">
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-group">
                                        <label>{{ ('Mobile/App Link') }}</label>
                                        <input type="hidden" name="types[]" value="home_adsbanner3_links_mobile">
                                        <input type="text" class="form-control" placeholder="http://"
                                            name="home_adsbanner3_links_mobile[]"
                                            value="{{ @json_decode(get_setting('home_adsbanner3_links_mobile'), true)[$key] }}">
                                    </div>
                                </div>
								<div class="col-md-auto">
									<div class="form-group pt-4">
										<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
											<i class="las la-times"></i>
										</button>
									</div>
								</div>
							</div>'
                                data-target=".home-adsbanner3-target">
                                {{ ('Add New') }}
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Doctor's Consultation --}}
            <div class="card" id="doctors-consultation">
                <div class="card-header">
                    <h6 class="mb-0">Doctor's Consultation</h6>
                </div>
                @php
                    $doctorsConsultation = json_decode(get_setting('doctors_consultation'), true) ?? [];
                @endphp
                <div class="card-body">
                    <form action="{{ route('doctors_consultation.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Title <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Enter title" name="title" value="{{ old('title', $doctorsConsultation['title'] ?? '') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Description <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-10">
                                <textarea class="form-control" placeholder="Enter description" name="description" rows="4" required>{{ old('description', $doctorsConsultation['description'] ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Banner Image <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-10">
                                <div class="form-group">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                Browse
                                            </div>
                                        </div>
                                        <div class="form-control file-amount">
                                            Choose File
                                        </div>
                                        <input type="hidden" name="banner" class="selected-files" value="{{ $doctorsConsultation['banner'] ?? '' }}">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Button Text <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Enter button text" name="button_text" value="{{ old('button_text', $doctorsConsultation['button_text'] ?? '') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Button Link</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="https://example.com" name="button_link" value="{{ old('button_link', $doctorsConsultation['button_link'] ?? '') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Show Experience Card</label>
                            </div>
                            <div class="col-md-10">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" {{ @$doctorsConsultation['show_experience_card'] ? 'checked' : '' }} id="show_experience_card" name="show_experience_card">
                                    <input type="hidden" value="{{ $doctorsConsultation['show_experience_card'] ?? 0 }}" name="show_experience_card">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Card Title</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Enter card title" name="card_title" value="{{ old('card_title', $doctorsConsultation['card_title'] ?? '') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Card Rating</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" placeholder="Between 1 and 5" name="card_rating" value="{{ old('card_rating', $doctorsConsultation['card_rating'] ?? '') }}">
                                <small class="form-text text-muted">
                                    Set the card rating between 1 and 5.
                                </small>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>

                    @if (session()->has('doctorsConsultationHasErrors') && $errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            {{-- New Arrivals --}}
            @php
                $new_arrival_content = json_decode(get_setting('new_arrival_content'), true) ?? [];
            @endphp
            <div class="card" id="new-arrival-products">
                <div class="card-header">
                    <h6 class="mb-0">New Arrivals</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">New Arrival Days</label>
                                @include('components.tooltip', [
                                    'title' => "This defines how many days from a product’s creation date it will be considered a 'New Arrival'. By default, it set to 30, products created within the last 30 days will appear in the 'New Arrivals' section.",
                                    'class' => 'text-info',
                                    'position' => 'top',
                                ])
                            </div>
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="new_arrival_days">
                                <input type="number" min="0" step="1" class="form-control" placeholder="Enter a positive number" name="new_arrival_days" value="{{ get_setting('new_arrival_days', 30) }}">
                                <small class="form-text text-muted">
                                    Set the number of days to mark products as "new arrivals" from their creation date.
                                </small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Title</label>
                            </div>
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="new_arrival_content">
                                <input type="text" class="form-control" placeholder="Enter title" name="new_arrival_content[title]" value="{{ data_get($new_arrival_content, 'title') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Icon <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-10">
                                <div class="form-group">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                Browse
                                            </div>
                                        </div>
                                        <div class="form-control file-amount">
                                            Choose File
                                        </div>
                                        <input type="hidden" name="types[]" value="new_arrival_content">
                                        <input type="hidden" name="new_arrival_content[icon]" class="selected-files" value="{{ data_get($new_arrival_content, 'icon') }}">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Highlight Brand --}}
            @php
                $highlight_brand = json_decode(get_setting('highlight_brand'), true) ?? [];
            @endphp
            <div class="card" id="highlight-brand">
                <div class="card-header">
                    <h6 class="mb-0">Highlight Brand</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Brand <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="highlight_brand">
                                <select class="form-control aiz-selectpicker" name="highlight_brand[id]" data-live-search="true" required>
                                    <option value="">Select Brand</option>
                                    @foreach ($brands as $id => $name)
                                        <option value="{{ $id }}" {{ $id == data_get($highlight_brand, 'id') ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Title</label>
                            </div>
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="highlight_brand">
                                <input type="text" class="form-control" placeholder="Enter title" name="highlight_brand[title]" value="{{ data_get($highlight_brand, 'title') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Banner <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-10">
                                <div class="form-group">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                Browse
                                            </div>
                                        </div>
                                        <div class="form-control file-amount">
                                            Choose File
                                        </div>
                                        <input type="hidden" name="types[]" value="highlight_brand">
                                        <input type="hidden" name="highlight_brand[banner]" class="selected-files" value="{{ data_get($highlight_brand, 'banner') }}">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label class="col-from-label">Icon <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-10">
                                <div class="form-group">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                Browse
                                            </div>
                                        </div>
                                        <div class="form-control file-amount">
                                            Choose File
                                        </div>
                                        <input type="hidden" name="types[]" value="highlight_brand">
                                        <input type="hidden" name="highlight_brand[icon]" class="selected-files" value="{{ data_get($highlight_brand, 'icon') }}">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            AIZ.plugins.bootstrapSelect('refresh');

            $('#show_experience_card').on('change', function() {
                if ($(this).is(':checked')) {
                    $(this).next().val(1);
                    $('input[name="show_experience_card"]').val(1);
                    $('input[name="card_title"]').prop('required', true);
                    $('input[name="card_rating"]').prop('required', true);
                } else {
                    $(this).next().val(0);
                    $('input[name="show_experience_card"]').val(0);
                    $('input[name="card_title"]').prop('required', false);
                    $('input[name="card_rating"]').prop('required', false);
                }
            });

            $('input[name="card_rating"]').on('input', function() {
                var value = parseFloat($(this).val());
                if (isNaN(value)) {
                    $(this).val('');
                    return;
                }
                if (value < 1) {
                    $(this).val(1);
                } else if (value > 5) {
                    $(this).val(5);
                }
            });
        });
    </script>
@endsection
