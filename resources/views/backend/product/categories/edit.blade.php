@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ ('Category Information')}}</h5>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-fill border-light">
                    @foreach (\App\Models\Language::all() as $key => $language)
                    <li class="nav-item">
                        <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3" href="{{ route('categories.edit', ['id'=>$category->id, 'lang'=> $language->code] ) }}">
                            <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                            <span>{{$language->name}}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                <form class="p-4" action="{{ route('categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                    <input name="_method" type="hidden" value="PATCH">
    	            <input type="hidden" name="lang" value="{{ $lang ?? 'en' }}">
                	@csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Name')}} <i class="las la-language text-danger" title="{{ ('Translatable')}}"></i></label>
                        <div class="col-md-9">
                            <input type="text" name="name" value="{{ $category->getTranslation('name', $lang) }}" class="form-control" id="name" placeholder="{{ ('Name')}}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Parent Category')}}</label>
                        <div class="col-md-9">
                            <select class="select2 form-control aiz-selectpicker" name="parent_id" data-toggle="select2" data-placeholder="Choose ..."data-live-search="true" data-selected="{{ $category->parent_id }}">
                                <option value="0">{{ ('No Parent') }}</option>
                                @foreach ($categories as $acategory)
                                    <option value="{{ $acategory->id }}">{{ $acategory->getTranslation('name') }}</option>
                                    @foreach ($acategory->childrenCategories as $childCategory)
                                        @include('categories.child_category', ['child_category' => $childCategory])
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            {{ ('Ordering Number')}}
                        </label>
                        <div class="col-md-9">
                            <input type="number" name="order_level" value="{{ $category->order_level }}" class="form-control" id="order_level" placeholder="{{ ('Order Level')}}">
                            <small>{{ ('Higher number has high priority')}}</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Type')}}</label>
                        <div class="col-md-9">
                            <select name="digital" required class="form-control aiz-selectpicker mb-2 mb-md-0">
                                <option value="0" @if ($category->digital == '0') selected @endif>{{ ('Physical')}}</option>
                                <option value="1" @if ($category->digital == '1') selected @endif>{{ ('Digital')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            BG Color For Child Categories
                            @include('components.tooltip', [
                                'title' => 'Configures the background color for child category cards in the mobile-responsive category page layout.',
                            ])
                        </label>
                        <div class="col-md-9">
                            <input type="text" name="child_bg_color" value="{{ $category->child_bg_color }}" class="form-control" id="child_bg_color" placeholder="Input hexadecimal color code. E.g: #ffffff">
                        </div>
                    </div>


                    {{-- Images --}}
                    @php
                        $banner = json_decode($category->banner, true);
                        $page_banner = json_decode($category->page_banner, true);
                        $icon = json_decode($category->icon, true);
                        $featured_icon = json_decode($category->featured_icon, true);
                        $bg_image = json_decode($category->bg_image, true);
                        $app_slider = json_decode($category->app_slider, true);
                        $app_banner1 = json_decode($category->app_banner1, true);
                        $app_banner2 = json_decode($category->app_banner2, true);
                    @endphp
                    {{-- Banner Images --}}
                    <div class="form-group row">
                        <div class="border-0 bg-transparent" style="min-width:100%">
                            <div class="card-header">
                                <h6 class="mb-0">{{ ('Layout Banner') }}</h6>
                            </div>
                            <div class="card-body">
                                    <div class="form-group">
                                        <div class="banner-target">
                                                <div class="row gutters-5">
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Desktop Image (Web)') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="banner[web]" class="selected-files" value="{{ $banner['web'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Mobile Image (Web)') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="banner[mobile]" class="selected-files" value="{{ $banner['mobile'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('App Image') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="banner[app]" class="selected-files" value="{{ $banner['app'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                    {{-- End Banner Images --}}

                    {{-- Page Banner Images --}}
                    <div class="form-group row">
                        <div class="border-0 bg-transparent" style="min-width:100%">
                            <div class="card-header">
                                <h6 class="mb-0">{{ ('Category Page Banner') }}</h6>
                            </div>
                            <div class="card-body">
                                    <div class="form-group">
                                        <div class="page-banner-target">
                                                <div class="row gutters-5">
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Desktop Image (Web)') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="pageBanner[web]" class="selected-files" value="{{ $page_banner['web'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Mobile Image (Web)') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="pageBanner[mobile]" class="selected-files" value="{{ $page_banner['mobile'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('App Image') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="pageBanner[app]" class="selected-files" value="{{ $page_banner['app'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                    {{-- End Page Banner Images --}}

                    {{-- Icon --}}
                    <div class="form-group row">
                        <div class="border-0 bg-transparent" style="min-width:100%">
                            <div class="card-header">
                                <h6 class="mb-0">{{ ('Title Icon') }}</h6>
                            </div>
                            <div class="card-body">
                                    <div class="form-group">
                                        <div class="icon-target">
                                                <div class="row gutters-5">
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Desktop Image (Web)') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="icon[web]" class="selected-files" value="{{ $icon['web'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Mobile Image (Web)') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="icon[mobile]" class="selected-files" value="{{ $icon['mobile'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('App Image') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="icon[app]" class="selected-files" value="{{ $icon['app'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                    {{-- End Icon --}}

                    {{-- Featured Icon --}}
                    <div class="form-group row">
                        <div class="border-0 bg-transparent" style="min-width:100%">
                            <div class="card-header">
                                <h6 class="mb-0">{{ ('Featured Icon') }}</h6>
                            </div>
                            <div class="card-body">
                                    <div class="form-group">
                                        <div class="featured-icon-target">
                                                <div class="row gutters-5">
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Desktop Image (Web)') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="featured_icon[web]" class="selected-files" value="{{ $featured_icon['web'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Mobile Category Page (Web)') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="featured_icon[mobile]" class="selected-files" value="{{ $featured_icon['mobile'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Home Page Icon (Mobile)') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="featured_icon[home_page]" class="selected-files" value="{{ $featured_icon['home_page'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('App Image') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="featured_icon[app]" class="selected-files" value="{{ $featured_icon['app'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                    {{-- End Featured Icon --}}

                    {{-- Background Image --}}
                    <div class="form-group row">
                        <div class="border-0 bg-transparent" style="min-width:100%">
                            <div class="card-header">
                                <h6 class="mb-0">{{ ('Layout Background Image') }}</h6>
                            </div>
                            <div class="card-body">
                                    <div class="form-group">
                                        <div class="bgImage-target">
                                                <div class="row gutters-5">
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Desktop Image (Web)') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="bg_image[web]" class="selected-files" value="{{ $bg_image['web'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Mobile Image (Web)') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="bg_image[mobile]" class="selected-files" value="{{ $bg_image['mobile'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('App Image') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="bg_image[app]" class="selected-files" value="{{ $bg_image['app'] ?? '' }}">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                    {{-- End Background Image --}}


                    {{-- App Slider --}}
                    <div class="form-group row">
                        <div class="border-0 bg-transparent" style="min-width:100%">
                            <div class="card-header">
                                <h6 class="mb-0">{{ ('App Slider') }}</h6>
                            </div>
                            <div class="card-body">
                                    <div class="form-group">
                                        <div class="app-slider-target">
                                                <div class="row gutters-5">
                                                    @if ($app_slider != null)
                                                        @foreach ($app_slider as $key => $image)
                                                            <div class="col-md">
                                                                <div class="form-group">
                                                                    <label>{{ ('Image') }}</label>
                                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                        <div class="input-group-prepend">
                                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                                {{ ('Browse') }}
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-control file-amount">
                                                                            {{ ('Choose File') }}
                                                                        </div>
                                                                        <input type="hidden" name="app_slider[]" class="selected-files" value="{{ $image }}">
                                                                    </div>
                                                                    <div class="file-preview box sm">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Image') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="app_slider[]" class="selected-files">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <div class="col-md-auto">
                                                        <div class="form-group">
                                                            <br>
                                                            <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                                                <i class="las la-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                        <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                            data-content='
                                            <div class="row gutters-5">
                                                <div class="col-md">
                                                    <div class="form-group">
                                                        <label>{{ ('Image') }}</label>
                                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                            <div class="input-group-prepend">
                                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                    {{ ('Browse') }}
                                                                </div>
                                                            </div>
                                                            <div class="form-control file-amount">
                                                                {{ ('Choose File') }}
                                                            </div>
                                                            <input type="hidden" name="app_slider[]" class="selected-files">
                                                        </div>
                                                        <div class="file-preview box sm">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-auto">
                                                    <div class="form-group">
                                                        <br>
                                                        <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                                            <i class="las la-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            '
                                            data-target=".app-slider-target">
                                            {{ ('Add New') }}
                                        </button>
                                    </div>
                            </div>
                        </div>
                    </div>
                    {{-- End App Slider --}}

                    {{-- App Banner1 Images --}}
                    <div class="form-group row">
                        <div class="border-0 bg-transparent" style="min-width:100%">
                            <div class="card-header">
                                <h6 class="mb-0">{{ ('App Banner1 Images') }}</h6>
                            </div>
                            <div class="card-body">
                                    <div class="form-group">
                                        <div class="app-banner1-target">
                                                <div class="row gutters-5">
                                                    @if ($app_banner1 != null)
                                                        @foreach ($app_banner1 as $key => $image)
                                                            <div class="col-md">
                                                                <div class="form-group">
                                                                    <label>{{ ('Image') }}</label>
                                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                        <div class="input-group-prepend">
                                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                                {{ ('Browse') }}
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-control file-amount">
                                                                            {{ ('Choose File') }}
                                                                        </div>
                                                                        <input type="hidden" name="app_banner1[]" class="selected-files" value="{{ $image }}">
                                                                    </div>
                                                                    <div class="file-preview box sm">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Image') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="app_banner1[]" class="selected-files">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-auto">
                                                        <div class="form-group">
                                                            <br>
                                                            <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                                                <i class="las la-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                        <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                            data-content='
                                            <div class="row gutters-5">
                                                <div class="col-md">
                                                    <div class="form-group">
                                                        <label>{{ ('Image') }}</label>
                                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                            <div class="input-group-prepend">
                                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                    {{ ('Browse') }}
                                                                </div>
                                                            </div>
                                                            <div class="form-control file-amount">
                                                                {{ ('Choose File') }}
                                                            </div>
                                                            <input type="hidden" name="app_banner1[]" class="selected-files">
                                                        </div>
                                                        <div class="file-preview box sm">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-auto">
                                                    <div class="form-group">
                                                        <br>
                                                        <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                                            <i class="las la-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            '
                                            data-target=".app-banner1-target">
                                            {{ ('Add New') }}
                                        </button>
                                    </div>
                            </div>
                        </div>
                    </div>
                    {{-- End App Banner1 Images --}}

                    {{-- App Banner2 Images --}}
                    <div class="form-group row">
                        <div class="border-0 bg-transparent" style="min-width:100%">
                            <div class="card-header">
                                <h6 class="mb-0">{{ ('App Banner2 Images') }}</h6>
                            </div>
                            <div class="card-body">
                                    <div class="form-group">
                                        <div class="app-banner2-target">
                                                <div class="row gutters-5">
                                                    @if ($app_banner2 != null)
                                                        @foreach ($app_banner2 as $key => $image)
                                                            <div class="col-md">
                                                                <div class="form-group">
                                                                    <label>{{ ('Image') }}</label>
                                                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                        <div class="input-group-prepend">
                                                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                                {{ ('Browse') }}
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-control file-amount">
                                                                            {{ ('Choose File') }}
                                                                        </div>
                                                                        <input type="hidden" name="app_banner2[]" class="selected-files" value="{{ $image }}">
                                                                    </div>
                                                                    <div class="file-preview box sm">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                    <div class="col-md">
                                                        <div class="form-group">
                                                            <label>{{ ('Image') }}</label>
                                                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                                <div class="input-group-prepend">
                                                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                        {{ ('Browse') }}
                                                                    </div>
                                                                </div>
                                                                <div class="form-control file-amount">
                                                                    {{ ('Choose File') }}
                                                                </div>
                                                                <input type="hidden" name="app_banner2[]" class="selected-files">
                                                            </div>
                                                            <div class="file-preview box sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-md-auto">
                                                        <div class="form-group">
                                                            <br>
                                                            <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                                                <i class="las la-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                        <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                            data-content='
                                            <div class="row gutters-5">
                                                <div class="col-md">
                                                    <div class="form-group">
                                                        <label>{{ ('Image') }}</label>
                                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                                            <div class="input-group-prepend">
                                                                <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                                    {{ ('Browse') }}
                                                                </div>
                                                            </div>
                                                            <div class="form-control file-amount">
                                                                {{ ('Choose File') }}
                                                            </div>
                                                            <input type="hidden" name="app_banner2[]" class="selected-files">
                                                        </div>
                                                        <div class="file-preview box sm">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-auto">
                                                    <div class="form-group">
                                                        <br>
                                                        <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
                                                            <i class="las la-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            '
                                            data-target=".app-banner2-target">
                                            {{ ('Add New') }}
                                        </button>
                                    </div>
                            </div>
                        </div>
                    </div>
                    {{-- End App Banner2 Images --}}


                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('App Featured Image')}} <small>({{ ('1920 × 130 px') }})</small></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="app_featured_image" class="selected-files" value="{{ $category->app_featured_image ?? '' }}">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('App Home Page Image')}} <small>({{ ('1920 × 130 px') }})</small></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="app_home_page_image" class="selected-files" value="{{ $category->app_home_page_image ?? '' }}">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>

                    @php
                      $start_date = $category->start_date!=0?date('d-m-Y H:i:s', $category->start_date):date('d-m-Y H:i:s');
                      $end_date = $category->end_date!=0?date('d-m-Y H:i:s', $category->end_date):date('d-m-Y H:i:s');
                    @endphp

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="start_date">{{ ('Discount Date Range')}}</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control aiz-date-range" value="{{ $start_date.' to '.$end_date }}" name="date_range" placeholder="Select Date" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="discount">{{ ('Discount')}} </label>
                        <div class="col-sm-9">
                            <input type="number" lang="en" name="discount" value="{{ $category->discount }}" min="0" step="1" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="discount">{{ ('Discount Type')}} </label>
                        <div class="col-sm-9">
                            <select class="aiz-selectpicker" name="discount_type">
                                <option value="amount" <?php if($category->discount_type == 'amount') echo "selected";?> >{{ ('Flat') }}</option>
                                <option value="percent" <?php if($category->discount_type == 'percent') echo "selected";?> >{{ ('Percent') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ("Update products discount")}}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="update_products_discount">
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="alert alert-danger">
                        {{ ('If any product has discount or exists in flash deal, the discount will be replaced by this discount & time limit.') }}
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Meta Title')}}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="meta_title" value="{{ $category->meta_title }}" placeholder="{{ ('Meta Title')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Meta Description')}}</label>
                        <div class="col-md-9">
                            <textarea name="meta_description" rows="5" class="form-control">{{ $category->meta_description }}</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Slug')}}</label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{ ('Slug')}}" id="slug" name="slug" value="{{ $category->slug }}" class="form-control">
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
                    @if (get_setting('category_wise_commission') == 1)
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ ('Commission Rate')}}</label>
                            <div class="col-md-9 input-group">
                                <input type="number" lang="en" min="0" step="0.01" id="commision_rate" name="commision_rate" value="{{ $category->commision_rate }}" class="form-control">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Filtering Attributes')}}</label>
                        <div class="col-md-9">
                            <select class="select2 form-control aiz-selectpicker" name="filtering_attributes[]" data-toggle="select2" data-placeholder="Choose ..."data-live-search="true" data-selected="{{ $category->attributes->pluck('id') }}" multiple>
                                @foreach (\App\Models\Attribute::all() as $attribute)
                                    <option value="{{ $attribute->id }}">{{ $attribute->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Product Variation Attributes')}}</label>
                        <div class="col-md-9">
                            <select class="select2 form-control aiz-selectpicker" name="variation_attributes[]" data-toggle="select2" data-placeholder="Choose ..."data-live-search="true" multiple>
                                @foreach (\App\Models\Attribute::all() as $attribute)
                                    <option <?php if(in_array($attribute->id,explode(',',$category->variation_attributes))){echo 'selected="selected"';}?> value="{{ $attribute->id }}">{{ $attribute->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Product Variation Color')}}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="variation_color" value="1" @if($category->variation_color == 1) checked @endif>
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="status">{{ ('Status') }}</label>
                        <div class="col-sm-9">
                            <select name="status" id="status" class="form-control">
                                <option value="1" {{ $category->status == 1 ? 'selected' : '' }}>{{ ('Active')}}</option>
                                <option value="0" {{ $category->status == 0 ? 'selected' : '' }}>{{ ('Inactive')}}</option>
                            </select>
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
@endsection

@section('script')
    <script>
        let old_slug = "{{ $category->slug }}";

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
        })

        $('#slug').on('keyup', function() {
            var slug = $(this).val();
            if (old_slug != slug) {
                $('#rewrite_url').prop('checked', true);
            }else{
                $('#rewrite_url').prop('checked', false);
            }
        });
    </script>
@endsection
