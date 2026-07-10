@php
    $name = $log['user_name'] ?? 'Unknown User';

    $initials = collect(explode(' ', $name))
        ->filter() // remove empty parts
        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->implode('');
@endphp
<div class="note">
    <div class="note-head">
        <div class="note-author">
            <div class="note-avatar">{{ $initials }}</div>
            <div>
                <div class="note-who">{{ $name }}</div>
                <div class="note-when">{{ \Carbon\Carbon::parse($log['created_at'] ?? now())->diffForHumans() }}</div>
            </div>
        </div>
    </div>
    <p class="note-text">
        {{ $log['message'] ?? '' }}
    </p>
</div>
