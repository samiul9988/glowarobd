@php
    $name = $note['user_name'] ?? 'Unknown User';

    $initials = collect(explode(' ', $name))
        ->filter() // remove empty parts
        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->implode('');
@endphp
<div class="note" id="{{ $note['id'] }}">
    <div class="note-head">
        <div class="note-author">
            <div class="note-avatar">{{ $initials }}</div>
            <div>
                <div class="note-who">{{ $name }}</div>
                <div class="note-when">{{ \Carbon\Carbon::parse($note['created_at'] ?? now())->diffForHumans() }}</div>
            </div>
        </div>
        @if($note['user_id'] === Auth::id())
            <div class="note-actions">
                <button class="icon-btn delete-note-btn" title="Delete" data-note-id="{{ $note['id'] }}">
                    <i class="las la-trash-alt"></i>
                </button>
            </div>
        @endif
    </div>
    <p class="note-text">
        {{ $note['text'] ?? '' }}
    </p>
</div>
