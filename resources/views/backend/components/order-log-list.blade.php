<ul class="list-group rounded-0">
    @forelse ($logs as $log)
        <li class="list-group-item mb-2 py-1 {{ $loop->first ? 'border-success' : ''}} border-width-3 border-right-0 border-top-0 border-bottom-0">
            <div>{{ ucfirst(strtolower(str_replace('_', ' ', $log['message']))) }}</div>
            <small class="text-muted">
                @if($log['hasManager'] ?? false)
                    By {{ $log['manager'] }}
                @endif
                At {{ $log['created_at'] }}
            </small>
        </li>
    @empty
        <li class="list-group-item mb-2 border-0">
            <div class="text-center text-muted">
                <i class="las la-exclamation-triangle"></i>
                {{ ('No logs found') }}
            </div>
        </li>
    @endforelse
</ul>
