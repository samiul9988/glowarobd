@php
    /*$featured_products = Cache::remember('featured_products', 3600, function () {
        return filter_products(\App\Models\Product::where('published', 1)->where('featured', '1'))->paginate(12);
    });*/
    $featured_products = filter_products(\App\Models\Product::where('published', 1)->where('featured', '1'))->orderByRaw('RAND()')->limit(48)->get();
@endphp

@if (count($featured_products) > 0)
    @if(intval(@$_GET['page'])==0)
    <section class="mb-1">
        <div class="container">
            <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded section_holder">
                <div class="d-flex mb-3 align-items-baseline border-bottom section_title_holder">
                    <h3 class="h5 fw-700 mb-0">
                        <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Featured Products') }}</span>
                    </h3>
                </div>
                <div id="featured_products_append" class="row aiz-carousel-ex gutters-10 half-outside-arrow products_holder" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                    <input type="hidden" name="featured_products_page" value="0" max-page="1">
    @endif

                    @foreach ($featured_products as $key => $product)
                    <div class="carousel-box col-md-2 col-6 px-1 fp-<?=intval(@$_GET['page'])?>" @if(intval(@$_GET['page'])>0) style="display:none" @endif>                        
                        @include(config('app.theme').'frontend.partials.product_box_1',['product' => $product])
                    </div>
                    @endforeach
    @if(intval(@$_GET['page'])==0)
                </div>

                <div class="row">
                    <div class="col-sm-12 text-center featured_product_loading" data-end="false">
                        <span id="featured_loading_icon" style="display: none; font-size: 50px;"><i class="fa fa-spin fa-spinner"></i></span>
                        <div class="alert alert-info featured_no_data_found mt-4" style="display: none"><i class="fa fa-exclamation-circle"></i> No data found</div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    @endif
@endif