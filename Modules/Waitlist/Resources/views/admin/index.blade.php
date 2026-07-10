@extends('waitlist::layouts.admin')
@php
    $products = Cache::remember('products_for_waitlist', now()->addHours(3), function () {
        return \App\Models\Product::published()->latest()->pluck('name', 'id')->toArray();
    });
@endphp
@section('content')
    @if(Auth::user()->user_type === 'admin')
        <div class="card">
            <div class="card-header d-block">
                <div class="form-group d-flex align-items-center justify-content-between mb-0">
                    <div class="">
                        <label class="col-from-label h6" for="automated_notify">
                            Enable/Disable Automated Notify Feature
                            @include('components.tooltip', [
                                'title' => 'If enabled, the system will automatically notify customers on the waitlist when the product is back in stock.',
                            ])
                        </label>
                    </div>
                    <div class="">
                        <label class="aiz-switch aiz-switch-success mb-0">
                            <input type="checkbox" id="automated_notify" value="1" onchange="updateSetting(this, 'waitlist_automated_notify')" @if (get_setting('waitlist_automated_notify', 0) == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div>
                    <small class="text-muted">If enabled, the system will automatically notify customers on the waitlist when the product is back in stock.</small>
                </div>
            </div>
        </div>
    @endif
    <div class="card">
        <form class="" id="sort_orders" action="" method="GET">
            <div class="card-header row gutters-5">
                <div class="col-md-2 mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="product" data-live-search="true">
                        <option value="">Filter By Products</option>
                        @foreach ($products as $id => $name)
                            <option value="{{ $id }}" @if ($id == request()->product) selected @endif>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="notified">
                        <option value="" selected>Filter By Status</option>
                        <option value="1" @if (intval(request()->notified) === 1) selected @endif>Notified</option>
                        <option value="0" @if (intval(request()->notified) !== 1) selected @endif>Pending</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="channel">
                        <option value="" selected>Filter By Channel</option>
                        <option value="email" @if (request()->channel === 'email') selected @endif>Email</option>
                        <option value="phone" @if (request()->channel === 'phone') selected @endif>Phone</option>
                    </select>
                </div>

                <div class="col-md-2 mb-2 mb-md-0">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control form-control-sm"
                            value="{{ request()->date }}" name="date" placeholder="Filter By Date" data-format="DD-MM-Y"
                            data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-2 mb-2 mb-md-0">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control form-control-sm" id="search" name="search"
                            value="{{ request()->search }}" placeholder="Search...">
                    </div>
                </div>

                <div class="col-auto mb-2 mb-md-0">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="window.location.href='{{ route('admin.waitlists.index') }}'">Reset</button>
                    <div class="dropdown mb-2 mb-md-0 d-inline-block">
                        <button type="button" data-toggle="dropdown" class="btn btn-light btn-sm p-0 py-1">
                            <i class="las la-ellipsis-v font-weight-bold fs-24"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item btn-item" href="javascript:;" id="silent_bulk_notify">
                                <i class="las la-bell-slash text-warning"></i> Only Mark As Notified
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" id="bulk_notify">
                                <i class="las la-bell text-info"></i> Notify And Mark As Notified
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" id="bulk_delete">
                                <i class="las la-trash text-danger"></i> Delete selection
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="card-body">
            <table id="waitlist-table" class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th width="5%">
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>Product</th>
                        <th class="text-center">Notify To</th>
                        <th class="text-center">Status</th>
                        <th data-breakpoints="md" class="text-center">Created At</th>
                        <th data-breakpoints="md" class="text-center">Notified At</th>
                        <th class="text-center" width="15%">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($waitlists as $key => $waitlist)
                        <tr>
                            <td>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]"
                                                value="{{ $waitlist->id }}">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($waitlist->product)
                                    <a href="{{ to_frontend(route('product', $waitlist->product->slug)) }}" target="_blank" class="text-muted">
                                        {{ Str::limit($waitlist->product->name, 70) }}
                                    </a>
                                    @if (strlen($waitlist->product->name) > 70)
                                        @include('components.tooltip', [
                                            'title' => $waitlist->product->name,
                                        ])
                                    @endif
                                    @php
                                        $currentStock = $waitlist->product?->stocks?->first()?->qty ?? 0;
                                    @endphp
                                    <span class="d-block text-muted fs-10 font-weight-bold {{ $currentStock > 0 ? 'text-success' : 'text-danger' }}">
                                        Current Stock: {{ $currentStock }}
                                    </span>
                                @else
                                    <span class="text-danger font-italic">Product Deleted</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $waitlist->contact }}
                            </td>
                            <td class="text-center">
                                @if ($waitlist->notified)
                                    <span class="badge badge-inline badge-success font-weight-bold">Notified</span>
                                @else
                                    <span class="badge badge-inline badge-info font-weight-bold">Pending</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $waitlist->created_at->diffForHumans() }}
                            </td>
                            <td class="text-center">
                                @if ($waitlist->notified && $waitlist->notified_at)
                                    {{ $waitlist->notified_at->diffForHumans() }}
                                @else
                                    <span class="text-muted font-weight-bold">Not Notified Yet</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if ($waitlist->notified)
                                    <button class="btn btn-soft-success btn-icon btn-circle btn-sm" data-toggle="tooltip" data-title="Already Notified" disabled>
                                        <i class="las la-bell"></i>
                                    </button>
                                @else
                                <button class="btn btn-soft-warning silent-notify-btn btn-icon btn-circle btn-sm" data-toggle="tooltip" data-title="Only Mark as Notified" data-id="{{ $waitlist->id }}">
                                    <i class="las la-bell-slash"></i>
                                </button>
                                <button class="btn btn-soft-info notify-btn btn-icon btn-circle btn-sm" data-toggle="tooltip" data-title="Notify & Mark as Notified" data-id="{{ $waitlist->id }}">
                                    <i class="las la-bell"></i>
                                </button>
                                @endif
                                <button class="btn btn-soft-danger btn-icon btn-circle btn-sm delete-btn" data-id="{{ $waitlist->id }}" data-toggle="tooltip" data-title="Delete">
                                    <i class="las la-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $waitlists->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).on("change", ".check-all", function() {
            if (this.checked) {
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }
        });

        $('#bulk_delete').on('click', function() {
            if (isValidRequest()) {
                let ids = $('input[name="id[]"]:checked').map(function() {
                    return this.value;
                }).get();
                let button = $(this);
                Swal.fire({
                    title: 'Are You Sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Delete Selected!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.prop('disabled', true);
                        $.ajax({
                            url: "{{ route('admin.waitlists.bulk-destroy') }}",
                            type: "POST",
                            data: {
                                ids: ids,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    showAlert('success', response.message, window.location.href);
                                } else {
                                    button.prop('disabled', false);
                                    showAlert('error', response.message || 'Something went wrong');
                                }
                            },
                            error: function(xhr) {
                                button.prop('disabled', false);
                                showAlert('error', xhr.responseJSON.message);
                            }
                        });
                    }
                });
            }
        });

        $('#waitlist-table').on('click', '.delete-btn', function() {
            // sweetalert delete
            let waitlistId = $(this).data('id');
            let button = $(this);
            Swal.fire({
                title: 'Are You Sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete It!'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.prop('disabled', true);
                    $.ajax({
                        url: `{{ route('admin.waitlists.destroy', ':id') }}`.replace(':id', waitlistId),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                showAlert('success', response.message, window.location.href);
                            } else {
                                button.prop('disabled', false);
                                showAlert('error', response.message || 'Something went wrong');
                            }
                        },
                        error: function(xhr) {
                            button.prop('disabled', false);
                            showAlert('error', xhr.responseJSON.message);
                        }
                    });
                }
            });
        });

        $('#waitlist-table').on('click', '.silent-notify-btn', function() {
            let waitlistId = $(this).data('id');
            let button = $(this);
            Swal.fire({
                title: 'Are You Sure?',
                text: "This action won't send any email/sms notification but mark the entry as notified!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.prop('disabled', true);
                    sendNotifyRequest(waitlistId, button, true);
                }
            });
        });

        $('#waitlist-table').on('click', '.notify-btn', function() {
            let waitlistId = $(this).data('id');
            let button = $(this);
            Swal.fire({
                title: 'Are You Sure?',
                text: "This action will send email/sms notification and mark the entry as notified!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Notify!'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.prop('disabled', true);
                    sendNotifyRequest(waitlistId, button);
                }
            });
        });

        $('#bulk_notify').on('click', function() {
            if (isValidRequest()) {
                let ids = $('input[name="id[]"]:checked').map(function() {
                    return this.value;
                }).get();
                let button = $(this);
                Swal.fire({
                    title: 'Are You Sure?',
                    text: "This action will send email/sms notification and mark all the entries as notified!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Notify!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.prop('disabled', true);
                        sendNotifyRequest(ids, button);
                    }
                });
            }
        });

        $('#silent_bulk_notify').on('click', function() {
            if (isValidRequest()) {
                let ids = $('input[name="id[]"]:checked').map(function() {
                    return this.value;
                }).get();
                let button = $(this);
                Swal.fire({
                    title: 'Are You Sure?',
                    text: "This action won't send any email/sms notification but mark all the entries as notified!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Proceed!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.prop('disabled', true);
                        sendNotifyRequest(ids, button, true);
                    }
                });
            }
        });

        function isValidRequest() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                showAlert('error', '{{ 'Please select at least one item' }}');
                return false;
            }
            return true;
        }

        function sendNotifyRequest(ids, button, silent = false) {
            $.ajax({
                url: "{{ route('admin.waitlists.notify') }}",
                type: "POST",
                data: {
                    id: ids,
                    silent: silent,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message, window.location.href);
                    } else {
                        button.prop('disabled', false);
                        showAlert('error', response.message || 'Something went wrong');
                    }
                },
                error: function(xhr) {
                    button.prop('disabled', false);
                    showAlert('error', xhr.responseJSON.message);
                }
            });
        }

        function updateSetting(el, type){
            let value = $(el).is(':checked') ? 1 : 0;
            $(el).prop('disabled', true);

            $.post("{{ route('business_settings.update.activation') }}", {
                _token:'{{ csrf_token() }}',
                type:type,
                value:value
            }, function(data){
                if (data == '1') {
                    showAlert('success', 'Automated notify settings changed.');
                } else {
                    showAlert('error', 'Something went wrong');
                    $(el).prop('checked', !value);
                }
                $(el).prop('disabled', false);
            }).fail(function() {
                showAlert('error', 'Something went wrong');
                $(el).prop('disabled', false);
                $(el).prop('checked', !value);
            });
        }
    </script>
@endsection
