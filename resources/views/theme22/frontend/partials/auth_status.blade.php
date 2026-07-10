@auth
    <div class="" id="login">
        <i class="la la-user la-2x opacity-80"></i>
        <span class="inline_login">
            <div>Welcome!</div>
            @if (isAdmin())
                <div>
                    <a href="{{ route('admin.dashboard') }}"
                        class="text-reset d-inline-block opacity-70 py-2">{{ ('My Panel') }}</a> / <a
                        href="{{ route('logout') }}"
                        class="text-reset d-inline-block opacity-70 py-2">{{ ('Logout') }}</a>
                </div>
            @else
                <div>
                    <a href="{{ route('dashboard') }}"
                        class="text-reset d-inline-block opacity-70 py-2">{{ ('My Panel') }}</a> / <a
                        href="{{ route('logout') }}"
                        class="text-reset d-inline-block opacity-70 py-2">{{ ('Logout') }}</a>
                </div>
            @endif
        </span>
    </div>
@else
    <div class="" id="login">
        <i class="la la-user la-2x opacity-80"></i>
        <span class="inline_login">
            <div>Welcome! Guest</div>
            <div>
                <a href="{{ route('user.login') }}"
                    class="text-reset d-inline-block opacity-70 py-2">{{ ('Login') }}</a> / <a
                    href="{{ route('user.registration') }}"
                    class="text-reset d-inline-block opacity-70 py-2">{{ ('Registration') }}</a>
            </div>
        </span>
    </div>
@endauth
