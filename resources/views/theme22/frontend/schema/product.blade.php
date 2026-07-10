{{-- Product Schema --}}
<script type="application/ld+json">
    {
        "@context": "https://schema.org/",
        "@type": "Product",
        "name": "{{ addslashes($schemaProduct->name) }}",
        "image": @php
            $photos = explode(',', $schemaProduct->photos);
            $images = [];
            foreach ($photos as $photo) {
                $images[] = uploaded_asset($photo);
            }
            echo json_encode($images, JSON_UNESCAPED_SLASHES);
        @endphp,
        "description": "{{ strip_tags(json_encode($schemaProduct->description)) }}",
        "brand": {
            "@type": "Brand",
            "name": "{{ addslashes($schemaProduct->brand?->name ?? '') }}"
        },
        "offers": {
            "@type": "Offer",
            "price": {{ min($schemaProduct->unit_price, $webPrice) }},
            "lowPrice": {{ min($schemaProduct->unit_price, $webPrice) }},
            "highPrice": {{ max($schemaProduct->unit_price, $webPrice) }},
            "priceCurrency": "{{ get_system_default_currency()->code ?? 'BDT' }}",
            "availability": "https://schema.org/{{ (($in_stock ?? false) ? 'InStock' : 'OutOfStock') }}"
        },
        "review": @php
            $reviews = [];
            foreach ($schemaProduct->reviews as $review) {
                $reviews[] = [
                    "@type" => "Review",
                    "datePublished" => $review->created_at->format('Y-m-d'),
                    "reviewBody" => $review->comment ?? '',
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
            echo json_encode($reviews, JSON_UNESCAPED_SLASHES);
        @endphp
        @if(count($schemaProduct->reviews)),
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": {{ $schemaProduct->rating ?? 0 }},
            "reviewCount": {{ count($schemaProduct->reviews) }}
        }
        @endif
    }
</script>
{{-- End Product Schema --}}