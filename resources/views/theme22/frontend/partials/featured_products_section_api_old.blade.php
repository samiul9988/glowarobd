@php
    // Check if the user is authenticated
    if (auth()->check()) {
        // Get the authenticated user
        $user = auth()->user();

        // Make the API request with the user's token
        $response = Illuminate\Support\Facades\Http::withToken($user->api_token)->get(url('/').'/api/v2/products?page=1&orderby=rand&limit=48');
    }else{
        $response = Illuminate\Support\Facades\Http::get(url('/').'/api/v2/products?page=1&orderby=rand&limit=48');
    }
@endphp

@if ($response && $response->successful() && isset($response->json()['data']) && count($response->json()['data']) > 0)
    <section class="mb-1">
        <div class="container px-2 px-sm-0">
            <div class="px-2 py-2 px-md-4 bg-white shadow-sm rounded section_holder">
                <div class="d-flex mb-3 align-items-baseline section_title_holder mx-0">
                    <h3 class="h5 fw-700 mb-0">
                        <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ ('Featured Products') }}</span>
                    </h3>
                </div>
                <div id="featured_products_append" class="row px-1 aiz-carousel-ex gutters-5 half-outside-arrow products_holder" data-items="6" data-xl-items="5" data-lg-items="4" data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                    <input type="hidden" name="featured_products_page" value="0" max-page="1">

                    @if ($response->successful())
                        @foreach ($response->json()['data'] as $key => $product)
                            @if($product['current_stock'] > 0)
                                <div class="carousel-box col-lg-2 col-md-4 col-sm-6 col-6 px-1 fp-<?=intval(@$_GET['page'])?>">
                                    @include(config('app.theme').'frontend.partials.product_box_2',['product' => $product])
                                </div>
                            @endif
                        @endforeach 
                    @endif


                </div>

                <div class="row">
                    <div class="col-sm-12 text-center featured_product_loading" data-end="false">
                        <span id="featured_loading_icon" style="display: none; font-size: 50px;"><i class="fa fa-spin fa-spinner"></i></span>
                        <div class="alert alert-info featured_no_data_found mt-4" style="display: none"><i class="fa fa-exclamation-circle"></i> No data found</div>
                    </div>
                </div>

                <div id="load" class="mx-auto align-items-center justify-content-center mt-4" style="display: none">
                    <img src="{{ static_asset('assets/img/loading.gif') }}" title="Loading.." alt="Loading..." srcset="{{ static_asset('assets/img/loading.gif') }}" width="100" class="text-center">
                </div>

                {{-- <div id="btn-fp-load-more-wrapper" class="mx-auto d-flex align-items-center justify-content-center mt-2">
                    <a href="{{ route('search') }}" id="btn-fp-load-more" class="view_btn-ex btn-view-more d-inline-block">{{ ('Load More') }}<i class="fa-sharp fa-solid fa-right-long ml-1" ></i></a>
                </div> --}}

            </div>
            {{-- <div class="container p-0">
                <hr class="horizontal-line my-2">
            </div> --}}
        </div>
    </section>
@endif
