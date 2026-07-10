@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <h5>Create Notice</h5>
        </div>
        @include('backend.notices.fields')
    </div>
</div>
@endsection

@section('script')
    <script>
        $('#status').on('change', function() {
            if ($(this).val() === 'scheduled') {
                $('#publish_at_group').show();
                $('#publish_at').prop('required', true);
            } else {
                $('#publish_at_group').hide();
                $('#publish_at').prop('required', false);
            }
        });

        $('#title').on('input', function() {
            let slug = $(this).val().toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^\w\-]+/g, '')
                    .replace(/\-\-+/g, '-')
                    .replace(/^-+/, '')
                    .replace(/-+$/, '');
            $('#slug').val(slug);
        });

        $(document).ready(function() {
            $('#create-btn').click(function(e) {
                e.preventDefault();
                let isValid = true;

                if($('#title').val() === '') {
                    $('#title_error').text('Title is required');
                    isValid = false;
                } else {
                    $('#title_error').text('');
                }

                if($('#category').val() === '') {
                    $('#category_error').text('Category is required');
                    isValid = false;
                } else {
                    $('#category_error').text('');
                }

                if($('#content').val() === '') {
                    $('#contect_error').text('Content is required');
                    isValid = false;
                } else {
                    $('#contect_error').text('');
                }

                if($('#visibility').val() === '') {
                    $('#visibility_error').text('Visibility is required');
                    isValid = false;
                } else {
                    $('#visibility_error').text('');
                }

                if($('#status').val() === '') {
                    $('#status_error').text('Status is required');
                    isValid = false;
                } else {
                    $('#status_error').text('');
                }

                if($('#publish_at').val() === '' && $('#status').val() === 'scheduled') {
                    $('#publish_at_error').text('Publish date is required');
                    isValid = false;
                } else {
                    $('#publish_at_error').text('');
                }

                if(isValid){
                    $('#notice-form').submit();
                }
            });

            $('#clear-btn').click(function() {
                $('.note-editable').html('');
                $('#title').val('');
                $('#content').val('');
                $('#visibility').val('both');
                $('#status').val('draft');
                $('#category').val('').refresh();
                $('#publish_at').val('');
                $('#publish_at_group').hide();
                $('.error').text('');
            });
        });
    </script>
@endsection