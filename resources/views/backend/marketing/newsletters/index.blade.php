@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ 'Send Newsletter' }}</h5>
                </div>

                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('newsletters.send') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-2 col-from-label" for="name">{{ 'Emails' }}
                                ({{ 'Users' }})</label>
                            <div class="col-sm-10">
                                <select class="form-control aiz-selectpicker" name="user_emails[]" multiple
                                    data-selected-text-format="count" data-actions-box="true">
                                    @foreach ($userEmails as $email)
                                        <option value="{{ $email }}">{{ $email }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-from-label" for="name">{{ 'Emails' }}
                                ({{ 'Subscribers' }})</label>
                            <div class="col-sm-10">
                                <select class="form-control aiz-selectpicker" name="subscriber_emails[]" multiple
                                    data-selected-text-format="count" data-actions-box="true">
                                    @foreach ($subscriberEmails as $email)
                                        <option value="{{ $email }}">{{ $email }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-from-label" for="subject">{{ 'Newsletter subject' }}</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="subject" id="subject" placeholder="Enter a subject" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-from-label" for="name">{{ 'Newsletter content' }}</label>
                            <div class="col-sm-10">
                                <textarea rows="8" class="form-control aiz-text-editor"
                                    data-buttons='[["font", ["bold", "underline", "italic"]],["para", ["ul", "ol"]], ["insert", ["link", "picture"]],["view", ["undo","redo"]]]'
                                    name="content" required></textarea>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-primary">{{ 'Send' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
