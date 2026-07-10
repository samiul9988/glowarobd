@php
    $wishlistCount = Auth::check() ? count(Auth::user()->wishlists) : \App\Models\Wishlist::whereNotNull('temp_user_id')->where('temp_user_id', session()->get('temp_user_id'))->count();
@endphp

<a href="{{ route('wishlists.index') }}" class="d-flex align-items-center text-reset flex-column  position-relative">
    <i class="la la-heart-o la-2x"></i>
    <span class="flex-grow-1 ml-1">
        <span class="badge badge-secondary badge-inline badge-pill">{{ $wishlistCount ?? 0 }}</span>
    </span>
</a>
