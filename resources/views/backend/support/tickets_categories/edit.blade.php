@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="d-flex justify-content-start align-items-center">
                <a href="{{ route('ticket_categories.index') }}" class="mr-2 btn btn-soft-secondary btn-icon btn-circle btn-sm">
                    <i class="las la-long-arrow-alt-left"></i>
                </a>
                <h5 class="mb-0">Edit Ticket Category</h5>
            </div>
        </div>
        <form id="ticket-category-form" action="{{ route('ticket_categories.update', $category->id) }}" method="post">
            @method('PUT')
            @include('backend.support.tickets_categories.fields', $category)
        </form>
        <div class="text-right">
            <button type="button" class="btn btn-secondary" id="clear-btn">Clear</button>
            <button type="submit" form="ticket-category-form" class="btn btn-primary" id="update-btn">Update</button>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#create-btn').click(function(e) {
            e.preventDefault();
            let isValid = true;
            if($('#name').val() === '') {
                $('#name_error').text('Name is required');
                isValid = false;
            } else {
                $('#name_error').text('');
            }

            if(isValid){
                $('#ticket-category-form').submit();
            }
        });

        $('#name').on('input', function() {
            let slug = $(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-');
            $('#slug').val(slug);
        });

        $('#clear-btn').click(function() {
            $('#ticket-category-form')[0].reset();
            $('.error').text('');
        });
    });
</script>
@endsection
