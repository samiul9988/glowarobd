@extends(config('app.theme') . 'frontend.layouts.user_panel')

@section('panel_content')
    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ ('Support Ticket') }}</h1>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mx-auto mb-3">
            {{-- <a href="{{ route('user.tickets.create') }}"> --}}
            <div class="p-3 rounded mb-3 c-pointer text-center bg-white shadow-sm hov-shadow-lg has-transition"
                data-toggle="modal" data-target="#ticket_modal">
                <span
                    class="size-70px rounded-circle mx-auto bg-secondary d-flex align-items-center justify-content-center mb-3">
                    <i class="las la-plus la-3x text-white"></i>
                </span>
                <div class="fs-20 text-primary">{{ ('Create a Ticket') }}</div>
            </div>
            {{-- </a> --}}
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ('Tickets') }}</h5>
        </div>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th data-breakpoints="lg">{{ ('Ticket ID') }}</th>
                        <th data-breakpoints="lg">{{ ('Sending Date') }}</th>
                        <th>{{ ('Issue') }}</th>
                        <th>{{ ('Status') }}</th>
                        <th data-breakpoints="lg">{{ ('Options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $key => $ticket)
                        <tr>
                            <td>#{{ $ticket->code }}</td>
                            <td>{{ $ticket->created_at->format('d-m-Y \a\t h:i A') }}</td>
                            <td>{{ Str::headline($ticket->issue) }}</td>
                            <td>
                                @php
                                    $class = match($ticket->status) {
                                        'open' => 'warning',
                                        'working' => 'info',
                                        'closed' => 'danger',
                                        default => 'danger',
                                    };
                                @endphp
                                <span class="badge badge-inline badge-{{ $class }} font-weight-bold">
                                    {{ strtoupper($ticket->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('tickets.show', encrypt($ticket->id)) }}"
                                    class="btn btn-styled btn-link py-1 px-0 icon-anim text-underline--none">
                                    {{ ('View Details') }}
                                    <i class="la la-angle-right text-sm"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
@endsection

@section('modal')
    <div class="modal fade" id="ticket_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title strong-600 heading-5">{{ ('Create a Ticket') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-3 pt-2">
                    <form class="" action="{{ route('tickets.user_store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{ ('Category') }} <span class="text-danger">*</span></label>
                                    <select style="border-radius: 5px !important;" class="form-control aiz-selectpicker" data-live-search="true" name="category" id="category" required>
                                        <option value="">Select Category</option>
                                        @foreach (\App\Models\TicketCategory::active()->whereNull('parent_id')->pluck('name', 'id') as $id => $name)
                                            <option value="{{ $id }}"
                                                @if (old('category') === $id) selected @endif>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{ ('Issue') }}</label>
                                    <select style="border-radius: 5px !important;" class="form-control aiz-selectpicker" data-live-search="true" name="issue" id="issue">
                                        <option value="">Select a category first</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{ ('Order Number') }} <span class="text-secondary fs-11">({{ ('Optional') }})</span></label>
                                    @php
                                        $orders = \App\Models\Order::where('user_id', Auth::user()->id)->orderBy('date', 'desc')->select('id', 'code')->get();
                                    @endphp
                                    <select style="border-radius: 5px !important;" class="form-control" name="related" id="related">
                                        <option value="">{{ ('Select order') }}</option>
                                        @foreach ($orders as $order)
                                            <option value="{{ $order->id }}">#{{ $order->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{ ('Message') }} <span class="text-danger">*</span></label>
                                    <textarea type="text" style="border-radius: 5px !important;" class="form-control mb-3" rows="3" name="details" placeholder="{{ ('Enter message') }}" required></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{ ('Photo') }}</label>
                                    <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                                {{ ('Browse') }}</div>
                                        </div>
                                        <div style="border-radius: 0 !important;" class="form-control file-amount">{{ ('Choose File') }}</div>
                                        <input type="hidden" name="attachments" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right mt-2">
                            <button type="submit" class="btn btn-primary">{{ ('Create') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $('#category').on('change', function() {
            let categoryId = $(this).val();
            if(categoryId) {
                $('#issue').empty().append('<option value="">Loading...</option>');
                $('#issue').selectpicker('refresh');
                $.ajax({
                    url: '{{ route('ticket_categories.get_subcategories') }}',
                    type: 'GET',
                    data: { category_id: categoryId },
                    success: function(response) {
                        if(response.data.length === 0) {
                            $('#issue').empty().append('<option value="">No issues available</option>');
                        }else{
                            $('#issue').empty().append('<option value="">Select an issue</option>');
                            let data = response.data;
                            data.forEach(item => {
                                $('#issue').append('<option value="' + item.slug + '">' + item.name + '</option>');
                            });
                        }
                        $('#issue').selectpicker('refresh');
                    }
                });
            } else {
                $('#issue').empty().append('<option value="">Select a category first</option>');
                $('#issue').selectpicker('refresh');
            }
        });
    </script>
@endsection
