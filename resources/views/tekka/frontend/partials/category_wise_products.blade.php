@if($products != null && count($products) > 0)
@foreach($products->slice(0, 8) as $key => $product)
<div class="carousel-box">
    @include(config('app.theme') . 'frontend.partials.product_box_1', ['product' => $product])
</div>
@endforeach
@else
<p class="text-center mx-auto text-danger">No product found</p>
@endif