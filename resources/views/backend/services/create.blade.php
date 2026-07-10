@extends('backend.layouts.app')
@php
    $issues = ['General Query', 'Refund Issue', 'Authenticity Issue', 'Skincare Suggestion', 'Exchange Product', 'Product Query', 'Restock Reminder'];
    $shipping_address = json_decode(@$order->shipping_address, true);
@endphp
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <h5>Create Ticket</h5>
        </div>
        <form id="service-form" action="{{ route('services.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Name <span class="text-danger"> *</span></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter customer name" value="{{ data_get($shipping_address, 'name', old('name')) }}" required @isset($order) disabled @endisset>
                        <span class="text-danger error" id="name_error">@error('name') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone">Phone <span class="text-danger"> *</span></label>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter customer phone" value="{{ data_get($shipping_address, 'phone', old('phone')) }}" required @isset($order) disabled @endisset>
                        <span class="text-danger error" id="phone_error">@error('phone') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="subject">Subject <span class="text-danger"> *</span></label>
                        <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter subject" value="{{ old('subject') }}" required>
                        <span class="text-danger error" id="subject_error">@error('subject') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="issue">Issue <span class="text-danger"> *</span></label>
                        <select class="form-control" name="issue" id="issue" required>
                            <option value="">Select Issue</option>
                            @foreach($issues as $issue)
                                <option value="{{ Str::slug($issue) }}" @if(old('issue') === Str::slug($issue)) selected @endif>{{ $issue }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger error" id="issue_error">@error('issue') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="related">Related</label>
                        <input type="text" name="related" id="related" class="form-control" placeholder="Enter related order code" value="{{ old('related', @$order->code) }}">
                        <span class="text-danger error" id="related_error">@error('related') {{ $message }} @enderror</span>
                    </div>
                </div>
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Status <span class="text-danger"> *</span></label>
                        <select class="form-control" name="status" id="status" required>
                            <option value="">Select Status</option>
                            <option value="open" @selected(old('status') === 'open')>Open</option>
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
        let related = '{{ @$order->id }}';
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
            if($('#subject').val() === '') {
                $('#subject_error').text('Subject is required');
                isValid = false;
            } else {
                $('#subject_error').text('');
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