@extends('backend.layouts.app')
@php
    $shipping_address = json_decode(@$order->shipping_address, true);
@endphp
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="d-flex justify-content-start align-items-center">
                <a href="{{ route('tickets.admin_index') }}" class="mr-2 btn btn-soft-secondary btn-icon btn-circle btn-sm">
                    <i class="las la-long-arrow-alt-left"></i>
                </a>
                <h5 class="mb-0">Create Ticket</h5>
            </div>
        </div>
        <form id="service-form" action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row gutters-5">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Name <span class="text-danger"> *</span></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter customer name" value="{{ data_get($shipping_address, 'name', old('name')) }}" required>
                        <span class="text-danger error" id="name_error">@error('name') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone">Phone <span class="text-danger"> *</span></label>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter customer phone" value="{{ data_get($shipping_address, 'phone', old('phone')) }}" required>
                        <span class="text-danger error" id="phone_error">@error('phone') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter subject" value="{{ old('subject') }}">
                        <span class="text-danger error" id="subject_error">@error('subject') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="assign_to">Assign To</label>
                        <select class="form-control aiz-selectpicker" data-live-search="true" name="assign_to" id="assign_to">
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" @if(Auth::id() == $staff->id) selected @endif>
                                    {{ $staff->name . (Auth::id() == $staff->id ? ' (Me)' : '') }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger error" id="assign_to_error">@error('assign_to') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category">Category <span class="text-danger"> *</span></label>
                        <select class="form-control aiz-selectpicker" data-live-search="true" name="category" id="category" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}" @if(old('category') === $id) selected @endif>{{ ucwords($name) }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger error" id="category_error">@error('category') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="issue">Issue</label>
                        <select class="form-control aiz-selectpicker" data-live-search="true" name="issue" id="issue">
                            <option value="">Select a category first</option>
                        </select>
                        <span class="text-danger error" id="issue_error">@error('issue') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="related">Related</label>
                        <input type="text" name="related" id="related" class="form-control" placeholder="Enter related order code" value="{{ old('related', @$order->code) }}">
                        <span class="text-danger error" id="related_error">@error('related') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="priority">Priority <span class="text-danger"> *</span></label>
                        <select class="form-control" name="priority" id="priority" required>
                            <option value="">Select Priority</option>
                            <option value="low" @selected(old('priority') === 'low')>Low</option>
                            <option value="medium" @selected(old('priority') === 'medium')>Medium</option>
                            <option value="high" @selected(old('priority') === 'high')>High</option>
                            <option value="high" @selected(old('priority') === 'critical')>Critical</option>
                        </select>
                        <span class="text-danger error" id="priority_error">@error('priority') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="status">Status <span class="text-danger"> *</span></label>
                        <select class="form-control" name="status" id="status" required>
                            <option value="">Select Status</option>
                            <option value="open" selected>Open</option>
                            <option value="working" @selected(old('status') === 'working')>Working</option>
                            <option value="closed" @selected(old('status') === 'closed')>Colsed</option>
                        </select>
                        <span class="text-danger error" id="status_error">@error('status') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="message">Message <span class="text-danger"> *</span></label>
                        <textarea class="form-control" name="message" id="message" rows="7" placeholder="Enter message here" required>{{ old('message') }}</textarea>
                        <span class="text-danger error" id="message_error">@error('message') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="">Attachment</label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ ('Choose Files') }}</div>
                            <input type="hidden" name="attachments" class="selected-files">
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>
                </div>
            </div>
        </form>
        <div class="text-right">
            <button type="button" class="btn btn-secondary" id="clear-btn">Clear</button>
            <button type="submit" form="service-form" class="btn btn-primary" id="create-btn">Create</button>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        let name = '{{ data_get($shipping_address, 'name') }}';
        let phone = '{{ data_get($shipping_address, 'phone') }}';
        let related = '{{ @$order->code }}';

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

        $('#create-btn').click(function(e) {
            e.preventDefault();
            let isValid = true;
            if($('#name').val() === '') {
                $('#name_error').text('Name is required');
                isValid = false;
            } else {
                $('#name_error').text('');
            }
            if($('#phone').val() === '') {
                $('#phone_error').text('Phone is required');
                isValid = false;
            } else if($('#phone').val() !== '') {
                let phonePattern = /^[0-9]{11}$/;
                if(!phonePattern.test($('#phone').val())) {
                    $('#phone_error').text('Phone number must be 11 digits');
                    isValid = false;
                } else {
                    $('#phone_error').text('');
                }
            } else {
                $('#phone_error').text('');
            }
            if($('#issue').val() === '') {
                $('#issue_error').text('Issue is required');
                isValid = false;
            } else {
                $('#issue_error').text('');
            }
            if($('#priority').val() === '') {
                $('#priority_error').text('Priority is required');
                isValid = false;
            } else {
                $('#priority_error').text('');
            }
            if($('#status').val() === '') {
                $('#status_error').text('Status is required');
                isValid = false;
            } else {
                $('#status_error').text('');
            }
            if($('#message').val() === '') {
                $('#message_error').text('Message is required');
                isValid = false;
            } else {
                $('#message_error').text('');
            }
            if($('#category').val() === '') {
                $('#category_error').text('Category is required');
                isValid = false;
            } else {
                $('#category_error').text('');
            }

            if(isValid){
                $('#service-form').submit();
            }
        });

        $('#clear-btn').click(function() {
            $('#service-form')[0].reset();
            $('.error').text('');
        });

        if(related !== ''){
            $('#related').on('change', function() {
                $('#related').val(related);
            });
            $('#name').on('change', function() {
                $('#name').val(name);
            });
            $('#phone').on('change', function() {
                $('#phone').val(phone);
            });
        }
    });
</script>
@endsection
