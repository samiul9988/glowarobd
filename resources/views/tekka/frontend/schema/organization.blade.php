@php
    $social_links = [];
    if (get_setting('facebook_link') != null) {
        $social_links[] = get_setting('facebook_link');
    }
    if ( get_setting('twitter_link') !=  null ) {
        $social_links[] = get_setting('twitter_link');
    }
    if ( get_setting('instagram_link') !=  null ) {
        $social_links[] = get_setting('instagram_link');
    }
    if ( get_setting('youtube_link') !=  null ) {
        $social_links[] = get_setting('youtube_link');
    }
    if ( get_setting('linkedin_link') !=  null ) {
        $social_links[] = get_setting('linkedin_link');
    }
@endphp
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ config('app.name') }}",
        "url": "{{ config('app.url') }}",
        "telephone": "{{ get_setting('contact_phone') }}",
        "email": "{{ get_setting('contact_email') }}",
        "logo": "{{ uploaded_asset(get_setting('system_logo_white')) }}",
        "image": "{{ uploaded_asset(get_setting('system_logo_white')) }}",
        "description": "{{ strip_tags(json_encode(get_setting('about_us_description',null,App::getLocale()))) }}",
        "sameAs": @json($social_links),
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "{{ get_setting('contact_address',null,App::getLocale()) }}",
            "addressCountry": "{{ get_setting('country_code', 'BD') }}"
        }
    }
</script>
