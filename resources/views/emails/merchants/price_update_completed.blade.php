@component('mail::message')
# ✅ Price Update Job Completed

**Merchant ID:** {{ $merchantId }}

@component('mail::panel')
Product prices update request has been sent successfully 🎉. {{ $price_type === 'flat' ? 'Flat ' . single_price($amount) : ($amount) . '% ' }} added with each products purchase price.
@endcomponent

See All Products: [{{ $link }}]({{ $link }})

Thanks,
{{ config('app.name') }}
@endcomponent
