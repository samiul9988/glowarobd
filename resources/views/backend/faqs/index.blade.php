@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="h3">{{ ('All Faqs') }}</h1>
            </div>

            <div class="col text-right">
                <a href="javascript:;" class="btn btn-info" data-toggle="modal" id="addFaq">
                    <span class="btn-text-inner"><i class="fas fa-plus"></i> Add New Faqs</span>
                </a>
            </div>

        </div>
    </div>

    <div class="card">
        <form>
            @csrf

            <div class="card-header row gutters-5">

                <div class="col">
                    <h5 class="mb-0 h6">{{ ('Faqs') }}</h5>
                </div>

                {{-- searching --}}
                <div class="col-md-8">
                    <div class="form-group mb-0 d-flex align-items-center justify-content-between">
                        <input type="text" class="form-control me-2" id="search" name="search"
                            @isset($search) value="{{ $search }}" @endisset
                            placeholder="{{ ('search by question or answer') }}" style="width: 75%;">

                        <button type="submit"
                            class="btn btn-info flex items-center justify-center px-4 py-2 text-lg font-medium text-white rounded-lg hover:bg-info-600 focus:ring-4 focus:ring-info-300 dark:bg-info-600 dark:hover:bg-info-700 focus:outline-none dark:focus:ring-info-800">
                            Search
                        </button>
                    </div>
                </div>

            </div>

            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ ('Question') }}</th>
                            <th>{{ ('Answer') }}</th>
                            <th>{{ ('Options') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($faqs as $faq)
                            <tr>
                                <td>
                                    {{ $faq->question }}
                                </td>

                                <td>
                                    {{ $faq->answer }}
                                </td>

                                <td class="">
                                    <!-- Edit -->
                                    <a href="javascript:;" class="btn btn-soft-info btn-icon btn-circle btn-sm"
                                        data-id="{{ $faq->id }}" data-question="{{ $faq->question }}"
                                        data-answer="{{ $faq->answer }}" title="{{ ('Edit') }}" id="editbtn">
                                        <i class="las la-edit"></i>
                                    </a>

                                    <!-- Delete -->
                                    <a href="javascript:;"
                                        class="btn btn-soft-danger btn-icon btn-circle btn-sm faq-delete-confirm"
                                        data-id="{{ $faq->id }}" title="{{ ('Delete') }}"
                                        data-toggle="modal" data-target="#delete-modal">
                                        <i class="las la-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- <div class="aiz-pagination">
                    {{ $faqs->appends(request()->input())->links() }}
                </div> --}}

            </div>

        </form>
    </div>
@endsection

{{-- modal --}}
@section('modal')
    <!-- delete Modal -->
    @include('modals.delete_modal')

    {{-- create modal --}}
    <div class="modal fade" id="addFaqModal" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="faqCreateTitle">Create Faqs</h5>
                </div>

                <div class="modal-body">
                    <div id="create_faq_success_message" class="alert alert-success d-none"></div>
                    <form action="javascript:;" id="createFaqForm" enctype="multipart/form-data" method="POST">
                        @csrf

                        <div class="col-md-12">
                            <label for="name">Question <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="question" name="question" placeholder="Enter Question" rows="4"></textarea>
                            <span class="text-danger d-none question_error"></span>
                        </div>

                        <div class="col-md-12 mt-4">
                            <label for="answer">Answer <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="answer" name="answer" placeholder="Enter Answer" rows="4"></textarea>
                            <span class="text-danger d-none answer_error"></span>
                        </div>

                        <div class="col-md-12 mt-3">
                            <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-sm btn-primary" id="createFaqSubmitRequest">Save</button>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>

    {{-- Edit modal --}}
    <div class="modal fade" id="edit_faq_modal" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Faqs</h5>
                </div>

                <div class="modal-body">
                    <!-- Success message container -->
                    <div id="edit_faq_success_message" class="alert alert-success d-none"></div>
                    <form action="javascript:;" id="editFaqForm" enctype="multipart/form-data" method="POST">
                        @csrf
                        <input type="hidden" id="edit_faq_id">

                        <div class="col-md-12">
                            <label for="name">Question <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_question" name="question" placeholder="Enter Question" rows="4"></textarea>
                            <span class="text-danger d-none question_error"></span>
                        </div>

                        <div class="col-md-12 mt-4">
                            <label for="answer">Answer <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_answer" name="answer" placeholder="Enter Answer" rows="4"></textarea>
                            <span class="text-danger d-none answer_error"></span>
                        </div>

                        <div class="col-md-12 mt-3">
                            <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-sm btn-primary" id="updateFaqBtn">Save</button>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>
