@if ($notes)
    <ul class="list-unstyled">
        @foreach ($notes as $key => $note)
            <li class="alert alert-warning d-flex justify-content-between align-items-center font-weight-bold">
                <span class="me-3">{{ ucfirst(strip_tags($note['message'])) }}</span>
                @php
                    $role = Auth::user()->staff?->role?->name ?? '';
                @endphp
                @if(Auth::user()->user_type == 'admin' || in_array('processing_orders', json_decode(Auth::user()->staff?->role?->permissions ?? '[]') ?? []) || $note['created_by'] == Auth::id())
                    <button data-index="{{ $key }}" onclick="deleteNote('{{ $key }}')" type="button" class="delete-order-note btn btn-sm p-0 m-0 text-danger" title="Delete Note">
                        <i class="las la-trash"></i>
                    </button>
                @endif
            </li>
        @endforeach
    </ul>
@endif
