<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="{{ env('FRONTEND_URL') }}">
    <meta name="author" content="{{ get_setting('meta_author', env('APP_NAME')) }}">
    <link rel="canonical" href="{{ env('FRONTEND_URL') }}" />

    <title>@yield('meta_title', get_setting('website_name').' | '.get_setting('site_motto'))</title>

    <link rel="alternate" href="{{ env('FRONTEND_URL') }}" hreflang="en" />

    <!-- Favicon -->
    <link rel="icon" href="{{ uploaded_asset(get_setting('site_icon')) }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body>
    <div class="flex flex-col items-center justify-center py-10 md:py-20 lg:py-28"
        style="font-family: Inter, &quot;Inter Fallback&quot;, Bangla139, sans-serif;"><span
            class="block mb-4 md:mb-6"><img alt="404 Not Found" loading="lazy" width="537" height="402" decoding="async"
                data-nimg="1" style="color:transparent" src="{{ static_asset('assets/img/404.gif') }}"></span>
        <h3 class="text-[28px] md:text-[40px] leading-[34px] md:leading-[44px] font-normal text-gray-800 mb-2"
            style="font-family: abigeta, &quot;abigeta Fallback&quot;, Bangla139, sans-serif;">Look like you’re lost
        </h3>
        <p class="text-sm md:text-base text-gray-500">The page you are looking for not available</p>
        <a class="text-center bg-gray-900 text-white py-2 px-6 rounded-xl font-medium hover:bg-gray-700 transition-colors mt-6 md:mt-10"
            href="{{ str_contains(url()->current(), 'admin') ? route('admin.dashboard') : route('home') }}">
            {{ str_contains(url()->current(), 'admin') ? 'Go to Admin Dashboard' : 'Explore Products' }}
        </a>
    </div>
</body>

</html>