@endsection




@section('script')
    <script>
        $(document).ready(function() {

            //create modal er value k dhorlam
            $('#addFaq').on('click', function() {
                // console.log('rtfyry6r');
                $('#createFaqForm').trigger('reset');
                $('#createFaqForm').attr('action', `{{ route('faqs.store') }}`);
                $('#faqCreateTitle').html("Create Faqs");
                $('#createFaqSubmitRequest').html("Save");
                $('#addFaqModal').modal('show');
            });

            //create modal er ajax submit form
            $(document).on('submit', '#createFaqForm', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $('#createFaqSubmitRequest').prop('disabled', true).text('Submitting...');
                $.ajax({
                    type: "POST",
                    url: $(this).attr('action'),
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response['status'] == 'success') {
                            $('#addFaqModal').modal('hide');
                            showAlert('success', response['message'], window.location.href);
                        } else if (response.status === 'error') {
                            for (let key in response.errors) {
                                if (response.errors.hasOwnProperty(key)) {
                                    let errorElement = $(`.${key}_error`);
                                    errorElement.removeClass('d-none').html(''); // Clear previous errors
                                    response.errors[key].forEach(error => {
                                        errorElement.append(error + '<br>');
                                    });
                                }
                            }
                        } else {
                            $.each(response['errors'], function(key, value) {
                                $('.error_' + key).html(value);
                            });
                        }
                        $('#createFaqSubmitRequest').prop('disabled', false).text('Save');
                    },
                    error: function(xhr) {
                        $('#createFaqSubmitRequest').prop('disabled', false).text('Save');
                        console.log(xhr.responseText);
                    }
                });
            });

            //edit modal er value k dhorlam
            $(document).on('click', '#editbtn', function() {
                var id = $(this).data('id');
                var question = $(this).data('question');
                var answer = $(this).data('answer');

                $('#edit_faq_id').val(id); // Store the FAQ ID in a hidden input field
                $('#edit_question').val(question); // Populate the question field
                $('#edit_answer').val(answer);

                $('#edit_faq_modal').modal('show');
            });

            //edit modal er ajax submit form
            $(document).on('submit', '#editFaqForm', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                var faqId = $('#edit_faq_id').val(); // Get the FAQ ID
                var url = `{{ route('faqs.update', ':id') }}`.replace(':id', faqId);

                $('#updateFaqBtn').prop('disabled', true).text('Updating...');
                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response['status'] == 'success') {
                            $('#edit_faq_modal').modal('hide');
                            showAlert('success', response['message'], window.location.href);
                        } else if (response.status === 'error') {
                            for (let key in response.errors) {
                                if (response.errors.hasOwnProperty(key)) {
                                    let errorElement = $(`.${key}_error`);
                                    errorElement.removeClass('d-none').html('');
                                    response.errors[key].forEach(error => {
                                        errorElement.append(error + '<br>');
                                    });
                                }
                            }
                        } else {
                            $.each(response['errors'], function(key, value) {
                                $('.error_' + key).html(value);
                            });
                        }
                        $('#updateFaqBtn').prop('disabled', false).text('Save');
                    },
                    error: function(xhr) {
                        $('#updateFaqBtn').prop('disabled', false).text('Save');
                        console.log(xhr.responseText);
                    }
                });
            });

            //delete modal er value k dhorlam
            $(document).on('click', '.faq-delete-confirm', function() {
                var faqId = $(this).data('id'); // Get the ID of the FAQ to be deleted
                $('#delete-link').data('id', faqId); // Set ID in modal delete button
            });

            //delete modal er ajax submit form
            $(document).on('click', '#delete-link', function(e) {
                e.preventDefault();
                var faqId = $(this).data('id'); // Get ID from modal delete button

                $.ajax({
                    type: 'POST',
                    url: "{{ route('faqs.destroy') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: faqId
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#delete-modal').modal('hide');
                            location.reload();
                        }
                    }
                });
            });

        });
    </script>
@endsection
