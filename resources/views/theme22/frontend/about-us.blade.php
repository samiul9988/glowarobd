@extends(config('app.theme') . 'frontend.layouts.app')
@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">About Us</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ ('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <span class="text-reset">"{{ ('About Us') }}"</sp>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
    <div class="container">
        @php
            $content = App\Models\Page::where('slug', 'about-us')->first()?->content;
            // dd($content);
        @endphp

        <div>
            {!! $content !!}
        </div>
    </div>
</section>
    {{-- <main class = "about-us-page">
        
    </main> --}}
@endsection