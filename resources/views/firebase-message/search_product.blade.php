@if($products)
@foreach ($products as $product)
    <div class="sp_item">
        <div class="card text-sm mb-2 mx-auto border" style="max-width: 450px;">
            <div class="row no-gutters p-2">
                <div class="col-auto">
                    @php
                        $image = $product['thumbnail_img'] ?? $product['thumbnail_image']
                    @endphp
                    <img
                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                        data-src="{{ static_asset($image) }}"
                        class="img-fit lazyload size-60px rounded"
                        alt="{{ $product['name'] }}"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                        style="width: 50px; height: 50px; object-fit: cover;"
                    >
                </div>
                <div class="col-auto">
                    <div class="card-body p-0 pl-1">
                        <p class="line-clamp-2 m-0 p-0">{{ $product['name'] }}</p>
                        <span class="m-0 text-primary"><b>{{ $product['main_price'] }}</b></span>
                        <span class="m-0 text-primary"><s>{{ $product['stroked_price'] }}</s></span>
                    </div>
                </div>
            </div>
            <div class="row gutters-5">
                <div class="col">
                    <a href="{{ route('product', $product['slug']) }}" target="_blank" class="btn btn-secondary btn-sm  btn-block">View</a>
                </div>
                <div class="col">
                    <button type="button" data-slug="{{ route('product', $product['slug']) }}" onclick="sendAsLink(this)" class="btn btn-primary btn-sm btn-block">Send</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endif