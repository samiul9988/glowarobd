<i class="las {{ @$icon ?: 'la-info-circle' }} {{ @$class }}" data-toggle="tooltip"
    data-html="{{ @$html == true ? 'true' : 'false' }}" data-title="{!! $title !!}"
    data-placement="{{ @$position ?: 'right' }}"
    style="{{ @$animate ?? false === true ? 'animation: 1.5s ease 0s infinite normal none running heartbeat;' : '' }}{{ @$style }}">
</i>
