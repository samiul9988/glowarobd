@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Staff Information') }}</h5>
            </div>

            <form class="form-horizontal" action="{{ route('staffs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ===== Account Info ===== --}}
                    <h6 class="mb-3 font-weight-bold text-uppercase text-muted">{{ ('Account Info') }}</h6>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{ ('Profile Picture') }}</label>
                        <div class="col-sm-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="profile_picture" value="{{ old('profile_picture') }}" class="selected-files">
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{ ('Name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Name') }}" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="email">Email (Official)</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Email') }}" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="mobile">{{ ('Phone') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Phone') }}" id="mobile" name="mobile" class="form-control" value="{{ old('mobile') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="password">{{ ('Password') }}</label>
                        <div class="col-sm-9">
                            <input type="password" placeholder="{{ ('Password') }}" id="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{ ('Role') }}</label>
                        <div class="col-sm-9">
                            <select name="role_id" required class="form-control aiz-selectpicker" data-live-search="true">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="gender">{{ ('Gender') }}</label>
                        <div class="col-sm-9">
                            <select name="gender" id="gender" class="form-control aiz-selectpicker">
                                <option value="">— Select —</option>
                                <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                                <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                                <option value="Other" @selected(old('gender') === 'Other')>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="date_of_birth">{{ ('Date of Birth') }}</label>
                        <div class="col-sm-9">
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                        </div>
                    </div>

                    {{-- ===== Employment Details ===== --}}
                    <hr>
                    <h6 class="mb-3 font-weight-bold text-uppercase text-muted">{{ ('Employment Details') }}</h6>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="employee_id">{{ ('Employee ID') }}</label>
                        <div class="col-sm-9">
                            <input type="text" id="employee_id" class="form-control bg-light text-muted"
                                   value="{{ \App\Models\Staff::generateEmployeeId() }}"
                                   readonly tabindex="-1">
                            <small class="text-muted">{{ ('Auto-generated and cannot be changed.') }}</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="joining_date">{{ ('Joining Date') }}</label>
                        <div class="col-sm-9">
                            <input type="date" id="joining_date" name="joining_date" class="form-control" value="{{ old('joining_date') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="salary">{{ ('Salary') }}</label>
                        <div class="col-sm-9">
                            <input type="number" step="1" placeholder="{{ ('Salary') }}" id="salary" name="salary" class="form-control" value="{{ old('salary') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="shift">{{ ('Shift') }}</label>
                        <div class="col-sm-9">
                            <select name="shift" id="shift" class="form-control aiz-selectpicker">
                                <option value="">{{ ('Select Shift') }}</option>
                                @foreach(\App\Enums\ShiftEnum::options() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('shift') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="working_hours">{{ ('Working Hours / Day') }}</label>
                        <div class="col-sm-9">
                            <input type="number" step="1" min="0" max="24" placeholder="8" id="working_hours" name="working_hours" class="form-control" value="{{ old('working_hours') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{ ('Weekly Off Day(s)') }}</label>
                        <div class="col-sm-9">
                            @foreach(['Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'] as $day)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="weekly_offday[]" id="offday_{{ $day }}" value="{{ $day }}" @checked(in_array($day, old('weekly_offday', [])))>
                                    <label class="form-check-label" for="offday_{{ $day }}">{{ $day }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ===== Personal Info ===== --}}
                    <hr>
                    <h6 class="mb-3 font-weight-bold text-uppercase text-muted">{{ ('Personal Info') }}</h6>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="personal_email">Email (Personal)</label>
                        <div class="col-sm-9">
                            <input type="email" id="personal_email" name="personal_email" class="form-control" value="{{ old('personal_email') }}" placeholder="Personal Email">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="address">{{ ('Address') }}</label>
                        <div class="col-sm-9">
                            <textarea id="address" name="address" rows="2" placeholder="{{ ('Address') }}" class="form-control">{{ old('address') }}</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="educational_background">{{ ('Educational Background') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('e.g. BSc, MBA, HSC') }}" id="educational_background" name="educational_background" class="form-control" value="{{ old('educational_background') }}">
                            <small class="text-muted">{{ ('Separate multiple entries with commas') }}</small>
                        </div>
                    </div>

                    {{-- ===== Emergency Contact ===== --}}
                    <hr>
                    <h6 class="mb-3 font-weight-bold text-uppercase text-muted">{{ ('Emergency Contact') }}</h6>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="ec_father_name">{{ ("Father's Name") }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ("Father's Name") }}" id="ec_father_name" name="ec_father_name" class="form-control" value="{{ old('ec_father_name') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="ec_mother_name">{{ ("Mother's Name") }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ("Mother's Name") }}" id="ec_mother_name" name="ec_mother_name" class="form-control" value="{{ old('ec_mother_name') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="ec_spouse_name">{{ ('Husband / Wife Name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Husband / Wife Name') }}" id="ec_spouse_name" name="ec_spouse_name" class="form-control" value="{{ old('ec_spouse_name') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="ec_contact_number">{{ ('Contact Number') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Contact Number') }}" id="ec_contact_number" name="ec_contact_number" class="form-control" value="{{ old('ec_contact_number') }}">
                        </div>
                    </div>

                    {{-- ===== HR Details ===== --}}
                    <hr>
                    <h6 class="mb-3 font-weight-bold text-uppercase text-muted">{{ ('HR Details') }}</h6>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="employment_status">{{ ('Employment Status') }}</label>
                        <div class="col-sm-9">
                            <select name="employment_status" id="employment_status" class="form-control">
                                <option value="active" @selected(old('employment_status', 'active') === 'active')>Active</option>
                                <option value="probation" @selected(old('employment_status') === 'probation')>Probation</option>
                                <option value="on_leave" @selected(old('employment_status') === 'on_leave')>On Leave</option>
                                <option value="resigned" @selected(old('employment_status') === 'resigned')>Resigned</option>
                                <option value="terminated" @selected(old('employment_status') === 'terminated')>Terminated</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="blood_group">{{ ('Blood Group') }}</label>
                        <div class="col-sm-9">
                            <select name="blood_group" id="blood_group" class="form-control aiz-selectpicker" data-live-search="true">
                                <option value="">— Select —</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                                    <option value="{{ $bg }}" @selected(old('blood_group') === $bg)>{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3 font-weight-bold text-uppercase text-muted small">{{ ('Bank Account') }}</h6>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="bank_name">{{ ('Bank Name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Bank Name') }}" id="bank_name" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="account_no">{{ ('Account Number') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Account Number') }}" id="account_no" name="account_no" class="form-control" value="{{ old('account_no') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="bank_branch">{{ ('Branch') }}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Branch') }}" id="bank_branch" name="bank_branch" class="form-control" value="{{ old('bank_branch') }}">
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3 font-weight-bold text-uppercase text-muted small">{{ ('Separation (if applicable)') }}</h6>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="resign_date">{{ ('Resign Date') }}</label>
                        <div class="col-sm-9">
                            <input type="date" id="resign_date" name="resign_date" class="form-control" value="{{ old('resign_date') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{ ('Resignation Letter') }}</label>
                        <div class="col-sm-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="document">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="resignation_letter" value="{{ old('resignation_letter') }}" class="selected-files">
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="termination_date">{{ ('Termination Date') }}</label>
                        <div class="col-sm-9">
                            <input type="date" id="termination_date" name="termination_date" class="form-control" value="{{ old('termination_date') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="termination_reason">{{ ('Termination Reason') }}</label>
                        <div class="col-sm-9">
                            <textarea id="termination_reason" name="termination_reason" rows="3" class="form-control" placeholder="{{ ('Reason for termination (if applicable)') }}">{{ old('termination_reason') }}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="note">{{ ('Internal Note') }}</label>
                        <div class="col-sm-9">
                            <textarea id="note" name="note" rows="3" class="form-control" placeholder="{{ ('HR notes visible to admin only') }}">{{ old('note') }}</textarea>
                        </div>
                    </div>

                    {{-- ===== Events ===== --}}
                    <hr>
                    <h6 class="mb-3 font-weight-bold text-uppercase text-muted">{{ ('Events') }}</h6>

                    <div id="events-wrapper">
                        <div class="events-row border rounded p-3 mb-2 bg-light">
                            <div class="form-group row mb-2">
                                <label class="col-sm-3 col-from-label">Type</label>
                                <div class="col-sm-9">
                                    <select name="event_type[]" class="form-control">
                                        <option value="any" selected>Any</option>
                                        <option value="appointment-letter">Appointment Letter</option>
                                        <option value="joining-letter">Joining Letter</option>
                                        <option value="resignation-letter">Resignation Letter</option>
                                        <option value="promotion-increment">Promotion / Increment</option>
                                        <option value="noc">No Objection Certificate</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-3 col-from-label">{{ ('Date') }}</label>
                                <div class="col-sm-9">
                                    <input type="date" name="event_date[]" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-3 col-from-label">{{ ('Title') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" name="event_title[]" placeholder="{{ ('Event Title') }}" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label class="col-sm-3 col-from-label">{{ ('Attachment') }}</label>
                                <div class="col-sm-9">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                        <input type="hidden" name="event_attachment[]" value="" class="selected-files">
                                    </div>
                                    <div class="file-preview box sm"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-1 mb-3" id="add-event-row">
                        <i class="las la-plus"></i> {{ ('Add More') }}
                    </button>

                    {{-- ===== Attachments ===== --}}
                    <hr>
                    <h6 class="mb-3 font-weight-bold text-uppercase text-muted">{{ ('Attachments') }}</h6>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{ ('CV') }}</label>
                        <div class="col-sm-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="document" data-multiple="true">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="attachment_cv" value="" class="selected-files">
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{ ('NID') }}</label>
                        <div class="col-sm-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="attachment_nid" value="" class="selected-files">
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">{{ ('Certificates') }}</label>
                        <div class="col-sm-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="document" data-multiple="true">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="attachment_certificate" value="" class="selected-files">
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{ ('Save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    document.getElementById('add-event-row').addEventListener('click', function () {
        var wrapper = document.getElementById('events-wrapper');
        var firstRow = wrapper.querySelector('.events-row');
        var clone = firstRow.cloneNode(true);
        // Clear values in clone
        clone.querySelectorAll('input').forEach(function (input) {
            input.value = '';
        });
        clone.querySelectorAll('.file-amount').forEach(function (el) {
            el.textContent = '{{ ('Choose File') }}';
        });
        clone.querySelectorAll('.file-preview').forEach(function (el) {
            el.innerHTML = '';
        });
        // Add remove button if not present
        if (!clone.querySelector('.remove-event-row')) {
            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-outline-danger mt-2 remove-event-row';
            removeBtn.innerHTML = '<i class="las la-times"></i> {{ ('Remove') }}';
            removeBtn.addEventListener('click', function () {
                clone.remove();
            });
            clone.appendChild(removeBtn);
        }
        wrapper.appendChild(clone);
        // Re-init aiz uploader for new row
        if (typeof AIZ !== 'undefined' && AIZ.plugins && AIZ.plugins.aizUploader) {
            AIZ.plugins.aizUploader();
        }
    });
</script>
@endsection
