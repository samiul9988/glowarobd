@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="align-items-center">
            <h1 class="h3">{{ 'All Customers' }}</h1>
        </div>
    </div>


    <div class="card">
        <form id="sort_customers">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-0 h6">{{ 'Customers' }}</h5>
                </div>

                @php
                    $groups = App\Models\Customergroup::all();
                @endphp

                <div class="col-md-2 ml-auto">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="group" name="group"
                        onchange="sort_products()">
                        <option value="">{{ 'All Groups' }}</option>
                        @foreach ($groups as $key => $group)
                            <option value="{{ $group->id }}" @if ($group->id == $group_id) selected @endif>
                                {{ $group->group_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 ml-auto">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="group"
                        name="order_login" onchange="sort_products()">
                        <option value="login" @if ($order_login == 'login') selected @endif>{{ 'Recent Login' }}
                        </option>
                        <option value="order" @if ($order_login == 'order') selected @endif>{{ 'Delivered Order' }}
                        </option>
                    </select>
                </div>

                <div class="col-md-2 ml-auto">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="group"
                        name="verified" onchange="sort_products()">
                        <option value="">All Customers</option>
                        <option value="true" @if (request()->verified == 'true') selected @endif>Verified</option>
                        <option value="false" @if (request()->verified == 'false') selected @endif>Not Verified</option>
                    </select>
                </div>

                <div class="col-md-2 ml-auto">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control" id="search" name="search"
                            value="{{ request()->search }}" placeholder="Search by email, name or phone">
                    </div>
                </div>

                <div class="col-auto mb-2 mb-md-0">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="window.location.href='{{ route('customers.index') }}'">Reset</button>
                    <div class="dropdown mb-2 mb-md-0 d-inline-block">
                        <button type="button" data-toggle="dropdown" class="btn btn-light btn-sm p-0 py-1"
                            title="Bulk Actions">
                            <i class="las la-ellipsis-v font-weight-bold fs-24"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item btn-item" href="javascript:;" id="createNewCustomer">
                                <i class="las la-plus text-success"></i> Create New Customer
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" id="verificationModalBtn">
                                <i class="las la-user-check text-info"></i> Change Verification Status
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" data-toggle="modal" data-target="#exampleModal">
                                <i class="las la-user-friends text-primary"></i> Change Customer Group
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" onclick="bulk_delete()">
                                <i class="las la-trash text-danger"></i> Delete Selection
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" onclick="fix_user_groups()">
                                <i class="las la-users text-warning"></i> Fix Customer Groups
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <!--<th data-breakpoints="lg">#</th>-->
                            <th>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-all">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </th>
                            <th>{{ 'Name' }}</th>
                            <th>{{ 'Group Name' }}</th>
                            <th>{{ 'Delivered Order' }}</th>
                            <th>{{ 'Recent Login' }}</th>
                            <th>{{ 'Verified' }}</th>
                            <th data-breakpoints="lg">{{ 'Email Address' }}</th>
                            <th data-breakpoints="lg">{{ 'Phone' }}</th>
                            <th data-breakpoints="lg">{{ 'Package' }}</th>
                            <th data-breakpoints="lg">{{ 'Wallet Balance' }}</th>
                            <th>{{ 'Options' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $key => $user)
                            @if ($user != null)
                                <tr>
                                    <!--<td>{{ $key + 1 + ($users->currentPage() - 1) * $users->perPage() }}</td>-->
                                    <td>
                                        <div class="form-group">
                                            <div class="aiz-checkbox-inline">
                                                <label class="aiz-checkbox">
                                                    <input type="checkbox" class="check-one" name="id[]"
                                                        value="{{ $user->id }}">
                                                    <span class="aiz-square-check"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($user->banned == 1)
                                            <i class="fa fa-ban text-danger" aria-hidden="true"></i>
                                        @endif {{ $user->name }} {{-- <span title="Total Orders" class="ml-1 badge  badge-success">{{$user->orders->count()}}</span> --}}
                                    </td>

                                    <td>
                                        {{ $user->customeringroup?->group?->group_name }}
                                    </td>
                                    <td>{{ $user->delivered_order }}</td>
                                    <td>
                                        @php
                                            if ($user->recent_login != null) {
                                                $mydate = $user->recent_login;

                                                $result = Carbon::createFromFormat(
                                                    'Y-m-d H:i:s',
                                                    $mydate,
                                                )->diffForHumans('now');

                                                echo $result;
                                            }
                                        @endphp
                                    </td>
                                    <td>
                                        @if ($user->email_verified_at != null)
                                            <span class="badge badge-success p-2 w-auto">Yes</span>
                                        @else
                                            <span class="badge badge-danger p-2 w-auto">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>
                                        @if ($user->customer_package != null)
                                            {{ $user->customer_package->getTranslation('name') }}
                                        @endif
                                    </td>
                                    <td>{{ single_price($user->balance) }}</td>
                                    <td class="">
                                        <a href="{{ route('customers.details', $user->id) }}" target="_blank"
                                            class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                            title="{{ 'See Customer Details' }}">
                                            <i class="las la-eye"></i>
                                        </a>
                                        <a href="{{ route('customers.login', encrypt($user->id)) }}"
                                            class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                            title="{{ 'Log in as this Customer' }}" target="_blank">
                                            <i class="las la-edit"></i>
                                        </a>
                                        @if ($user->banned != 1)
                                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm"
                                                onclick="confirm_ban('{{ route('customers.ban', $user->id) }}');"
                                                title="{{ 'Ban this Customer' }}">
                                                <i class="las la-user-slash"></i>
                                            </a>
                                        @else
                                            <a href="#" class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                                onclick="confirm_unban('{{ route('customers.ban', $user->id) }}');"
                                                title="{{ 'Unban this Customer' }}">
                                                <i class="las la-user-check"></i>
                                            </a>
                                        @endif
                                        {{-- <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('customers.destroy', ['id' => $user->id, 'page' => request()->query('page') ? request()->query('page') : '' ] )}}" title="{{ ('Delete') }}">
                                        <i class="las la-trash"></i>
                                    </a> --}}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $users->appends(request()->input())->links() }}
                </div>
            </div>
        </form>
    </div>


    <div class="modal fade" id="confirm-ban">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h6">{{ 'Confirmation' }}</h5>
                    <button type="button" class="close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ 'Do you really want to ban this Customer?' }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ 'Cancel' }}</button>
                    <a type="button" id="confirmation" class="btn btn-primary">{{ 'Proceed!' }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirm-unban">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h6">{{ 'Confirmation' }}</h5>
                    <button type="button" class="close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ 'Do you really want to unban this Customer?' }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ 'Cancel' }}</button>
                    <a type="button" id="confirmationunban" class="btn btn-primary">{{ 'Proceed!' }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')

    {{-- Change Group Modal --}}
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        {{ 'Choose customer groups' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="min-height: 300px">
                    <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                        id="customer_group" name="customer_group">
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="customergroup()">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Change Verification Status Modal --}}
    <div class="modal fade" id="verificationModal" tabindex="-1" role="dialog"
        aria-labelledby="verificationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verificationModalLabel">
                        Change Verification Status
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="min-height: 300px">
                    <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                        id="verification_status" name="verification_status">
                        <option value="1" selected>Verified</option>
                        <option value="0">Not Verified</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="changeVerificationStatus()">Save</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Create New Customer  --}}
    <div class="modal fade" id="createNewCustomerModal" tabindex="-1" role="dialog"
        aria-labelledby="createNewCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createNewCustomerModalLabel">
                        Create New Customer
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="min-height: 300px">
                    <div class="form-group">
                        <label for="customer_name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name"
                            placeholder="Enter customer name">
                        <span class="text-danger fs-10" id="customer_name_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="customer_email">Email</label>
                        <input type="email" class="form-control" id="customer_email" name="customer_email"
                            placeholder="Enter customer email">
                        <span class="text-danger fs-10" id="customer_email_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="customer_phone">Phone</label>
                        <input type="text" class="form-control" id="customer_phone" name="customer_phone"
                            placeholder="Enter customer phone">
                        <span class="text-danger fs-10" id="customer_phone_error"></span>
                    </div>
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="customer_password">Password <span class="text-danger">*</span></label>
                            <span role="button" class="badge badge-inline badge-info fs-9 ml-auto"
                                onclick="$('#customer_password').val(generatePassword(8))">
                                Generate
                            </span>
                        </div>
                        <input type="text" class="form-control" id="customer_password" name="customer_password"
                            placeholder="Enter customer password">
                        <span class="text-danger fs-10" id="customer_password_error"></span>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="notify_customer" class="col-from-label">Notify Customer ?</label>
                        </div>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" id="notify_customer" name="notify_customer" value="1" onchange="this.value = this.checked ? 1 : 0" checked>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="createNewCustomer()">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if (this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

        function sort_customers(el) {
            $('#sort_customers').submit();
        }

        function confirm_ban(url) {
            $('#confirm-ban').modal('show', {
                backdrop: 'static'
            });
            document.getElementById('confirmation').setAttribute('href', url);
        }

        function confirm_unban(url) {
            $('#confirm-unban').modal('show', {
                backdrop: 'static'
            });
            document.getElementById('confirmationunban').setAttribute('href', url);
        }


        function customergroup() {
            var data = new FormData($('#sort_customers')[0]);
            data.append('status', $('#customer_group').val());
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('customer_group') }}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response == 1) {
                        location.reload();
                    }
                }
            });
        }

        $('#createNewCustomer').on('click', function() {
            $('#customer_name').val('');
            $('#customer_email').val('');
            $('#customer_phone').val('');
            $('#customer_name_error').text('');
            $('#customer_email_error').text('');
            $('#customer_phone_error').text('');
            $('#customer_password_error').text('');
            $('#notify_customer').prop('checked', true);
            $('#customer_password').val(generatePassword(8));

            $('#createNewCustomerModal').modal('show');
        });

        function generatePassword(length) {
            var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            var password = "";
            for (var i = 0; i < length; i++) {
                var randomIndex = Math.floor(Math.random() * charset.length);
                password += charset.charAt(randomIndex);
            }
            return password;
        }

        $('#customer_phone').on('input', function() {
            var sanitizedValue = this.value.replace(/[^0-9+\-\s()]/g, '');
            sanitizedValue = sanitizedValue.replace('-','');
            let maxLength = sanitizedValue.startsWith('+88') ? 14 : 11;
            if (sanitizedValue.length > maxLength) {
                sanitizedValue = sanitizedValue.slice(0, maxLength);
            }
            this.value = sanitizedValue;
        });

        function createNewCustomer() {
            let name = $('#customer_name').val().trim();
            let email = $('#customer_email').val().trim();
            let phone = $('#customer_phone').val().trim();
            let password = $('#customer_password').val().trim();
            let isValid = true;

            if (name === '') {
                $('#customer_name_error').text('Name is required');
                isValid = false;
            } else if (name.length < 3) {
                $('#customer_name_error').text('Name should be at least 3 characters long');
                isValid = false;
            }  else {
                $('#customer_name_error').text('');
            }

            if (email.length === 0 && phone.length === 0) {
                $('#customer_email_error').text('Email or phone is required');
                $('#customer_phone_error').text('Email or phone is required');
                isValid = false;
            } else {
                $('#customer_email_error').text('');
                $('#customer_phone_error').text('');
            }

            if (email !== '') {
                let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email)) {
                    $('#customer_email_error').text('Please enter a valid email address');
                    isValid = false;
                } else {
                    $('#customer_email_error').text('');
                }
            }

            if (phone !== '') {
                let phonePattern = /^[0-9+\-\s()]*$/;
                if (!phonePattern.test(phone)) {
                    $('#customer_phone_error').text('Please enter a valid phone number');
                    isValid = false;
                } else {
                    $('#customer_phone_error').text('');
                }
            }

            if (password === '') {
                $('#customer_password_error').text('Password is required');
                isValid = false;
            } else if (password.length < 6) {
                $('#customer_password_error').text('Password should be at least 6 characters long');
                isValid = false;
            } else {
                $('#customer_password_error').text('');
            }

            if (!isValid) {
                return;
            }

            $.ajax({
                url: "{{ route('customers.create') }}",
                type: 'POST',
                data: {
                    name: name,
                    email: email,
                    phone: phone,
                    password: password,
                    notify_customer: $('#notify_customer').is(':checked') ? 1 : 0
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message, window.location.href);
                    } else {
                        showAlert('error', response.message || 'An error occurred.');
                    }
                },
                error: function(xhr, error, status) {
                    let errors = xhr.responseJSON.errors;
                    if (errors) {
                        if (errors.name) {
                            $('#customer_name_error').text(errors.name[0]);
                        }
                        if (errors.email) {
                            $('#customer_email_error').text(errors.email[0]);
                        }
                        if (errors.phone) {
                            $('#customer_phone_error').text(errors.phone[0]);
                        }
                        if (errors.password) {
                            $('#customer_password_error').text(errors.password[0]);
                        }
                    } else {
                        showAlert('error', xhr.responseJSON.message || 'Something went wrong.');
                    }
                }
            });
        }

        $('#verificationModalBtn').on('click', function() {
            let ids = $('.check-one:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                showAlert('error', 'Please select at least one customer.');
                return;
            }
            $('#verificationModal').modal('show');
        });

        function changeVerificationStatus() {
            let ids = $('.check-one:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                showAlert('error', 'Please select at least one customer.');
                return;
            }
            $('#verificationModal').modal('hide');
            let status = $('#verification_status').val();
            $.ajax({
                url: "{{ route('customers.change-verification-status') }}",
                type: 'POST',
                data: {
                    ids: ids,
                    verified: status
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message, window.location.href);
                    } else {
                        showAlert('error', response.message || 'An error occurred.');
                    }
                },
                error: function(error, xhr, status) {
                    showAlert('error', 'Something went wrong.');
                    console.log(error);
                }
            });
        }

        function sort_products(el) {
            $('#sort_customers').submit();
        }

        function bulk_delete() {
            let ids = $('.check-one:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                showAlert('error', 'Please select at least one customer.');
                return;
            }
            var data = new FormData($('#sort_customers')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('bulk-customer-delete') }}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response == 1) {
                        location.reload();
                    }
                }
            });
        }

        function fix_user_groups() {
            Swal.fire({
                title: 'Fix Customer Groups?',
                text: "This will update customer groups based on their order history. This will run in the background and may take some time to updtae the list.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Proceed!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('customers.fix-groups') }}",
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response) {
                                showAlert('success', response.message);
                            }
                        },
                        error: function(error, xhr, status) {
                            showAlert('error', 'Something went wrong.');
                            console.log(error);
                        }
                    });
                }
            });
        }
    </script>
@endsection
