@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Category Information')}}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                	@csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Name')}}</label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{ ('Name')}}" id="name" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Parent Category')}}</label>
                        <div class="col-md-9">
                            <select class="select2 form-control aiz-selectpicker" name="parent_id" data-toggle="select2" data-placeholder="Choose ..." data-live-search="true">
                                <option value="0">{{ ('No Parent') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                    @foreach ($category->childrenCategories as $childCategory)
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
                            <input type="number" name="order_level" class="form-control" id="order_level" placeholder="{{ ('Order Level')}}">
                            <small>{{ ('Higher number has high priority')}}</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Type')}}</label>
                        <div class="col-md-9">
                            <select name="digital" required class="form-control aiz-selectpicker mb-2 mb-md-0">
                                <option value="0">{{ ('Physical')}}</option>
                                <option value="1">{{ ('Digital')}}</option>
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
                            <input type="text" name="child_bg_color" class="form-control" id="child_bg_color" placeholder="Input hexadecimal color code. E.g: #ffffff">
                        </div>
                    </div>

                    {{-- Banner Images --}}
                    <div class="form-group row">
                        <div class="border-0 bg-transparent" style="min-width:100%">
                            <div class="card-header">
                                <h6 class="mb-0">{{ ('Banner') }}</h6>
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
                                                                <input type="hidden" name="banner[web]" class="selected-files">
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
                                                                <input type="hidden" name="banner[mobile]" class="selected-files">
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
                                                                <input type="hidden" name="banner[app]" class="selected-files">
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
                                <h6 class="mb-0">{{ ('Page Banner') }}</h6>
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
                                                                <input type="hidden" name="pageBanner[web]" class="selected-files">
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
                                                                <input type="hidden" name="pageBanner[mobile]" class="selected-files">
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
                                                                <input type="hidden" name="pageBanner[app]" class="selected-files">
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
                                <h6 class="mb-0">{{ ('Icon') }}</h6>
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
                                                                <input type="hidden" name="icon[web]" class="selected-files">
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
                                                                <input type="hidden" name="icon[mobile]" class="selected-files">
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
                                                                <input type="hidden" name="icon[app]" class="selected-files">
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
                                                                <input type="hidden" name="featured_icon[web]" class="selected-files">
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
                                                                <input type="hidden" name="featured_icon[mobile]" class="selected-files">
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
                                                                <input type="hidden" name="featured_icon[home_page]" class="selected-files">
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
                                                                <input type="hidden" name="featured_icon[app]" class="selected-files">
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
                                <h6 class="mb-0">{{ ('Background Image') }}</h6>
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
                                                                <input type="hidden" name="bg_image[web]" class="selected-files">
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
                                                                <input type="hidden" name="bg_image[mobile]" class="selected-files">
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
                                                                <input type="hidden" name="bg_image[app]" class="selected-files">
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
                                <input type="hidden" name="app_featured_image" class="selected-files">
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
                                <input type="hidden" name="app_home_page_image" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Meta Title')}}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="meta_title" placeholder="{{ ('Meta Title')}}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Meta Description')}}</label>
                        <div class="col-md-9">
                            <textarea name="meta_description" rows="5" class="form-control"></textarea>
                        </div>
                    </div>
                    @if (get_setting('category_wise_commission') == 1)
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ ('Commission Rate')}}</label>
                            <div class="col-md-9 input-group">
                                <input type="number" lang="en" min="0" step="0.01" placeholder="{{ ('Commission Rate')}}" id="commision_rate" name="commision_rate" class="form-control">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Filtering Attributes')}}</label>
                        <div class="col-md-9">
                            <select class="select2 form-control aiz-selectpicker" name="filtering_attributes[]" data-toggle="select2" data-placeholder="Choose ..."data-live-search="true" multiple>
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
                                    <option value="{{ $attribute->id }}">{{ $attribute->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Product Variation Color')}}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="variation_color" value="1">
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="status">{{ ('Status') }}</label>
                        <div class="col-sm-9">
                            <select name="status" id="status" class="form-control">
                                <option value="1" selected>{{ ('Active')}}</option>
                                <option value="0">{{ ('Inactive')}}</option>
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
