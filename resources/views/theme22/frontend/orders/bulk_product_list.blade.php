<div>

	@php
	$logo = get_setting('header_logo');
	$totalQty = 0;
	@endphp

	<div style="padding: 1rem;">
		<table class="padding text-left small border-bottom">
			<thead>
				<tr class="gry-color" style="background: #eceff4;">
					<th width="15%" class="text-left">{{ ('Image') }}</th>
					<th width="35%" class="text-left">{{ ('Product Name') }}</th>
					@if($status == 'merchant')
						<th width="15%" class="text-left">{{ ('Breakdown') }}</th>
					@endif
					<th width="10%" class="text-left">{{ ('Total Qty') }}</th>
				</tr>
			</thead>
			<tbody class="strong">
				@foreach ($products as $id => $product)
				@php
					$totalQty += $product['total_quantity'];
				@endphp
				{{-- @dd($product) --}}
				<tr class="">
					<td>
						<img src="{{ uploaded_asset($product['thumbnail']) }}" height="80" alt="Image" />
					</td>
					<td>{{ $product['name'] }}</td>
					@if($status == 'merchant')
						<td>
							@foreach ($product['users'] as $userId => $user)
								<div>
									{{ $user['name'] }} -- {{ $user['quantity'] }}
								</div>
							@endforeach
						</td>
					@endif
					<td>
						{{ $product['total_quantity'] }}
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		<table width="100%" style="margin-top: 10px;">
			<tr>
				<td width="33%"><strong>Total Products: {{ count($products) }}</strong></td>
				<td width="33%" align="center"></td>
				<td width="33%" style="text-align: right;"><strong>Total Qty: {{ $totalQty }}</strong></td>
			</tr>
		</table>
	</div>

</div>

<htmlpagefooter name="myFooter2">
	<table width="100%" style="padding: 0px 70px;">
		<tr>
			<td width="33%">Print Date: {{ date('d-m-Y') }}</td>
			<td width="33%" align="center"></td>
			<td width="33%" style="text-align: center; border-top:solid 1px #000000">Authorized Signature</td>
		</tr>
	</table>
</htmlpagefooter>
<sethtmlpagefooter name="myFooter2" value="on" />