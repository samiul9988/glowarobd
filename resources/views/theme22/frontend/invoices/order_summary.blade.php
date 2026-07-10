<div class="row">
	<div class="col-xl-6">
		@php
	        $subtotal = 0;
	        $tax = 0;
			$shippingCost = $orderItems[0]->shipping_cost;
	    @endphp
	    @if ($orderItems)
	        <ul class="list-group list-group-flush">
	        @forelse ($orderItems as $key => $cartItem)
	            @php
	                $subtotal += $cartItem['price']*$cartItem['quantity'];
	                $tax += $cartItem['tax']*$cartItem['quantity'];
	                $stock = \App\Models\ProductStock::find($cartItem['stock_id']);
	            @endphp
	            <li class="list-group-item px-0">
	                <div class="row gutters-10 align-items-center">
	                    <div class="col">
	                    	<div class="d-flex">
	                    		@if($stock->image == null)
	                    			<img src="{{ uploaded_asset($stock->product->thumbnail_img) }}" class="img-fit size-60px">
	                    		@else
	                    			<img src="{{ uploaded_asset($stock->image) }}" class="img-fit size-60px">
	                    		@endif
	                    		<span class="flex-grow-1 ml-3 mr-0">
			                        <div class="text-truncate-2">{{ $stock->product->name }}</div>
			                        <span class="span badge badge-inline fs-12 badge-soft-secondary">{{ $cartItem['variant'] }}</span>
	                    		</span>
	                    	</div>
	                    </div>
	                    <div class="col-xl-3">
	                        <div class="fs-14 fw-600 text-right">{{ single_price($cartItem['price']) }}</div>
	                        <div class="fs-14 text-right">{{ ('QTY') }}: {{ $cartItem['quantity'] }}</div>
	                    </div>
	                </div>
	            </li>
	        @empty
	            <li class="list-group-item">
	                <div class="text-center">
	                    <i class="las la-frown la-3x opacity-50"></i>
	                    <p>{{ ('No Product Added') }}</p>
	                </div>
	            </li>
	        @endforelse
	        </ul>
	    @else
	        <div class="text-center">
	            <i class="las la-frown la-3x opacity-50"></i>
	            <p>{{ ('No Product Added') }}</p>
	        </div>
	    @endif
	</div>
	<div class="col-xl-6">
		<div class="pl-xl-4">
			<div class="card mb-4">
				<div class="card-header"><span class="fs-16">{{ ('Customer Info') }}</span></div>
				<div class="card-body">
					@if($order->shipping_address)
						<div class="d-flex justify-content-between  mb-2">
							<span class="">{{ ('Name')}}:</span>
							<span class="fw-600">{{ $shippingInfo->name }}</span>
						</div>
						<div class="d-flex justify-content-between  mb-2">
							<span class="">{{ ('Email')}}:</span>
							<span class="fw-600">{{ $shippingInfo->email }}</span>
						</div>
						<div class="d-flex justify-content-between  mb-2">
							<span class="">{{ ('Phone')}}:</span>
							<span class="fw-600">{{ $shippingInfo->phone }}</span>
						</div>
						<div class="d-flex justify-content-between  mb-2">
							<span class="">{{ ('Address')}}:</span>
							<span class="fw-600">{{ $shippingInfo->address }}</span>
						</div>
						<div class="d-flex justify-content-between  mb-2">
							<span class="">{{ ('Country')}}:</span>
							<span class="fw-600">{{ $shippingInfo->country }}</span>
						</div>
						<div class="d-flex justify-content-between  mb-2">
							<span class="">{{ ('City')}}:</span>
							<span class="fw-600">{{ $shippingInfo->city }}</span>
						</div>
						<div class="d-flex justify-content-between  mb-2">
							<span class="">{{ ('Area')}}:</span>
							<span class="fw-600">{{ @$shippingInfo->area }}</span>
						</div>
						<div class="d-flex justify-content-between  mb-2">
							<span class="">{{ ('Postal Code')}}:</span>
							<span class="fw-600">{{ @$shippingInfo->postal_code }}</span>
						</div>
					@else
						<div class="text-center p-4">
							{{ ('No customer information selected.') }}
						</div>
					@endif
				</div>
			</div>

			<div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
		        <span>{{ ('Total')}}</span>
		        <span>{{ single_price($subtotal) }}</span>
		    </div>
		    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
		        <span>{{ ('Tax')}}</span>
		        <span>{{ single_price($tax) }}</span>
		    </div>
		    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
		        <span>{{ ('Shipping')}}</span>
		        <span>{{ single_price($shippingCost) }}</span>
		    </div>
		    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
		        <span>{{ ('Discount')}}</span>
		        <span>{{ single_price($order->coupon_discount) }}</span>
		    </div>
		    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
		        <span>{{ ('Reward Point Discount')}}</span>
		        <span>{{ single_price($order->reward_point_discount) }}</span>
		    </div>
		    <div class="d-flex justify-content-between fw-600 fs-18 border-top pt-2">
		        <span>{{ ('Total')}}</span>
		        <span>{{ single_price($subtotal+$tax+$shippingCost - ($order->coupon_discount+$order->reward_point_discount)) }}</span>
		    </div>
		</div>
	</div>
</div>
