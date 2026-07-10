@extends(config('app.theme').'frontend.layouts.user_panel')

@section('panel_content')
    <div class="aiz-titlebar mt-2 mb-4">
      <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ ('Add Your Product') }}</h1>
        </div>
      </div>
    </div>
    <ul class="nav nav-tabs nav-fill border-light">
			@foreach (\App\Models\Language::all() as $key => $language)
				<li class="nav-item">
					<a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3" href="{{ route('customer_products.edit', ['id'=>$product->id, 'lang'=> $language->code] ) }}">
						<img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
						<span>{{ $language->name }}</span>
					</a>
				</li>
        @endforeach
		</ul>

    <form class="" action="{{route('customer_products.update', $product->id)}}" method="POST" enctype="multipart/form-data" id="choice_form">
        <input name="_method" type="hidden" value="PATCH">
        <input type="hidden" name="lang" value="{{ $lang }}">
        @csrf
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('General')}}</h5>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Product Name')}} <span class="text-danger">* <i class="las la-language" title="{{ ('Translatable')}}"></i></span></label>
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="name" value="{{ $product->getTranslation('name') }}" placeholder="{{ ('Product Name')}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Product Category')}} <span class="text-danger">*</span></label>
                    <div class="col-md-10">
                        <select class="form-control aiz-selectpicker" data-placeholder="{{ ('Select a Category')}}" id="categories" name="category_id" data-live-search="true" data-selected={{ $product->category_id }} required>
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
                    <label class="col-md-2 col-from-label">{{ ('Product Brand')}} <span class="text-danger">*</span></label>
                    <div class="col-md-10">
                        <select class="form-control selectpicker" data-placeholder="{{ ('Select a brand')}}" data-live-search="true"  id="brands" name="brand_id">
                            <option value=""></option>
                            @foreach (\App\Models\Brand::all() as $brand)
                                <option value="{{ $brand->id }}" @if($brand->id == $product->brand_id) selected @endif>{{ $brand->getTranslation('name') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Product Unit')}} <span class="text-danger">* <i class="las la-language" title="{{ ('Translatable')}}"></i></span></label>
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="unit" value="{{ $product->getTranslation('unit') }}" placeholder="{{ ('Product unit')}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Condition')}} <span class="text-danger">*</span></label>
                    <div class="col-md-10">
                        <select class="form-control selectpicker" data-placeholder="{{ ('Select a condition')}}" id="conditon" name="conditon" required>
                            <option value="new" @if ($product->conditon == 'new') selected @endif>{{ ('New')}}</option>
                            <option value="used" @if ($product->conditon == 'used') selected @endif>{{ ('Used')}}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Location')}} <span class="text-danger">*</span></label>
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="location" value="{{ $product->location }}" placeholder="{{ ('Location')}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Product Tag')}} <span class="text-danger">*</span></label>
                    <div class="col-md-10">
                        <input type="text" class="form-control aiz-tag-input" name="tags[]" value="{{ $product->tags }}" placeholder="{{ ('Type & hit enter')}}">
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Images')}}</h5>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Main Images')}} <span class="text-danger">*</span></label>
                    <div class="col-md-10">
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ ('Choose File') }}</div>
                            <input type="hidden" name="photos" class="selected-files" value="{{ $product->photos }}">
                        </div>
                        <div class="file-preview box sm">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Thumbnail Image')}} <span class="text-danger">*</span></label>
                    <div class="col-md-10">
                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ ('Choose File') }}</div>
                            <input type="hidden" name="thumbnail_img" class="selected-files" value="{{ $product->thumbnail_img }}">
                        </div>
                        <div class="file-preview box sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Videos')}}</h5>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Video From')}}</label>
                    <div class="col-md-10">
                        <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity" name="video_provider">
                            <option value="youtube" @if ($product->video_provider == 'youtube') selected @endif>{{ ('Youtube')}}</option>
                            <option value="dailymotion" @if ($product->video_provider == 'dailymotion') selected @endif>{{ ('Dailymotion')}}</option>
                            <option value="vimeo" @if ($product->video_provider == 'vimeo') selected @endif>{{ ('Vimeo')}}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Video URL')}}</label>
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="video_link" value="{{ $product->video_link }}" placeholder="{{ ('Video link')}}">
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Meta Tags')}}</h5>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Meta Title')}}</label>
                    <div class="col-md-10">
                        <input type="text" name="meta_title" value="{{ $product->meta_title }}" class="form-control" placeholder="{{ ('Meta Title')}}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Description')}}</label>
                    <div class="col-md-10">
                        <textarea name="meta_description" rows="8" class="form-control">{{ $product->meta_description }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Meta Image')}}</label>
                    <div class="col-md-10">
                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ ('Choose File') }}</div>
                            <input type="hidden" name="meta_img" class="selected-files" value="{{ $product->meta_img }}">
                        </div>
                        <div class="file-preview box sm">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Slug')}}</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="slug" value="{{ $product->slug }}" placeholder="{{ ('Slug')}}">
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Price')}}</h5>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Unit Price')}} <span class="text-danger">*</span></label>
                    <div class="col-md-10">
                        <input type="number" lang="en" value="{{ $product->unit_price }}"  min="0" step="0.01" class="form-control" name="unit_price" placeholder="{{ ('Unit Price')}} ({{ ('Base Price')}})" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Description')}}</h5>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-md-2 col-from-label">{{ ('Description')}} <span class="text-danger">* <i class="las la-language" title="{{ ('Translatable')}}"></i></span></label>
                    <div class="col-md-10">
                        <div class="mb-3">
                            <textarea class="aiz-text-editor" name="description" required>{{$product->getTranslation('description')}}</textarea>
                        </div>
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
                    <label class="col-md-2 col-from-label">{{ ('PDF')}}</label>
                    <div class="col-md-10">
                        <div class="input-group" data-toggle="aizuploader" data-type="document">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ ('Choose File') }}</div>
                            <input type="hidden" name="pdf" class="selected-files" value="{{ $product->pdf }}">
                        </div>
                        <div class="file-preview box sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mar-all text-right">
            <button type="submit" name="button" class="btn btn-primary">{{ ('Update Product') }}</button>
        </div>
    </form>

@endsection
