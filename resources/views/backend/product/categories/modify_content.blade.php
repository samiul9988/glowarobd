@extends('backend.layouts.app')
@section('meta_title', 'Parent Category Page Settings')
@section('content')
    @php
        $contentCollection = $category->content ?? collect([]);
    @endphp
    <div class="row">
        <div class="col-xl-10 mx-auto">
            <h6 class="fw-600">{{ ('Parent Category Page Settings') }}</h6>

            {{--<div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Enable/Disable Features') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update-content', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="show_subcategories_section">
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="show_subcategories_section" name="show_subcategories_section" value="1" @if (@intval(@$contentCollection->where('type', 'show_subcategories_section')->first()->value) == 1) checked="checked" @endif>
                                    <label class="form-check-label" for="show_subcategories_section">Display Subcategories Section?</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row d-none">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="show_subcategories_section_mobile">
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="show_subcategories_section_mobile" name="show_subcategories_section_mobile" value="1" @if (@intval(@$contentCollection->where('type', 'show_subcategories_section_mobile')->first()->value) == 1) checked="checked" @endif>
                                    <label class="form-check-label" for="show_subcategories_section_mobile">Display Subcategories Section On App/Mobile Devices?</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="show_best_selling_products">
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="show_best_selling_products" name="show_best_selling_products" value="1" @if (@intval($contentCollection->where('type', 'show_best_selling_products')->first()->value) == 1) checked="checked" @endif>
                                    <label class="form-check-label" for="show_best_selling_products">Display Best Selling Products?</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row d-none">
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="show_best_selling_products_mobile">
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="show_best_selling_products_mobile" name="show_best_selling_products_mobile" value="1" @if (@intval($contentCollection->where('type', 'show_best_selling_products_mobile')->first()->value) == 1) checked="checked" @endif>
                                    <label class="form-check-label" for="show_best_selling_products_mobile">Display Best Selling Products?</label>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>--}}

            {{-- Category Page Slider --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Parent Category Slider') }}</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        {{ ('We have limited banner height to maintain UI. We had to crop from both left & right side in view for different devices to make it responsive. Before designing banner keep these points in mind.') }}
                    </div>
                    <form action="{{ route('admin.categories.update-content', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">

                            <div class="home-slider-target">
                                <input type="hidden" name="types[]" value="home_slider_images">
                                <input type="hidden" name="types[]" value="home_slider_images_mobile">
                                <input type="hidden" name="types[]" value="home_slider_links">
                                <input type="hidden" name="types[]" value="home_slider_links_mobile">
                                <input type="hidden" name="types[]" value="home_slider_bgcolors">
                                <input type="hidden" name="types[]" value="home_slider_bgcolors_mobile">
                                @if (!empty($contentCollection->where('type', 'home_slider_images')->first()) && json_decode($contentCollection->where('type', 'home_slider_images')->first()->value, true) != null)
                                    @foreach (json_decode(@$contentCollection->where('type', 'home_slider_images')->first()->value, true) as $key => $value)
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
                                                        <input type="hidden" name="home_slider_images[]" class="selected-files" value="{{ json_decode(@$contentCollection->where('type', 'home_slider_images')->first()->value, true)[$key] }}">
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
                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}
                                                            </div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]"
                                                            value="home_slider_images_mobile">
                                                        <input type="hidden" name="home_slider_images_mobile[]"
                                                            class="selected-files"
                                                            value="{{ @json_decode(@$contentCollection->where('type', 'home_slider_images_mobile')->first()->value, true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Web Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_slider_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_slider_links[]"
                                                        value="{{ json_decode(@$contentCollection->where('type', 'home_slider_links')->first()->value, true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_slider_links_mobile">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_slider_links_mobile[]"
                                                        value="{{ @json_decode(@$contentCollection->where('type', 'home_slider_links_mobile')->first()->value, true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Web Bg color') }}</label>
                                                    <input type="hidden" name="types[]" value="home_slider_bgcolors">
                                                    <input type="color" class="form-control" placeholder="http://"
                                                        name="home_slider_bgcolors[]"
                                                        value="{{ json_decode(@$contentCollection->where('type', 'home_slider_bgcolors')->first()->value, true)[$key] ?? '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Bg color') }}</label>
                                                    <input type="hidden" name="types[]" value="home_slider_bgcolors_mobile">
                                                    <input type="color" class="form-control" placeholder="http://"
                                                        name="home_slider_bgcolors_mobile[]"
                                                        value="{{ json_decode(@$contentCollection->where('type', 'home_slider_bgcolors_mobile')->first()->value, true)[$key] ?? '' }}">
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
										<label>{{ ('Web Link') }}</label>
										<input type="hidden" name="types[]" value="home_slider_links">
										<input type="text" class="form-control" placeholder="http://" name="home_slider_links[]">
									</div>
								</div>
                                <div class="col-md">
									<div class="form-group">
										<label>{{ ('Mobile Link') }}</label>
										<input type="hidden" name="types[]" value="home_slider_links_mobile">
										<input type="text" class="form-control" placeholder="http://" name="home_slider_links_mobile[]">
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
										<label>Web Bg color</label>
										<input type="hidden" name="types[]" value="home_slider_bgcolors">
										<input type="color" class="form-control" name="home_slider_bgcolors[]" value="">
									</div>
								</div>
                                <div class="col-md">
									<div class="form-group">
										<label>Mobile/App Bg color</label>
										<input type="hidden" name="types[]" value="home_slider_bgcolors_mobile">
										<input type="color" class="form-control" name="home_slider_bgcolors_mobile[]" value="">
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

            {{-- Subcategories --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Subcategories') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update-content', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-2 col-from-label">{{ ('Sub-categories') }}</label>
                            <div class="col-md-10">
                                <input type="hidden" name="types[]" value="top_categories">
                                <select name="top_categories[]" class="form-control aiz-selectpicker" multiple
                                    data-max-options="10" data-live-search="true"
                                    data-selected="{{ @$contentCollection->where('type', 'top_categories')->first()->value }}">
                                    @foreach (@$category->subcategories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}
                                        </option>
                                        @foreach ($cat->childrenCategories as $childCategory)
                                            @include('categories.child_category', [
                                                'child_category' => $childCategory,
                                            ])
                                        @endforeach
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

            {{-- Page Banner 1 --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Page Banner 1') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update-content', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <div class="home-banner1-target">
                                <input type="hidden" name="types[]" value="home_banner1_images">
                                <input type="hidden" name="types[]" value="home_banner1_images_mobile">
                                <input type="hidden" name="types[]" value="home_banner1_links">
                                <input type="hidden" name="types[]" value="home_banner1_links_mobile">
                                @if ($contentCollection->where('type', 'home_banner1_images')->first())
                                    @foreach (json_decode(@$contentCollection->where('type', 'home_banner1_images')->first()->value, true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}
                                                            </div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}
                                                        </div>
                                                        <input type="hidden" name="types[]" value="home_banner1_images">
                                                        <input type="hidden" name="home_banner1_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(@$contentCollection->where('type', 'home_banner1_images')->first()->value, true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}
                                                            </div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}
                                                        </div>
                                                        <input type="hidden" name="types[]" value="home_banner1_images_mobile">
                                                        <input type="hidden" name="home_banner1_images_mobile[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(@$contentCollection->where('type', 'home_banner1_images_mobile')->first()->value, true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Links') }}</label>
                                                    <input type="hidden" name="types[]" value="home_banner1_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner1_links[]"
                                                        value="{{ json_decode(@$contentCollection->where('type', 'home_banner1_links')->first()->value, true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Links') }}</label>
                                                    <input type="hidden" name="types[]" value="home_banner1_links_mobile">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner1_links_mobile[]"
                                                        value="{{ json_decode(@$contentCollection->where('type', 'home_banner1_links_mobile')->first()->value, true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group">
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
                                        <label>{{ ('Mobile/App Image') }}</label>
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
											</div>
											<div class="form-control file-amount">{{ ('Choose File') }}</div>
											<input type="hidden" name="types[]" value="home_banner1_images_mobile">
											<input type="hidden" name="home_banner1_images_mobile[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Desktop/Web Links') }}</label>
										<input type="hidden" name="types[]" value="home_banner1_links">
										<input type="text" class="form-control" placeholder="http://" name="home_banner1_links[]">
									</div>
								</div>
                                <div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Mobile/App Links') }}</label>
										<input type="hidden" name="types[]" value="home_banner1_links_mobile">
										<input type="text" class="form-control" placeholder="http://" name="home_banner1_links_mobile[]">
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
                    <h6 class="mb-0">{{ ('Page Banner 2') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update-content', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>{{ ('Banner & Links') }}</label>
                            <div class="home-banner2-target">
                                <input type="hidden" name="types[]" value="home_banner2_images">
                                <input type="hidden" name="types[]" value="home_banner2_images_mobile">
                                <input type="hidden" name="types[]" value="home_banner2_links">
                                <input type="hidden" name="types[]" value="home_banner2_links_mobile">
                                @if(!empty($contentCollection->where('type', 'home_banner2_images')->first()) && json_decode(@$contentCollection->where('type', 'home_banner2_images')->first()->value, true) != null)
                                    @foreach (json_decode(@$contentCollection->where('type', 'home_banner2_images')->first()->value, true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Desktop/Web Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}
                                                            </div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}
                                                        </div>
                                                        <input type="hidden" name="types[]" value="home_banner2_images">
                                                        <input type="hidden" name="home_banner2_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(@$contentCollection->where('type', 'home_banner2_images')->first()->value, true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile/App Image') }}</label>
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}
                                                            </div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}
                                                        </div>
                                                        <input type="hidden" name="types[]" value="home_banner2_images">
                                                        <input type="hidden" name="home_banner2_images_mobile[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(@$contentCollection->where('type', 'home_banner2_images_mobile')->first()->value, true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <label>{{ ('Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_banner2_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner2_links[]"
                                                        value="{{ json_decode(@$contentCollection->where('type', 'home_banner2_links')->first()->value, true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md d-none">
                                                <div class="form-group">
                                                    <label>{{ ('Mobile Link') }}</label>
                                                    <input type="hidden" name="types[]" value="home_banner2_links_mobile">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner2_links_mobile[]"
                                                        value="{{ json_decode(@$contentCollection->where('type', 'home_banner2_links_mobile')->first()->value, true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group">
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
                                        <label>{{ ('Banner Image') }}</label>
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
                                        <label>{{ ('Mobile/App Image') }}</label>
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
											</div>
											<div class="form-control file-amount">{{ ('Choose File') }}</div>
											<input type="hidden" name="types[]" value="home_banner2_images_mobile">
											<input type="hidden" name="home_banner2_images_mobile[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Link') }}</label>
										<input type="hidden" name="types[]" value="home_banner2_links">
										<input type="text" class="form-control" placeholder="http://" name="home_banner2_links[]">
									</div>
								</div>
                                <div class="col-md">
									<div class="form-group">
                                        <label>{{ ('Mobile Link') }}</label>
										<input type="hidden" name="types[]" value="home_banner2_links_mobile">
										<input type="text" class="form-control" placeholder="http://" name="home_banner2_links_mobile[]">
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
                    <h6 class="mb-0">{{ ('Page Categories') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update-content', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-5"><label>{{ ('Categories') }}</label></div>
                                <div class="col-md-5"><label>{{ ('Collection Design') }}</label></div>
                            </div>
                            <div class="home-categories-target">
                                <input type="hidden" name="types[]" value="home_categories">
                                <input type="hidden" name="types[]" value="home_categories_designs">
                                @if ($contentCollection->where('type', 'home_categories')->first() != null && json_decode($contentCollection->where('type', 'home_categories')->first()->value, true) != null)
                                    @foreach (json_decode($contentCollection->where('type', 'home_categories')->first()->value, true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <select class="form-control aiz-selectpicker" name="home_categories[]"
                                                        data-live-search="true" data-selected={{ $value }}
                                                        required>
                                                        @foreach ($category->subcategories as $c_cat)
                                                            <option value="{{ $c_cat->id }}">
                                                                {{ $c_cat->name }}</option>
                                                            @foreach ($c_cat->childrenCategories as $childCategory)
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
                                                    <select class="form-control aiz-selectpicker" name="home_categories_designs[]" data-live-search="true" data-selected="{{ @json_decode(@$contentCollection->where('type', 'home_categories_designs')->first()->value, true)[$key] }}" required>
                                                        <option value="design_1">{{ ('Design 1') }}</option>
                                                        <option value="design_2">{{ ('Design 2') }}</option>
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
											@foreach($category->subcategories as $ccat)
                                                <option value="{{ $ccat->id }}">{{ $ccat->name }}</option>
                                            @endforeach
										</select>
									</div>
								</div>
                                <div class="col-md-5">
									<div class="form-group">
										<select class="form-control aiz-selectpicker" name="home_categories_designs[]" data-live-search="true" required>
											<option value="design_1">Design 1</option>
											<option value="design_2">Design 2</option>
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

            {{--
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Page Banner 3') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update-content', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>{{ ('Banner & Links') }}</label>
                            <div class="home-banner3-target">
                                <input type="hidden" name="types[]" value="home_banner3_images">
                                <input type="hidden" name="types[]" value="home_banner3_images_mobile">
                                <input type="hidden" name="types[]" value="home_banner3_links">
                                <input type="hidden" name="types[]" value="home_banner3_links_mobile">
                                @if($contentCollection->where('type', 'home_banner3_images')->first())
                                    @foreach (json_decode(@$contentCollection-where('type', 'home_banner3_images')->first()->value, true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}
                                                            </div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}
                                                        </div>
                                                        <input type="hidden" name="types[]" value="home_banner3_images">
                                                        <input type="hidden" name="home_banner3_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(@$contentCollection-where('type', 'home_banner3_images')->first()->value, true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5 d-none">
                                                <div class="form-group">
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}
                                                            </div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}
                                                        </div>
                                                        <input type="hidden" name="types[]" value="home_banner3_images">
                                                        <input type="hidden" name="home_banner3_images_mobile[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(@$contentCollection-where('type', 'home_banner3_images_mobile')->first()->value, true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <input type="hidden" name="types[]" value="home_banner3_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner3_links[]"
                                                        value="{{ json_decode(get_setting('home_banner3_links'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md d-none">
                                                <div class="form-group">
                                                    <input type="hidden" name="types[]" value="home_banner3_links_mobile">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_banner3_links_mobile[]"
                                                        value="{{ json_decode(@$contentCollection-where('type', 'home_banner3_links_mobile')->first()->value, true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group">
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
                                <div class="col-md-5 d-none">
									<div class="form-group">
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
											</div>
											<div class="form-control file-amount">{{ ('Choose File') }}</div>
											<input type="hidden" name="types[]" value="home_banner3_images_mobile">
											<input type="hidden" name="home_banner3_images_mobile[]" class="selected-files">
										</div>
										<div class="file-preview box sm">
										</div>
									</div>
								</div>
								<div class="col-md">
									<div class="form-group">
										<input type="hidden" name="types[]" value="home_banner3_links">
										<input type="text" class="form-control" placeholder="http://" name="home_banner3_links[]">
									</div>
								</div>
                                <div class="col-md d-none">
									<div class="form-group">
										<input type="hidden" name="types[]" value="home_banner3_links_mobile">
										<input type="text" class="form-control" placeholder="http://" name="home_banner3_links_mobile[]">
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

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Page Ads Banner 1') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update-content', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>{{ ('Banner & Links') }}</label>
                            <div class="home-adsbanner1-target">
                                <input type="hidden" name="types[]" value="home_adsbanner1_images">
                                <input type="hidden" name="types[]" value="home_adsbanner1_links">
                                @if ($contentCollection->where('type', 'home_adsbanner1_images')->first())
                                    @foreach (json_decode(@$contentCollection->where('type', 'home_adsbanner1_images')->first()->value, true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}
                                                            </div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]"
                                                            value="home_adsbanner1_images">
                                                        <input type="hidden" name="home_adsbanner1_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(@$contentCollection->where('type', 'home_adsbanner1_images')->first()->value, true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <input type="hidden" name="types[]" value="home_adsbanner1_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_adsbanner1_links[]"
                                                        value="{{ json_decode(@$contentCollection->where('type', 'home_adsbanner1_links')->first()->value, true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group">
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
										<input type="hidden" name="types[]" value="home_adsbanner1_links">
										<input type="text" class="form-control" placeholder="http://" name="home_adsbanner1_links[]">
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

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Home Ads Banner 2') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update-content', $category->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>{{ ('Banner & Links') }}</label>
                            <div class="home-adsbanner2-target">
                                <input type="hidden" name="types[]" value="home_adsbanner2_images">
                                <input type="hidden" name="types[]" value="home_adsbanner2_links">
                                @if ($contentCollection->where('type', 'home_adsbanner2_images')->first() != null)
                                    @foreach (json_decode(@$contentCollection->where('type', 'home_adsbanner2_images')->first()->value, true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}
                                                            </div>
                                                        </div>
                                                        <div class="form-control file-amount">
                                                            {{ ('Choose File') }}</div>
                                                        <input type="hidden" name="types[]"
                                                            value="home_adsbanner2_images">
                                                        <input type="hidden" name="home_adsbanner2_images[]"
                                                            class="selected-files"
                                                            value="{{ json_decode(@$contentCollection->where('type', 'home_adsbanner2_images')->first()->value, true)[$key] }}">
                                                    </div>
                                                    <div class="file-preview box sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="form-group">
                                                    <input type="hidden" name="types[]" value="home_adsbanner2_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_adsbanner2_links[]"
                                                        value="{{ json_decode(@$contentCollection->where('type', 'home_adsbanner2_links')->first()->value, true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group">
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
										<input type="hidden" name="types[]" value="home_adsbanner2_links">
										<input type="text" class="form-control" placeholder="http://" name="home_adsbanner2_links[]">
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

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ ('Home Ads Banner 3') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update-content', $category->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>{{ ('Banner & Links') }}</label>
                            <div class="home-adsbanner3-target">
                                <input type="hidden" name="types[]" value="home_adsbanner3_images">
                                <input type="hidden" name="types[]" value="home_adsbanner3_links">
                                @if ($contentCollection->where('type', 'home_adsbanner3_images')->first() != null)
                                    @foreach (json_decode(@$contentCollection->where('type', 'home_adsbanner3_images')->first()->value, true) as $key => $value)
                                        <div class="row gutters-5">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                {{ ('Browse') }}
                                                            </div>
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
                                                    <input type="hidden" name="types[]" value="home_adsbanner3_links">
                                                    <input type="text" class="form-control" placeholder="http://"
                                                        name="home_adsbanner3_links[]"
                                                        value="{{ json_decode(get_setting('home_adsbanner3_links'), true)[$key] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="form-group">
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
										<input type="hidden" name="types[]" value="home_adsbanner3_links">
										<input type="text" class="form-control" placeholder="http://" name="home_adsbanner3_links[]">
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
                                data-target=".home-adsbanner3-target">
                                {{ ('Add New') }}
                            </button>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div> --}}
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            AIZ.plugins.bootstrapSelect('refresh');
        });
    </script>
@endsection
