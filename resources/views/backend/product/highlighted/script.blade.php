@include('backend.components.linkable-script', [
    'type' => @$highlightedItem->linkable_type,
    'linkable' => @$highlightedItem->linkable_id,
    'custom' => @$highlightedItem->custom_link,
])
<script>
    $('#add-more-highlights').click(function() {
        let newHighlight = `<div class="row gutters-5">
                    <div class="col">
                        <div class="form-group">
                            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary">Browse</div>
                                </div>
                                <div class="form-control file-amount">Choose Files</div>
                                <input type="hidden" name="highlight_icons[]" class="selected-files" value="">
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Label text" name="highlight_labels[]" value="">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger remove-highlight">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                </div>`;

        let highlightCount = $('.highlights-target .row').length;
        if(highlightCount < 4) {
            $('.highlights-target').append(newHighlight);
            highlightCount++;
        }
        if (highlightCount == 4) {
            $('#add-more-highlights').prop('disabled', true);
            return;
        } else {
            $('#add-more-highlights').prop('disabled', false);
        }
        // AIZ.plugins.bootstrapSelect('refresh');
    });

    $('.highlights-target').on('click', '.remove-highlight', function() {
        $(this).closest('.row').remove();
        let highlightCount = $('.highlights-target .row').length;
        if (highlightCount < 4) {
            $('#add-more-highlights').prop('disabled', false);
        }
    });

    $(document).ready(function() {
        $('#create-btn').click(async function(e) {
            e.preventDefault();
            let isValid = true;
            if ($('#title').val() === '') {
                $('#title_error').text('Title is required');
                isValid = false;
            } else {
                $('#title_error').text('');
            }

            if ($('#subtitle').val() === '') {
                $('#subtitle_error').text('Subtitle is required');
                isValid = false;
            } else {
                $('#subtitle_error').text('');
            }

            if ($('#description').val() === '') {
                $('#description_error').text('Description is required');
                isValid = false;
            } else {
                $('#description_error').text('');
            }

            let banner = $('input[name="banner"]').val();
            if (banner.length === 0) {
                $('#banner_error').text('Banner image is required');
                isValid = false;
            } else {
                $('#banner_error').text('');
            }

            isValid = await validateLinkable() && isValid;

            let highlightCount = $('.highlights-target .row').length;
            if (highlightCount === 0) {
                $('#highlights_error').text('Highlights are required');
                isValid = false;
            } else {
                $('#highlights_error').text('');
            }

            if (isValid) {
                $('#highlight-product-form').submit();
            }
        });

        $('#clear-btn').click(function() {
            $('#highlight-product-form')[0].reset();
            $('.error').text('');
            AIZ.plugins.bootstrapSelect('refresh');
        });
    });
</script>
