<div class="aiz-pos-cart-list mb-4 mt-3 c-scrollbar-light">
    @php
        $subtotal = 0;
        $tax = 0;
        $carts = Session::get('pos.cart', collect());
        $regularCarts = $carts->where('type', 'regular');
        $stockIds = $regularCarts->pluck('stock_id')->unique()->toArray();
        $stocks = \App\Models\ProductStock::whereIn('id', $stockIds)->get()->keyBy('id');
    @endphp
    @if (Session::has('pos.cart'))
        <ul class="list-group list-group-flush">
            @forelse ($regularCarts as $key => $cartItem)
                @php
                    $subtotal += $cartItem['price']*$cartItem['quantity'];
                    $tax += $cartItem['tax']*$cartItem['quantity'];
                    $stock = $stocks->get($cartItem['stock_id']);
                @endphp
                <li class="list-group-item py-0 pl-2">
                    <div class="row gutters-5 align-items-center">
                        <div class="col-auto w-60px">
                            <div class="row no-gutters align-items-center flex-column aiz-plus-minus">
                                <button class="btn col-auto btn-icon btn-sm fs-15" type="button" data-id="{{ $cartItem['id'] }}" data-type="plus" data-field="qty-{{ $key }}">
                                    <i class="las la-plus"></i>
                                </button>
                                <input type="text" name="qty-{{ $key }}" id="qty-{{ $key }}" class="col border-0 text-center flex-grow-1 fs-16 input-number inputCart-{{$cartItem['id']}}" placeholder="1" value="{{ $cartItem['quantity'] }}" min="{{ $stock->product->min_qty }}" max="{{ $stock->qty }}" onchange="updateQuantity({{ $key }})">
                                <button class="btn col-auto btn-icon btn-sm fs-15" type="button" data-id="{{ $cartItem['id'] }}" data-type="minus" data-field="qty-{{ $key }}">
                                    <i class="las la-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-truncate-2">{{ $stock->product?->name ?? 'Unknown Product' }}</div>
                            <span class="span badge badge-inline fs-12 badge-soft-secondary">{{ $cartItem['variant'] }}</span>
                        </div>
                        <div class="col-auto">
                            <div class="fs-12 opacity-60">{{ single_price($cartItem['price']) }} x {{ $cartItem['quantity'] }}</div>
                            <div class="fs-15 fw-600">{{ single_price($cartItem['price']*$cartItem['quantity']) }}</div>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-circle btn-icon btn-sm btn-soft-danger ml-2 mr-0" onclick="removeFromCart({{ $key }})">
                                <i class="las la-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </li>
            @empty
                <li class="list-group-item" data-message="Cart {{ json_encode($carts) }}">
                    <div class="text-center">
                        <i class="las la-frown la-3x opacity-50"></i>
                        <p>{{ ('No Product Added') }}</p>
                    </div>
                </li>
            @endforelse
        </ul>
    @else
        <div class="text-center" data-message="Cart session is empty">
            <i class="las la-frown la-3x opacity-50"></i>
            <p>{{ ('No Product Added') }}</p>
        </div>
    @endif
</div>
<div>
    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
        <span>{{ ('Sub Total')}}</span>
        <span>{{ single_price($subtotal) }}</span>
    </div>
    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
        <span>{{ ('Tax')}}</span>
        <span>{{ single_price($tax) }}</span>
    </div>
    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
        <span>{{ ('Shipping')}}</span>
        <span>{{ single_price(Session::get('pos.shipping', 0)) }}</span>
    </div>
    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
        <span>{{ ('Discount')}}</span>
        <span>{{ single_price(Session::get('pos.discount', 0)) }}</span>
    </div>
    {{-- @if(Session::get('pos.total_paid', 0))
    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
        <span>{{ ('Paid Amount')}}</span>
        <span>
            <a type="button" title="{{ ('Remove Paid Amount') }}" class="text-danger" onclick="removePaidAmount()">
                <i class="las la-times-circle"></i>
            </a>
            {{ single_price(Session::get('pos.total_paid', 0)) }} (-)
        </span>
    </div>
    @endif --}}
    @php
        $total = $subtotal + $tax + Session::get('pos.shipping', 0) - Session::get('pos.discount', 0) - Session::get('pos.total_paid', 0);
    @endphp
    <div class="d-flex justify-content-between border-top pt-2">
        <span class="fw-600 fs-18">{{ ('Total')}}</span>
        {{-- @if (Session::has('pos.cart') && Session::get('pos.cart', collect())->count() > 0)
            <button type="button" class="btn btn-sm btn-outline-dark btn-styled copy_summary">
                Copy Summary
            </button>
        @endif --}}
        <span class="fw-600 fs-18 total-amount" data-total="{{ max($total, 0) }}">{{ single_price(max($total, 0)) }}</span>
    </div>
</div>
