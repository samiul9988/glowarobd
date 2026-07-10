<table class="table table-bordered invoice-summary">
    <thead>
        <tr class="bg-trans-dark">
            <th class="">{{ ('Status') }}</th>
            <th data-breakpoints="lg" class="min-col">{{ ('Duration') }}</th>
            <th class="text-center">{{ ('Note') }}</th>
            <th width="20%" class="text-center">{{ ('Action') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($callLogs as $key => $callLog)
            @php
                $data = [
                    'status' => $callLog['status'] ? ucwords(str_replace('_', ' ', $callLog['status'])) : 'N/A',
                    'rescheduled_at' => $callLog['rescheduled_at'] ?? null,
                    'note' => $callLog['note'] ?? 'N/A',
                    'duration' => $callLog['duration']
                        ? $callLog['duration'] . ' ' . translate(Str::plural('minute', $callLog['duration']))
                        : 'N/A',
                    'created_at' => $callLog['created_at'],
                    'user' => $callLog['creator'],
                ];
            @endphp
            <tr id="call-log-{{ $callLog['id'] }}">
                <td>
                    {{ $callLog['status'] ? ucwords(str_replace('_', ' ', $callLog['status'])) : 'N/A' }}
                </td>
                <td>
                    @if ($callLog['duration'] != null)
                        {{ $callLog['duration'] }} {{ (Str::plural('min', $callLog['duration'])) }}
                    @else
                        {{ ('N/A') }}
                    @endif
                </td>
                <td class="">
                    @if ($callLog['hasCreator'])
                        @if($callLog['note'])
                        {{ Str::limit($callLog['note']) }}
                        <br>
                        @endif
                        <strong>{{ $callLog['creator'] }}</strong>
                        <br>
                        <span class="fs-9">{{ $callLog['created_at'] }}</span>
                    @else
                        {{ ('N/A') }}
                    @endif
                </td>
                <td class="text-center">
                    <a role="button" class="view-btn btn btn-soft-info btn-icon btn-circle btn-sm"
                        title="{{ ('View') }}" data-log="{{ json_encode($data) }}">
                        <i class="las la-info-circle"></i>
                    </a>
                    @if ($callLog['deleteable'])
                        <a href="javascript:void(0)" type="button"
                            class="btn btn-soft-danger btn-icon btn-circle btn-sm delete-call-log"
                            data-href="{{ route('call-logs.destroy', $callLog['id']) }}"
                            title="{{ ('Delete') }}">
                            <i class="las la-trash"></i>
                        </a>
                    @endif
                </td>
            </tr>
        @empty
            <tr class="footable-empty">
                <td colspan="4">
                    <div class="text-center">
                        <i class="las la-exclamation-triangle"></i>
                        {{ ('No call logs found') }}
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
