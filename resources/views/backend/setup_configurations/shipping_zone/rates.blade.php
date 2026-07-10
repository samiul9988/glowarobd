<?php
$sz = \App\Models\ShippingZone::findOrFail($id);
$preRates = json_decode($sz->rates);
?>

<form class="form" id="submit_rates" action="{{ route('shipping_zone.update-rates') }}">
    @csrf
    <input type="hidden" name="zone" value="{{ $id }}"/>

    <table class="table">
        @foreach ($shippingMethods as $key=>$shipping_method)

        @php
        $selPrice=0;
        $isChecked = 0;
        @endphp

        @if(is_array($preRates))
        @foreach($preRates as $k=>$v)
            @if($v->id==$shipping_method->id)
                @php
                $selPrice = $v->price;
                $isChecked = 1;
                @endphp
            @endif
        @endforeach
        @endif

        <tr>
            <td style="vertical-align: middle">
                <input type="checkbox" name="rate[id][]" value="{{ $shipping_method->id }}" @if($isChecked) checked @endif />
            </td>
            <td>
                <img src="{{ uploaded_asset($shipping_method->logo) }}" alt="Image" class="size-50px img-fit"/><br>
                {{ $shipping_method->name }}
            </td>
            <td>
                <input type="number" name="rate[price][{{ $shipping_method->id }}]" class="form-control" value="{{ $selPrice }}"/>
            </td>
        </tr>
        @endforeach
    </table>
    <button type="submit" class="btn btn-primary"> {{ ('Save') }} </button>
</form>
