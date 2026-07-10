<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsArticle",
        "headline": "{{ addslashes($blog->title) }}",
        "image": "{{ uploaded_asset($blog->banner) }}",
        "datePublished": "{{ $blog->created_at->toIso8601String() }}",
        "dateModified": "{{ $blog->updated_at->toIso8601String() }}",
        "author": {
            "@type": "Person",
            "name": "{{ config('app.name') }}",
            "url": "{{ config('app.url') }}"
        }
    }
</script>
