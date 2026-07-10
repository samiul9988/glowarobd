<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{  translate('INVOICE') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
	<style media="all">
        @page {
			margin: 0;
			padding:0;
		}
		body{
			font-size: 0.875rem;
            font-family: '<?php echo  $font_family ?>';
            font-weight: normal;
            direction: <?php echo  $direction ?>;
            text-align: <?php echo  $text_align ?>;
			padding:0;
			margin:0;
		}
		.gry-color *,
		.gry-color{
			color:#000;
		}
		table{
			width: 100%;
		}
		table th{
			font-weight: normal;
		}
		table.padding th{
			padding: .25rem .7rem;
		}
		table.padding td{
			padding: .25rem .7rem;
		}
		table.sm-padding td{
			padding: .1rem .7rem;
		}
		.border-bottom td,
		.border-bottom th{
			border-bottom:1px solid #eceff4;
		}
		.text-left{
			text-align:<?php echo  $text_align ?>;
		}
		.text-right{
			text-align:<?php echo  $not_text_align ?>;
		}
		.strong{
			font-weight: bold;
		}
		.no-padding{
			padding:0px!important;
		}
	</style>
</head>
<body>
	<div>

		@php
			$logo = get_setting('system_logo_black');
		@endphp

		<div style="padding: 1rem;">
			<table>
				<tr>
					<td style="line-height: 1.8">
					    <br />
					    <div>
					        <br>
					        @php
    						    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    						    echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($order->code, $generator::TYPE_CODE_128, 1)) . '" width="auto" height="50">';
    						@endphp
					    </div>
					    <br>
					    <div>
					        {!! order_payment_status($order) !!}
					    </div>
					    <div>
					        {{  translate('Order No') }}</span> <span class="strong">{{ $order->code }}</span>
					    </div>
					    <div>
					        <span class="gry-color small">{{  translate('Order Date') }}:</span> <span class=" strong">{{ date('d-m-Y', $order->date) }}</span>
					    </div>
					</td>
					<td class="text-right" style="line-height: 1.8">
					    <div>
    						@if($logo != null)
    							<img src="{{ uploaded_asset($logo) }}" height="30" style="display:inline-block;">
    						@else
    							<img src="{{ static_asset('assets/img/logo.png') }}" height="30" style="display:inline-block;">
    						@endif
						</div>
						<div>
						    {{ get_setting('site_name') }}
						</div>
						<div>
						    {{  translate('Phone') }}: {{ get_setting('contact_phone') }}
						</div>
						<div>
						    {{  translate('Email') }}: {{ get_setting('contact_email') }}
						</div>
						<div>
						    {{ get_setting('contact_address',null,App::getLocale()) }}
						</div>
					</td>
				</tr>
			</table>
		</div>

		<div style="padding: 1rem;padding-top: 0; padding-bottom: 0">
            <table>
				@php
					$shipping_address = json_decode($order->shipping_address);
				@endphp
				<tr><td class="strong small gry-color">{{ translate('Bill to') }}:</td></tr>
				<tr><td class="strong">{{ $shipping_address->name }}</td></tr>
				<tr><td class="gry-color small">{{ translate('Phone') }}: {{ $shipping_address->phone }}</td></tr>
				@if($shipping_address->email!='')
				<tr><td class="gry-color small">{{ translate('Email') }}: {{ $shipping_address->email }}</td></tr>
				@endif
				<tr><td class="gry-color small">{{ $shipping_address->address }},</td></tr>
				<tr><td class="gry-color small">
					City: {{ $shipping_address->city }},
					Area: {{ @$shipping_address->area }},
					@if(@$shipping_address->postal_code!='')
					Postal Code: {{ @$shipping_address->postal_code }},
					@endif
					Country: {{ $shipping_address->country }}</td></tr>

			</table>
		</div>

	    <div style="padding: 1rem;">
			<table class="padding text-left small border-bottom">
				<thead>
	                <tr class="gry-color" style="background: #eceff4;">
						<th width="15%" class="text-left">{{ translate('Image') }}</th>
	                    <th width="35%" class="text-left">{{ translate('Product Name') }}</th>
						{{-- <th width="15%" class="text-left">{{ translate('Delivery Type') }}</th> --}}
	                    <th width="10%" class="text-left">{{ translate('Qty') }}</th>
	                    <th width="15%" class="text-left">{{ translate('Unit Price') }}</th>
	                    <th width="10%" class="text-left">{{ translate('Tax') }}</th>
	                    <th width="15%" class="text-right">{{ translate('Total') }}</th>
	                </tr>
				</thead>
				<tbody class="strong">
	                @foreach ($order->orderDetails as $key => $orderDetail)
		                @if ($orderDetail->product != null)
							<tr class="">
								<td>
									<img src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}" height="80" alt="Image"/>
								</td>
								<td>{{ $orderDetail->product->name }} @if($orderDetail->variation != null) ({{ $orderDetail->variation }}) @endif</td>
								{{-- <td>
									@if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
										{{ translate('Home Delivery') }}
									@elseif ($orderDetail->shipping_type == 'pickup_point')
										@if ($orderDetail->pickup_point != null)
											{{ $orderDetail->pickup_point->getTranslation('name') }} ({{ translate('Pickip Point') }})
										@endif
									@endif
								</td> --}}
								<td class="">{{ $orderDetail->quantity }}</td>
								<td class="currency">{{ single_price($orderDetail->price/$orderDetail->quantity) }}</td>
								<td class="currency">{{ single_price($orderDetail->tax/$orderDetail->quantity) }}</td>
			                    <td class="text-right currency">{{ single_price($orderDetail->price+$orderDetail->tax) }}</td>
							</tr>
		                @endif
					@endforeach
	            </tbody>
			</table>
		</div>

	    <div style="padding:1rem;">
	        <table class="text-right sm-padding small strong">
	        	<thead>
	        		<tr>
	        			<th width="60%"></th>
	        			<th width="40%"></th>
	        		</tr>
	        	</thead>
		        <tbody>
			        <tr>
			            <td style="text-align: left; vertical-align: top">
							<table class="text-left sm-padding small strong" style="width: 80%">
						        <tbody>
									<tr>
							            <th class="gry-color text-left">{{ translate('Delivery Type') }}</th>
							            <td class="currency no-padding">
											@if ($order->orderDetails[0]->shipping_type != null && $order->orderDetails[0]->shipping_type == 'home_delivery')
												{{ translate('Home Delivery') }}
											@elseif ($order->orderDetails[0]->shipping_type == 'pickup_point')
												@if ($order->orderDetails[0]->pickup_point != null)
													{{ $order->orderDetails[0]->pickup_point->getTranslation('name') }} ({{ translate('Pickip Point') }})
												@endif
											@endif
										</td>
							        </tr>
                                    @if($order->delivery_date!='')
                                    <tr>
							            <th class="gry-color text-left">{{ translate('Delivery Date') }}</th>
							            <td class="currency no-padding">
                                            {{ date('d-m-Y', $order->delivery_date) }} ({{ date('l', $order->delivery_date) }})
										</td>
							        </tr>
                                    @endif
									@if($order->orderDetails[0]->shipping_type == 'home_delivery')
							        <tr>
							            <th class="gry-color text-left">{{ translate('Shipping Method') }}</th>
							            <td class="currency no-padding">{{ @$order->orderDetails[0]->shippingMethod->name }}</td>
							        </tr>
									@endif
							        <tr>
							            <th class="gry-color text-left">{{ translate('Payment Type') }}</th>
							            <td class="currency no-padding">
											{{ str_replace('_',' ',strtoupper($order->payment_type))  }}
										</td>
							        </tr>
						        </tbody>
						    </table>
			            </td>
			        </tr>
					<br>
					<tr>
						<td style="text-align: left; vertical-align: top">
					        <table class="text-right sm-padding small strong" style="width: 50%;">
						        <tbody>
							        <tr>
							            <th class="gry-color text-left">{{ translate('Sub Total') }}</th>
							            <td class="currency no-padding" style="padding: 0px!important">{{ single_price($order->orderDetails->sum('price')) }}</td>
							        </tr>
									@if($order->coupon_discount>0 || $order->reward_point_discount>0)
										@if($order->coupon_discount>0)
											<tr class="border-bottom">
												<th class="gry-color text-left">{{ translate('Coupon Discount') }}</th>
												<td class="currency no-padding" style="padding: 0px!important">{{ single_price($order->coupon_discount) }}</td>
											</tr>
										@endif
										@if($order->reward_point_discount>0)
											<tr class="border-bottom">
												<th class="gry-color text-left">{{ translate('Reward point Discount') }}</th>
												<td class="currency no-padding" style="padding: 0px!important">{{ single_price($order->reward_point_discount) }}</td>
											</tr>
										@endif
										<tr>
											<th class="text-left strong"><strong>{{ translate('Grand Total') }}</strong></th>
											<td class="currency no-padding" style="padding: 0px!important">{{ single_price($order->orderDetails->sum('price') - ($order->coupon_discount + $order->reward_point_discount)) }}</td>
										</tr>
									@endif
							        <tr>
							            <th class="gry-color text-left">{{ translate('Shipping Cost') }}</th>
							            <td class="currency no-padding" style="padding: 0px!important">{{ single_price($order->orderDetails->sum('shipping_cost')) }}</td>
							        </tr>
							        <tr class="border-bottom">
							            <th class="gry-color text-left">{{ translate('Total Tax') }}</th>
							            <td class="currency no-padding" style="padding: 0px!important">{{ single_price($order->orderDetails->sum('tax')) }}</td>
							        </tr>
							        <tr>
							            <th class="text-left strong"><strong>{{ translate('Net Total') }}</strong></th>
							            <td class="currency no-padding" style="padding: 0px!important">{{ single_price($order->grand_total) }}</td>
							        </tr>
						        </tbody>
						    </table>
			            </td>
					</tr>
		        </tbody>
		    </table>
	    </div>

	</div>

	<htmlpagefooter name="myFooter2">
		<table width="100%" style="padding: 0px 70px;">
			<tr>
				<td width="33%">Print Date: {DATE j-m-Y}</td>
				<td width="33%" align="center"></td>
				<td width="33%" style="text-align: center; border-top:solid 1px #000000">Authorized Signature</td>
			</tr>
		</table>
	</htmlpagefooter>
	<sethtmlpagefooter name="myFooter2" value="on" />
</body>
</html>
