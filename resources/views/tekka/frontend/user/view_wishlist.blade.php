@extends(config('app.theme').'frontend.layouts.user_panel')

@section('meta')
<x-seo />
@endsection

@section('panel_content')
<div class="bg-white wishlist_main" >
    <div class="aiz-titlebar mt-2 mb-3 mb-md-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <b class="h4 fs-24 fw-500">{{ ('Your Saved Items')}}</b>
            </div>
        </div>
    </div>

    <div class="row gutters-5 wishlist_page" id="wishlist-items-container">
        @forelse ($wishlists as $key => $wishlist)
            @if ($wishlist->product != null)
                <div class="col-xxl-3 col-xl-4 col-lg-3 col-md-4 col-sm-6 col-6 wishlist-item" id="wishlist_{{ $wishlist->id }}">
                    <div class="card mb-2 border-0">
                        <div class="card-body ">
                            <a href="{{ route('product', $wishlist->product->slug) }}" class="d-block mb-3">
                                <img src="{{ uploaded_asset($wishlist->product->thumbnail_img) }}" data-href={{ uploaded_asset($wishlist->product->thumbnail_img) }} class="img-fit" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                            </a>

                           <div class="product-info px-2">
                                <h5 class="fs-14 mb-0 lh-1-5 fw-400 text-truncate-2">
                                        <a href="{{ route('product', $wishlist->product->slug) }}" class="text-reset">{{ $wishlist->product->name }}</a>
                                    </h5>


                                    <div class="rating rating-sm mb-1 ">
                                        {!! renderStarRating($wishlist->product->rating) !!} <span>(2)</span>
                                    </div>
                                    <div class=" fs-14">
                                        <span class="fw-500 fs-16">{{ home_discounted_base_price($wishlist->product) }}</span>
                                    </div>
                           </div>
                        </div>
                        <div class="card-footer p-2">

                            <form id="option-choice-form">
                            @csrf
                            <input type="hidden" name="id" value="{{ $wishlist->product->id }}">
                            <input type="hidden" name="quantity" class="col bg-light border-0 text-center flex-grow-1 fs-16 input-number inputCart-{{$wishlist->product->id}}" placeholder="1" value="1" min="1" max="10">
                            <button type="button" class="btn btn-sm btn-block w-100" onclick="addToCart(this)">
                            <i class="fas fa-shopping-cart fs-14 fw-400 mr-1"></i></i>{{ ('Add to Bag')}}
                            </button>
                            </form>
                        </div>
                        <a href="javascript:void(0)" class="link link--style-3 remove-wish" data-toggle="tooltip" data-placement="top" title="Remove from wishlist" onclick="removeFromWishlist({{ $wishlist->id }})">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            @endif
        @empty
            <div class="col">
                <div class="text-center bg-white p-4 rounded shadow">
                    <img class="mw-100 h-200px" src="{{ static_asset('assets/img/nothing.svg') }}" alt="Image">
                    <h5 class="mb-0 h5 mt-3">{{ ("There isn't anything added yet")}}</h5>
                </div>
            </div>
        @endforelse
    </div>
    <div class="aiz-pagination">
        {{ $wishlists->links() }}
    </div>
</div>
@endsection

@section('modal')

<div class="modal fade" id="addToCart" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
        <div class="modal-content position-relative">
            <div class="c-preloader">
                <i class="fa fa-spin fa-spinner"></i>
            </div>
            <button type="button" class="close absolute-close-btn" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div id="addToCart-modal-body">

            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function removeFromWishlist(id){
            $.post('{{ route('wishlists.remove') }}',{_token:'{{ csrf_token() }}', id:id}, function(data){
                $('#wishlist').html(data);
                $('#wishlist_'+id).remove();
                if ($('.wishlist-item').length == 0) {
                    $('#wishlist-items-container').html('<div class="col"><div class="text-center bg-white p-4 rounded shadow"><img class="mw-100 h-200px" src="{{ static_asset('assets/img/nothing.svg') }}" alt="Image"><h5 class="mb-0 h5 mt-3">{{ ("There isn\'t anything added yet")}}</h5></div></div>');
                }
                AIZ.plugins.notify('success', '{{ ('Item has been renoved from wishlist') }}');
            })
        }
    </script>
@endsection
