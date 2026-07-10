@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <h5>Create Campaign</h5>
        </div>
        @include('backend.campaigns.fields')
    </div>
</div>
@endsection

@section('script')
    <script>
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

                if($('#description').val() === '') {
                    $('#description_error').text('Description is required');
                    isValid = false;
                } else {
                    $('#description_error').text('');
                }

                if($('#thumbnail').val() === '') {
                    $('#thumbnail_error').text('Thumbnail is required');
                    isValid = false;
                } else {
                    $('#thumbnail_error').text('');
                }

                if($('#status').val() === '') {
                    $('#status_error').text('Status is required');
                    isValid = false;
                } else {
                    $('#status_error').text('');
                }

                if($('#start_date').val() !== '' && $('#end_date').val() !== '') {
                    let startDate = new Date($('#start_date').val());
                    let endDate = new Date($('#end_date').val());
                    if(startDate > endDate) {
                        $('#end_date_error').text('End date must be after or equal start date');
                        isValid = false;
                    } else {
                        $('#end_date_error').text('');
                    }
                }

                if(isValid){
                    $('#campaign-form').submit();
                }
            });

            $('#clear-btn').click(function() {
                $('.note-editable').html('');
                $('#title').val('');
                $('#slug').val('');
                $('#category').val('').trigger('change');
                $('#description').val('');
                $('#start_date').val('');
                $('#end_date').val('');
                $('#thumbnail').val('');
                $('#status').val('draft').trigger('change');
                $('.file-amount').text('Choose File');
                $('.file-preview').html('');
                $('.error').text('');
            });
        });
    </script>
@endsection