@component('mail::message')
# ✅ Product Push Job Completed

**Merchant ID:** {{ $merchantId }}

@component('mail::panel')
Product push request has been sent successfully 🎉.
@endcomponent

See All Products: [{{ $link }}]({{ $link }})

Thanks,
{{ config('app.name') }}
@endcomponent
