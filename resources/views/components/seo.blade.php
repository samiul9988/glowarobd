<title>{{ trim(data_get($meta, 'title')) ?: get_setting('website_name') . ' | ' . get_setting('site_motto') }}</title>
<meta name="author" content="{{ trim(data_get($meta, 'author')) ?: get_setting('meta_author', env('APP_NAME')) }}">
<link rel="canonical" href="{{ trim(data_get($meta, 'canonical')) ?: config('app.url') }}" />

{{-- Meta Data --}}
<meta name="title" content="{{ trim(data_get($meta, 'title')) ?: get_setting('website_name') . ' | ' . get_setting('site_motto') }}">
<meta name="description" content="{{ trim(data_get($meta, 'description')) ?: get_setting('meta_description') }}">
<meta name="keywords" content="{{ trim(data_get($meta, 'keywords')) ?: get_setting('meta_keywords') }}">

{{-- Schema.org --}}
<meta itemprop="name" content="{{ trim(data_get($meta, 'title')) ?: get_setting('meta_title') }}">
<meta itemprop="description" content="{{ trim(data_get($meta, 'description')) ?: get_setting('meta_description') }}">
<meta itemprop="image" content="{{ uploaded_asset(trim(data_get($meta, 'image')) ?: get_setting('meta_image')) }}">

{{-- Twitter --}}
<meta name="twitter:card" content="{{ trim(data_get($meta, 'twitter.card')) ?: 'website' }}">
<meta name="twitter:site" content="@publisher_handle">
<meta name="twitter:creator" content="@author_handle">
<meta name="twitter:title" content="{{ trim(data_get($meta, 'twitter.title')) ?: trim(data_get($meta, 'title')) ?: get_setting('twitter_title', get_setting('meta_title')) }}">
<meta name="twitter:description" content="{{ trim(data_get($meta, 'twitter.description')) ?: trim(data_get($meta, 'description')) ?: get_setting('twitter_description', get_setting('meta_description')) }}">
<meta name="twitter:image" content="{{ uploaded_asset(trim(data_get($meta, 'twitter.image')) ?: trim(data_get($meta, 'image')) ?: get_setting('twitter_image', get_setting('meta_image'))) }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ trim(data_get($meta, 'og.type')) ?: 'website' }}">
<meta property="og:url" content="{{ trim(data_get($meta, 'og.url')) ?: request()->fullUrl() ?: config('app.url') }}">
<meta property="og:site_name" content="{{ trim(data_get($meta, 'og.site_name')) ?: get_setting('meta_title') ?: config('app.name') }}">
<meta property="og:title" content="{{ trim(data_get($meta, 'og.title')) ?: trim(data_get($meta, 'title')) ?: get_setting('og_title', get_setting('meta_title')) }}">
<meta property="og:description" content="{{ trim(data_get($meta, 'og.description')) ?: trim(data_get($meta, 'description')) ?: get_setting('og_description', get_setting('meta_description')) }}">
<meta property="og:image" content="{{ uploaded_asset(trim(data_get($meta, 'og.image')) ?: trim(data_get($meta, 'image')) ?: get_setting('og_image', get_setting('meta_image'))) }}">

{{-- Facebook --}}
<meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}">

{{-- Extra / Page-specific meta --}}
{{ $slot }}
