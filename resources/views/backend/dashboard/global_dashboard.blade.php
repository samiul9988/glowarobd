@extends('backend.layouts.app')

@section('content')
    @include('backend.dashboard.partials.attendance')

    <div class="alert alert-info">
        Note: You don't have permission to see dashboard reports.
    </div>
@endsection

@section('modal')
    @if (Auth::user()->user_type === 'staff')
        <div class="modal fade" id="check-in-modal" tabindex="-1" role="dialog" aria-labelledby="checkInModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="checkInModalLabel">{{ __('Check In') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="check-in-type">{{ __('Check In Type') }}</label>
                            <select name="check-in-type" id="check-in-type" class="form-control form-control-sm">
                                <option value="regular" selected>Regular</option>
                                <option value="alternative">Alternative</option>
                            </select>
                            <small class="text-danger" id="check-in-type-error"></small>
                        </div>
                        <div id="check-in-alter-date-section" style="display: none;">
                            <div class="form-group mb-2">
                                <label for="check-in-alter-date">
                                    {{ __('Alternative Date') }} *
                                </label>
                                <input type="date" id="check-in-alter-date" class="form-control"
                                    placeholder="{{ __('Select a date') }}">
                                <small class="text-danger" id="check-in-alter-date-error"></small>
                            </div>
                            <div class="form-group mb-0">
                                <label for="check-in-alter-note">
                                    {{ __('Note') }} *
                                </label>
                                <textarea rows="3" id="check-in-alter-note" class="form-control" placeholder="{{ __('Enter a note') }}"></textarea>
                                <small class="text-danger" id="check-in-alter-note-error"></small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-light"
                            data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-sm btn-success" id="btn-confirm-checkin">
                            {{ __('Confirm') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    @include('backend.dashboard.partials.attendance_script')
@endsection
