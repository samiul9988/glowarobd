@extends(config('app.theme').'frontend.layouts.user_panel')

@section('meta')
<x-seo />
@endsection

@section('panel_content')
    <div class="user-profile row bg-white py-2 rounded-sm align-items-center m-0 mb-3 px-2">
        <div class=" p-0 col-12 col-md-6 py-2 py-md-0">
            <p class="m-0 fw-500 fs-22 text-capitalize  text-lg-left text-start px-2">
                <span class="fw-700 " style="color:#FA7E16">Welcome, </span> {{ Auth::user()->name }}
            </p>
        </div>
        <div class="col-6 p-0 align-items-center justify-content-center justify-content-md-end pr-1 pr-md-4 d-none d-md-flex">
                <span class="avatar avatar-md pr-2 pr-md-0">
                    @if (Auth::user()->avatar_original != null)
                        <img class="show-user-avatar" src="{{ uploaded_asset(Auth::user()->avatar_original) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                    @else
                        <img id="user-avatar-default" src="{{ static_asset('assets/img/avatar-place.png') }}" class="image rounded-circle" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                    @endif

                </span>
                @php

                    @$user_group = $currentlyAuthenticatedUser->customeringroup;

                @endphp
                <div>
                    <h4 class="h5 fs-16 fw-500 text-capitalize mb-1">
                        {{ Auth::user()->name }}
                    </h4>
                    @if(Auth::user()->phone != null)
                        <div class="text-truncate opacity-60 fs-14">{{ Auth::user()->phone }}</div>
                    @else
                        <div class="text-truncate opacity-60 fs-14">{{ Auth::user()->email }}</div>
                    @endif
                </div>
            </div>
    </div>

    <div class="card manage-profile  border-0 shadow-none">
        <div class="card-body">
            <div class="alert alert-info mb-4" role="alert">
                <p class="mb-0 fs-12">If you have any complaints or suggestions regarding our services, please feel free to share them with the <strong>Management Team</strong>. Your feedback is valuable in helping us improve and provide you with a better experience.</p>
            </div>
            <div class="form-group">
                <label class="col-form-label">Type <span class="text-danger">*</span></label>
                <div class="">
                    <select id="type" class="form-control" required>
                        <option value="suggestion" selected>Suggestion</option>
                        <option value="complaint">Complaint</option>
                    </select>
                </div>
                <span class="text-danger fs-12" id="type-error"></span>
            </div>
            <div class="form-group">
                <label class="col-form-label">Message <span class="text-danger">*</span></label>
                <div class="">
                    <textarea id="content" class="form-control" placeholder="Please share your suggestion with us. We value your input and are always looking for ways to improve our services." rows="5" required></textarea>
                </div>
                <span class="text-danger fs-12" id="content-error"></span>
            </div>

            <div class="form-group mb-0 text-right">
                <button id="submitContent" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            const typeSelect = $('#type');
            const contentTextarea = $('#content');

            function changePlaceholder() {
                const selectedType = typeSelect.val();
                if (selectedType === 'complaint') {
                    contentTextarea.attr('placeholder', 'Please describe your complaint in detail, including any relevant information that can help us address the issue effectively.');
                } else if (selectedType === 'suggestion') {
                    contentTextarea.attr('placeholder', 'Please share your suggestion with us. We value your input and are always looking for ways to improve our services.');
                } else {
                    contentTextarea.attr('placeholder', 'Your Complaint or Suggestion');
                }
            }

            typeSelect.change(changePlaceholder);
            changePlaceholder(); // Initialize placeholder on page load

            $('#submitContent').click(function(){
                const content = contentTextarea.val().trim();
                if(content.length < 10 || content === '') {
                    $('#content-error').text('Please share your complaint or suggestion in detail');
                    contentTextarea.focus().addClass('is-invalid');
                    return;
                } else {
                    contentTextarea.removeClass('is-invalid');
                    $('#content-error').text('');
                }

            });
        });
    </script>
@endsection
