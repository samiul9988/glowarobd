@extends(config('app.theme').'frontend.layouts.app')

@section('content')
<section class="pt-4 {{ $agent->isMobile() ? 'mb-2' : 'mb-4' }}">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                @if(isset($category))
                    <h1 class="fw-600 h4">{{ $category->name }}</h1>
                @else
                    <h1 class="fw-600 h4">All Categories</h1>
                @endif
            </div>
            <div class="col-lg-6 {{ $agent->isMobile() ? 'd-none' : '' }}">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ ('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        @if(isset($category))
                        <a class="text-reset" href="{{ route('products.category', ['category_slug'=>$category->slug]) }}">"{{ $category->name }}"</a>
                        @else
                        <a class="text-reset" href="{{ route('categories.all') }}">"{{ ('All Categories') }}"</a>
                        @endif

                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="mb-4">
    <div class="container">
        <ul class="list-unstyled mb-0 row gutters-5 categories_list">
            @foreach ($categories as $key => $category)
            <li class="minw-0 col-4 col-md-2 mb-0 px-0 border border-width-1 bg-white text-center">
                <a href="{{ route('products.category', $category->slug) }}" class="d-block rounded bg-white-ex p-1 text-reset shadow-sm py-2">
                    @php
                        $images = json_decode($category->featured_icon, true);
                        if(is_array($images)) {
                            $img = $agent->isMobile() ? $images['mobile'] : $images['web'];
                        }else{
                            $img = $category->featured_icon;
                        }
                    @endphp
                    <img
                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                        data-src="{{ uploaded_asset($img) }}"
                        alt="{{ $category->getTranslation('name') }}"
                        class="lazyload"
                        width="78"
                        height="78"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                    >
                    <div class="text-truncate fs-12 fw-600 mt-2 opacity-70">{{ $category->getTranslation('name') }}</div>
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</section>

{{--
<section class="mb-4">
    <div class="container">
        @foreach ($categories as $key => $category)
            <div class="mb-3 bg-white shadow-sm rounded">
                <div class="p-3 border-bottom fs-16 fw-600">
                    <a href="{{ route('products.category', $category->slug) }}" class="text-reset">{{  $category->getTranslation('name') }}</a>
                </div>
                <div class="p-3 p-lg-4">
                    <div class="row">
                        @foreach (\App\Utility\CategoryUtility::get_immediate_children_ids($category->id) as $key => $first_level_id)
                        <div class="col-lg-4 col-6 text-left">
                            <h6 class="mb-3"><a class="text-reset fw-600 fs-14" href="{{ route('products.category', \App\Models\Category::find($first_level_id)->slug) }}">{{ \App\Models\Category::find($first_level_id)->getTranslation('name') }}</a></h6>
                            <ul class="mb-3 list-unstyled pl-2">
                                @foreach (\App\Utility\CategoryUtility::get_immediate_children_ids($first_level_id) as $key => $second_level_id)
                                <li class="mb-2">
                                    <a class="text-reset" href="{{ route('products.category', \App\Models\Category::find($second_level_id)->slug) }}" >{{ \App\Models\Category::find($second_level_id)->getTranslation('name') }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
--}}

@endsection
