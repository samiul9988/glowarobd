<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "url": "{{ config('app.url') }}",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ config('app.url') }}/search?keyword={query}",
            "query": "required"
        }
    }
</script>