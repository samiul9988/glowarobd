@if($data)
<div class="card mb-3 p-3">
    <div class="d-flex align-items-center">
        <div class="image">
            <img src="https://glowarobd.com/public/{{$data['avatar_original']}}" class="rounded" width="155" height="155" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/female-and-male-icon.jpg') }}';" style="object-fit: cover;">
        </div>
        <div class="ml-3 w-100">
            <h4 class="mb-0 mt-0">{{ @$data['name'] }}</h4>
            <span>Group: {{ $data['group']['name'] }}</span>
            <div class="p-2 mt-2 bg-primary d-flex justify-content-between rounded text-white stats">
                <div class="d-flex flex-column">
                    <span class="articles">Gender</span>
                    <span class="number1">{{ isset($data['gender']) ? $data['gender'] : 'NULL' }}</span>
                </div>
                <div class="d-flex flex-column">
                    <span class="followers">Date of Birth</span>
                    <span class="number2">{{ isset($data['date_of_birth']) ? date('j M, Y', (intval($data['date_of_birth'])/1000)) : 'NULL';}}</span>
                </div>
                <div class="d-flex flex-column">
                    <span class="rating">Email</span>
                    <span class="number3">{{ $data['email'] ?? 'NULL' }}</span>
                </div>
            </div>
            <div class="button mt-2 d-flex flex-row align-items-center">
                <a href="{{route('customers.details', @$data['id'])}}" class="btn btn-sm btn-outline-primary w-100">Profile</a>
                <a href="tel:{{ @$data['phone'] }}" class="btn btn-sm btn-primary px-0 w-100 ml-2">{{ @$data['phone'] }}</a>
            </div>
        </div>
    </div>
</div>
<div class="card p-3">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="pills-customer-tab" data-toggle="pill" data-target="#pills-customer" type="button" role="tab" aria-controls="pills-customer" aria-selected="true">Customer</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="pills-product-tab" data-toggle="pill" data-target="#pills-product" type="button" role="tab" aria-controls="pills-product" aria-selected="false">Product</a>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-customer" role="tabpanel" aria-labelledby="pills-customer-tab">
            @if(count($data['orders']) > 0)
                <div class="d-flex justify-content-between">
                    <p class="m-0"><b>Status:</b> {{ @$data['orders'][0]['delivery_status'] }}</p>
                    <p class="m-0"><b>Code:</b> {{ @$data['orders'][0]['code'] }}</p>
                </div>
                <div class="d-flex justify-content-between">
                    <p class="m-0"><b>Grand Total:</b> {{ @single_price($data['orders'][0]['grand_total']) }}</p>
                    <p class="m-0"><b>Order Time:</b> {{ @$data['orders'][0]['created_at'] }}</p>
                </div>
                <div class="cs_products">
                    @foreach($data['order_products'] as $details)
                        @php
                            $product = \App\Models\Product::find($details['product_id']);
                        @endphp
                        <a href="{{ route('product', $product->slug) }}" target="_blank" title="{{ $product->getTranslation('name') }}">
                            <div class="card text-sm mb-0">
                                <div class="row no-gutters p-2">
                                    <div class="col-auto">
                                        <img
                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                                            class="img-fit lazyload size-60px rounded"
                                            alt="{{ $product->getTranslation('name') }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            style="width: 50px; height: 50px; object-fit: cover;"
                                        >
                                    </div>
                                    <div class="col-auto">
                                        <div class="card-body p-0 pl-1">
                                            <p class="line-clamp-2 m-0 p-0">{{ $product->getTranslation('name') }}</p>
                                            <span class="m-0 text-primary"><b>{{ single_price($details['price']) }}</b> x {{ @$details['quantity'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-danger text-center">No orders yet</p>
            @endif
        </div>
        <div class="tab-pane fade" id="pills-product" role="tabpanel" aria-labelledby="pills-product-tab">
            <div class="suggested_products style-4" style="height: 60vh; overflow-y: auto;">
                <div class="search_preloader " style="position: relative;margin-bottom:30px">
                    <div class="input-group mb-3">
                        <input type="text" class="border-0 border-lg form-control" id="product_search_keyup" placeholder="Search product ..." >
                    </div>
                    <div class="search-preloader search_preloader_position d-none">
                        <div class="dot-loader">
                            <div></div><div></div><div></div>
                        </div>
                    </div>
                </div>

                <div class="product_data_push">
                    @foreach(data_get($data, 'products.data', []) as $product)
                        <div class="sp_item">
                            <div class="card text-sm mb-2 mx-auto border" style="max-width: 450px;">
                                <div class="row no-gutters p-2">
                                    <div class="col-auto">
                                        <img
                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            data-src="{{ static_asset($product['thumbnail_image']) }}"
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
                </div>
            </div>
        </div>
    </div>
</div>



@else
<div class="card mb-3 p-3">
    <div class="card-body">
        <div class="text-center">
            <h5 class="text-primary text-center">Unable to find customer</h5>
        </div>
    </div>
</div>
@endif

<style>
    .search_preloader_position {
        position: absolute;
        top: 100%;
        left: 50%;
        -webkit-transform: translateX(-50%);
        transform: translateX(-50%);
    }
</style>

<script type="text/javascript">
    function sendAsLink(identifier){
        // e.preventDefault();
        let slug = $(identifier).data('slug');
        $('#message').val(slug);
        $("#sendSmsForm .input-group-text").click();
    }

    $('#product_search_keyup').on('keyup', function(){
        var value = $(this).val();
        if(value.length > 2 || value.length == 0){
            $.post({
                url: "{{route('msg.search.product')}}",
                data: {_token: AIZ.data.csrf, search: value},
                beforeSend: function(){
                    $('.search_preloader_position').removeClass('d-none')
                },
                success: function(data){
                    if(data){
                        $('.product_data_push').html(data);
                    } else{
                        $('.product_data_push').html('<h5>Product not match</h5>');
                    }

                },
                complete: function() {
                     $('.search_preloader_position').addClass('d-none')
                }
            })
        }
    });

</script>
