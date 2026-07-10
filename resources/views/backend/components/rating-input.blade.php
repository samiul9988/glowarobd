@php
    $name = @$name ?? 'rating';
    $size = @$size ?? 15;
    $size = $size < 15 ? 15 : max($size, 24);
@endphp

<div class="rating rating-input">
    <label>
        <input type="radio" class="{{ @$class }}" name="{{ @$name ?? 'rating' }}" required value="1">
        <i class="las la-star fs-{{ @$size }}"></i>
    </label>
    <label>
        <input type="radio" class="{{ @$class }}" name="{{ @$name ?? 'rating' }}" value="2">
        <i class="las la-star fs-{{ @$size }}"></i>
    </label>
    <label>
        <input type="radio" class="{{ @$class }}" name="{{ @$name ?? 'rating' }}" value="3">
        <i class="las la-star fs-{{ @$size }}"></i>
    </label>
    <label>
        <input type="radio" class="{{ @$class }}" name="{{ @$name ?? 'rating' }}" value="4">
        <i class="las la-star fs-{{ @$size }}"></i>
    </label>
    <label>
        <input type="radio" class="{{ @$class }}" name="{{ @$name ?? 'rating' }}" value="5">
        <i class="las la-star fs-{{ @$size }}"></i>
    </label>
</div>
