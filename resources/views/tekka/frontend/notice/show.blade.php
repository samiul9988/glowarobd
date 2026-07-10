@extends(config('app.theme') . 'frontend.layouts.app')

@section('meta')
<x-seo />
@endsection

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">Notice</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ ('Home')}}</a>
                    </li>
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('customer.notice') }}">{{ ('Notice')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <span class="text-reset">"{{ Str::limit($notice->title,50) }}"</sp>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
    <div class="container" style="padding-inline: 15rem;">
        <div>
            <h1 class="fw-600 h4 mb-3">{{ $notice->title }}</h1>
            <p class="text-muted mb-3">
                <i class="las la-calendar-alt"></i> {{ $notice->created_at->format('d M Y') }}
            </p>
        </div>
        <div>
            {!! $notice->content !!}
        </div>
    </div>
</section>
@endsection
