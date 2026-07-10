@extends(config('app.theme').'frontend.layouts.app')

@section('meta')
<x-seo />
@endsection

@section('content')

<section class="pt-4 mb-4 compare-header mt-md-2">
    <div class="container text-center">
        <div class="row m-0">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ ('Compare Product')}}</h1>
            </div>
            <div class="col-lg-6">
                <div class="buttonWrapper">
                     <a href="{{ route('compare.reset') }}" style=" text-decoration: none;" class="reset-btn btn  btn-sm ">
                        <i class="fas fa-redo"></i>
                        {{ ('Reset')}}
                    </a>
                     <a href=""  class="add-product btn  btn-sm ">
                        <i class="fas fa-plus"></i>
                        {{ ('Add Product')}}
                    </a>

                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-4">
    <div class="container text-left comapare-product-main">
        <div class="bg-white shadow-none rounded">
            <!-- <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="fs-15 fw-600">{{ ('Comparison')}}</div>
                <a href="{{ route('compare.reset') }}" style="text-decoration: none;" class="btn btn-soft-primary btn-sm fw-600">{{ ('Reset Compare List')}}</a>
            </div> -->
            @if(Session::has('compare'))
                @if(count(Session::get('compare')) > 0)
                    <div class="p-md-3">
                        <table class="table table-responsive border-0 mb-0">
                            <tbody>
                                <tr>
                                    <td   scope="row"></td>
                                    @foreach (Session::get('compare') as $key => $item)
                                        <td  class="img-box" style="text-align:center;">
                                            <img loading="lazy" src="{{ uploaded_asset(\App\Models\Product::find($item)->thumbnail_img) }}" alt="{{ ('Product Image') }}" class="img-fluid">
                                            <span class="remove-from-compare"><i class="fas fa-times"></i></span>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th scope="col" style="width:20%" class=" border-top-0">
                                        {{ ('Name')}}
                                    </th>

                                    @foreach (Session::get('compare') as $key => $item)
                                        <td scope="col" style="width:28%" class="">
                                            <a class="text-reset " href="{{ route('product', \App\Models\Product::find($item)->slug) }}">{{ \App\Models\Product::find($item)->getTranslation('name') }}</a>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th scope="col" style="width:20%" class="">
                                        {{ ('Customer Rating')}}
                                    </th>
                                    @foreach (Session::get('compare') as $key => $item)
                                        <td>
                                            <div>
                                                <i class="las la-star " style="fill:#FF9017; color:#FF9017;"></i>
                                                <span class="">(24)</span>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th scope="row">{{ ('Price')}}</th>
                                    @foreach (Session::get('compare') as $key => $item)
                                        @php
                                            $product = \App\Models\Product::find($item);
                                        @endphp
                                        <td>
                                            @if(home_base_price($product) != home_discounted_base_price($product))
                                                <del class="fw-600 opacity-50 mr-1">{{ home_base_price($product) }}</del>
                                            @endif
                                            <span class=" ">{{ home_discounted_base_price($product) }}</span>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th scope="row">{{ ('Brand')}}</th>
                                    @foreach (Session::get('compare') as $key => $item)
                                        <td>
                                            @if (\App\Models\Product::find($item)->brand != null)
                                                {{ \App\Models\Product::find($item)->brand->getTranslation('name') }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th scope="row">{{ ('Sub Category')}}</th>
                                    @foreach (Session::get('compare') as $key => $item)
                                        <td>
                                            @if (\App\Models\Product::find($item)->subsubcategory != null)
                                                {{ \App\Models\Product::find($item)->subsubcategory->getTranslation('name') }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th class="border-bottom-0" scope="row"></th>
                                    @foreach (Session::get('compare') as $key => $item)
                                        <td class="text-center pt-4 pb-0">
                                            <button type="button" class="btn btn-primary " onclick="showAddToCartModal({{ $item }})">
                                                <i class="fas fa-shopping-cart"></i>
                                                {{ ('Add to cart')}}
                                            </button>
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                <div class="text-center p-4">
                    <p class="fs-17">{{ ('Your comparison list is empty')}}</p>
                </div>
            @endif
        </div>
    </div>
</section>

@endsection
