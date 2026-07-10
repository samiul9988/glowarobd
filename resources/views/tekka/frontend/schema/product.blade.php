{{-- Product Schema --}}
@php
    $photos = explode(',', $schemaProduct->photos);
    $images = [];
    foreach ($photos as $photo) {
        $images[] = uploaded_asset($photo);
    }

    $reviews = [];
    foreach ($schemaProduct->reviews as $review) {
        $reviews[] = [
            "@type" => "Review",
            "datePublished" => $review->created_at->format('Y-m-d'),
            "reviewBody" => addslashes(strip_tags($review->comment ?? '')),
            "reviewRating" => [
                "@type" => "Rating",
                "ratingValue" => $review->rating ?? 0,
                "bestRating" => 5
            ],
            "author" => [
                "@type" => "Person",
                "name" => addslashes($review->name ?? '')
            ]
        ];
    }

    $avgRating = $schemaProduct->reviews?->avg('rating') ?? 0;
    $reviewCount = $schemaProduct->reviews?->count() ?? 0;

    $now = now()->timestamp;
    if ($schemaProduct->pre_order && $schemaProduct->pre_order_start_date >= $now && $schemaProduct->pre_order_end_date <= $now) {
        $inStock = true;
    } else {
        $inStock = $schemaProduct->stocks?->first()?->qty > 0;
    }
@endphp

<script type="application/ld+json">
    {
        "@context": "https://schema.org/",
        "@type": "Product",
        "name": "{{ addslashes($schemaProduct->name) }}",
        "url": "{{ route('product', $schemaProduct->slug) }}",
        "image": {!! json_encode($images, JSON_UNESCAPED_SLASHES) !!},
        "description": "{{ addslashes(strip_tags($schemaProduct->description)) }}",
        "brand": {
            "@type": "Brand",
            "name": "{{ addslashes($schemaProduct->brand?->name ?? '') }}"
        },
        "offers": {
            "@type": "Offer",
            "price": {{ min($schemaProduct->unit_price, $webPrice) }},
            "priceCurrency": "{{ get_system_default_currency()->code ?? 'BDT' }}",
            "availability": "https://schema.org/{{ ($inStock ? 'InStock' : 'OutOfStock') }}"
        },
        "review": {!! json_encode($reviews, JSON_UNESCAPED_SLASHES) !!},
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": {{ $avgRating }},
            "reviewCount": {{ $reviewCount }}
        }
    }
</script>
{{-- End Product Schema --}}
