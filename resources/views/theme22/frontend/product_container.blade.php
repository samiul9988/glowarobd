@foreach ($products as $product)
    <div class="col">
        @include(config('app.theme').'frontend.partials.product_box_1',['product' => $product])
    </div>
@endforeach
