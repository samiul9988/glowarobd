@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <h5 class="mb-0 h6">{{ ('Brand Information') }}</h5>
    </div>

    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-fill border-light">
                    @foreach (\App\Models\Language::all() as $key => $language)
                        <li class="nav-item">
                            <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3"
                                href="{{ route('brands.edit', ['id' => $brand->id, 'lang' => $language->code]) }}">
                                <img src="{{ static_asset('assets/img/flags/' . $language->code . '.png') }}" height="11"
                                    class="mr-1">
                                <span>{{ $language->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <form class="p-4" action="{{ route('brands.update', $brand->id) }}" method="POST"
                    enctype="multipart/form-data">
                    <input name="_method" type="hidden" value="PATCH">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{ ('Name') }} <i
                                class="las la-language text-danger" title="{{ ('Translatable') }}"></i></label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Name') }}" id="name" name="name"
                                value="{{ $brand->getTranslation('name', $lang) }}" class="form-control" required>
                        </div>
                    </div>
                    @php
                        $start_date =
                            $brand->start_date != 0 ? date('d-m-Y H:i:s', $brand->start_date) : date('d-m-Y H:i:s');
                        $end_date = $brand->end_date != 0 ? date('d-m-Y H:i:s', $brand->end_date) : date('d-m-Y H:i:s');
                    @endphp

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label"
                            for="start_date">{{ ('Discount Date Range') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control aiz-date-range"
                                value="{{ $start_date . ' to ' . $end_date }}" name="date_range" placeholder="Select Date"
                                data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to "
                                autocomplete="off" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="discount">{{ ('Discount') }} </label>
                        <div class="col-sm-9">
                            <input type="number" lang="en" name="discount" value="{{ $brand->discount }}"
                                min="0" step="1" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="discount">{{ ('Discount Type') }} </label>
                        <div class="col-sm-9">
                            <select class="aiz-selectpicker" name="discount_type">
                                <option value="amount" <?php if ($brand->discount_type == 'amount') {
                                    echo 'selected';
                                } ?>>{{ ('Flat') }}</option>
                                <option value="percent" <?php if ($brand->discount_type == 'percent') {
                                    echo 'selected';
                                } ?>>{{ ('Percent') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Update products discount') }}</label>
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
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Logo') }}
                            <small>({{ ('120x80') }})</small></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                        {{ ('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="logo" value="{{ $brand->logo }}" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{ ('Page Banner') }}
                            <small>({{ ('1920 × 130 px') }})</small></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                        {{ ('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="page_banner" class="selected-files"
                                    value="{{ $brand->page_banner }}">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{ ('Meta Title') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="meta_title"
                                value="{{ $brand->meta_title }}" placeholder="{{ ('Meta Title') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{ ('Meta Description') }}</label>
                        <div class="col-sm-9">
                            <textarea name="meta_description" rows="8" class="form-control">{{ $brand->meta_description }}</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{ ('Slug') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Slug') }}" id="slug" name="slug"
                                value="{{ $brand->slug }}" class="form-control">
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
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="status">{{ ('Status') }}</label>
                        <div class="col-sm-9">
                            <select name="status" id="status" class="form-control">
                                <option value="1" {{ $brand->status === 1 ? 'selected' : '' }}>{{ ('Active')}}</option>
                                <option value="0" {{ $brand->status === 0 ? 'selected' : '' }}>{{ ('Inactive')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ ('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
		let old_slug = "{{ $brand->slug }}";

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
