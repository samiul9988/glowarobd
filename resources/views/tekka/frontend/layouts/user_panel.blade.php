@extends(config('app.theme').'frontend.layouts.app')
@section('content')
<section class="py-2 py-md-3 py-lg-5 dashboard-page">
    <div class="container ">
       <div class="dashboard-wrapper">
            <div class="row mx-auto ">
                @auth
                    <div class="col-xl-4 pr-3">
                        @include(config('app.theme').'frontend.inc.user_side_nav')
                    </div>
                @endauth
                <div class="aiz-user-panel col-xl-8 pl-2 ">
                    @yield('panel_content')

                </div>
            </div>
       </div>
    </div>
</section>
@endsection
