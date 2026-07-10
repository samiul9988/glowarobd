@extends(config('app.theme').'frontend.layouts.app')

@section('meta')
<x-seo :meta="[
    'title' => @$category->name ?: 'All Categories',
    'description' => @$category->meta_description ?: 'Browse all categories available in our store.',
]" />
@endsection

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                @if(isset($category))
                <h1 class="fw-600 h4">{{ $category->name }}</h1>
                @else
                <h1 class="fw-600 h4">{{ ('All Categories') }}</h1>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="mb-4">
    <div class="container">
    <ul class="list-unstyled mb-0 all-categories_list">
        @php
            // $reverseCategory = $categories->reverse();
            // dd($reverseCategory);
        @endphp
        @foreach ($categories as $key => $category)
        @php
            // dd($category);
            if(array_key_exists('featured_icon',$category->toArray())){
                $featured_icon = json_decode($category->featured_icon, true);
            }
        @endphp
            @if($agent->isMobile())
                @if ($key % 2 == 0)
                    <div class="category-wrapper">
                @endif
            @else
                @if ($key % 3 == 0)
                    <div class="category-wrapper">
                @endif
            @endif

            @php
                $subcategories = \App\Models\Category::query()
                    ->where('level', '!=', 0)
                    ->where('parent_id', $category->id)
                    ->get();
                $imageMobile = $featured_icon['mobile'] ?? '';
                $imageWeb = $featured_icon['web'] ?? '';
                $image = $agent->isMobile() ? $imageMobile : $imageWeb;
                $oppositeImage = $agent->isMobile() ? $imageWeb : $imageMobile;
            @endphp
            <li class="p-0 m-0 bg-white text-center category-item">
                <a href="{{ $subcategories->isEmpty() ? route('products.category', $category->slug) : 'javascript:void(0);' }}" class="d-block bg-white-ex text-reset">
                    <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($image ?: $oppositeImage) }}" alt="{{ $category->name }}" class="lazyload" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                    <div class="fs-16 fw-600 mt-2 category-name">
                        {{ $category->name }}
                        @if (!$subcategories->isEmpty())
                            <span>
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        @endif
                    </div>
                </a>


                @if(!$subcategories->isEmpty())
                <ul class="subcategories-list">
                    @foreach ($subcategories as $subcategory)
                        <li>
                            <a href="{{ route('products.category', $subcategory->slug) }}">
                                <span>{{ $subcategory->name }}</span>
                                <span><i class="fas fa-chevron-right"></i></span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @endif
            </li>

            @if($agent->isMobile())
                @if ($key % 2 == 1 || $key == $categories->count() - 1)
                    <div class="sub-categories"></div>
                    </div>
                @endif
            @else
                @if ($key % 3 == 2 || $key == $categories->count() - 1)
                    <div class="sub-categories"></div>
                    </div>
                @endif
            @endif
        @endforeach
    </ul>

    </div>
</section>

@endsection
