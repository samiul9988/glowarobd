<script>
    $(document).ready(function() {
        $('#name').on('input', function() {
            let name = $(this).val().trim();
            if (name) {
                $('#slug').val(name.toLowerCase().replace(/[^a-z0-9]+/g, '-'));
            } else {
                $('#slug').val('');
            }
        });

        $('#create-btn').click(function(e) {
            e.preventDefault();
            let isValid = true;
            if ($('#name').val() === '') {
                $('#name_error').text('Name is required');
                isValid = false;
            } else {
                $('#name_error').text('');
            }

            if ($('#thumbnail').val() === '') {
                $('#thumbnail_error').text('Thumbnail is required');
                isValid = false;
            } else {
                $('#thumbnail_error').text('');
            }

            if (isValid) {
                $('#playlist-form').submit();
            }
        });

        $('#clear-btn').click(function() {
            $('#playlist-form')[0].reset();
            $('.error').text('');
        });
    });
</script>
