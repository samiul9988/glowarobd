@if (count($featured_categories) > 0)
    <div class="container d-none categories_sec pb-md-4 mt-md-4">
        <div class="row">
            {{-- Blank --}}
        </div>
        <ul class="list-unstyled mb-0 row gutters-5 categories_list ">
            @foreach ($featured_categories->reverse() as $key => $category)
            @php
                $featured_icon = json_decode($category->featured_icon, true);
            @endphp
                <li class="minw-0 mb-0 px-0 bg-white text-center">
                    <a href="{{ route('products.category', $category->slug) }}"
                        class="d-block rounded bg-white-ex p-1 text-reset py-2">
                        <img src="{{ uploaded_asset($agent->isMobile() ? ($featured_icon['mobile'] ?? '') : ($featured_icon['web'] ?? '')) }}"
                            data-src="{{ uploaded_asset($agent->isMobile() ? ($featured_icon['mobile'] ?? '') : ($featured_icon['web'] ?? '')) }}"
                            alt="{{ $category->getTranslation('name') }}" class="lazyload" width="78" height="78"
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                        <div class="product-title  fs-18 fw-600 mt-2">
                            {{ $category->getTranslation('name') }}</div>
                    </a>
                </li>
            @endforeach
            <li class="minw-0 mb-0 px-0 bg-white text-center">
                <a href="{{ route('search') }}" class="d-block rounded bg-white-ex p-1 text-reset py-2">
                    <img src="{{ static_asset('assets/img/more.png') }}" alt="More Button" class="lazyload"
                        width="78" height="78"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                    <div class="product-title text-truncate fs-18 fw-600 mt-2">More</div>
                </a>
            </li>
        </ul>
    </div>
@endif
