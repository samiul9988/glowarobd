<script type="text/javascript">
    $(document).ready(function() {
        let template = @json(@$template);
        let templateType = `{{ old('type', @$template->type?->value) }}`;
        let hasTemplate = template !== null && template !== undefined && template !== '';

        loadSampleData(templateType, false);
        showPreview(`{!! old('content', @$template->content) !!}`);

        $('#view_sample_code').click(function() {
            $('#copy_code').html('<i class="las la-copy"></i>');

            if ($('#sample_content').text().trim().length > 0) {
                $('#copy_code').show();
            } else {
                $('#copy_code').hide();
            }
            $('#sample_view_code').modal('show');
        });

        async function copyTextToClipboard(textToCopy) {
            const $tempTextarea = $('<textarea>');
            $('body').append($tempTextarea);

            $tempTextarea.val(textToCopy).select();

            console.log('Copying content to clipboard:', textToCopy);
            try {
                const successful = document.execCommand('copy');
                $tempTextarea.remove();
                if (!successful) {
                    AIZ.plugins.notify('error', 'Failed to copy');
                }
                AIZ.plugins.notify('success', 'Content copied to clipboard');
            } catch (err) {
                $tempTextarea.remove();
                AIZ.plugins.notify('error', 'Failed to copy');
                console.error('Copy failed:', err);
            }
        }

        $('#copy_code').click(async function() {
            const htmlToCopy = $('#sample_content').html();
            const textToCopy = htmlToCopy && htmlToCopy.length > 0 ? htmlToCopy : $('#sample_content').text();
            if (!textToCopy || textToCopy.length === 0) {
                AIZ.plugins.notify('danger', 'No content to copy');
                return;
            }
            await copyTextToClipboard(textToCopy);
        });

        $('#type').on('change changed.bs.select', function() {
            let type = $(this).val().trim();
            loadSampleData(type, true).then(function() {
                showPreview($('#content').summernote('code'));
            });
        });

        async function loadSampleData(type, shouldUpdateContent = false) {
            if (type.length === 0) {
                $('#sample_content').html('');
                $('#short_code_variables').html('');
                $('#view_sample_code').hide();
                if (shouldUpdateContent || !hasTemplate) {
                    $('#content').summernote('code', $('#sample_content').html());
                }
                return;
            }
            switch (type) {
                case 'product_sticker':
                    $('#sample_content').html(`@include('backend.templates.defaults.product_sticker')`);
                    $('#short_code_variables').html(`@include('backend.templates.defaults.placeholders.product_sticker')`);
                    break;
                case 'id_card':
                    $('#sample_content').html(`@include('backend.templates.defaults.id_card')`);
                    $('#short_code_variables').html(`@include('backend.templates.defaults.placeholders.id_card')`);
                    break;
                case 'appointment-letter':
                    $('#sample_content').html(`@include('backend.templates.defaults.appointment_letter')`);
                    $('#short_code_variables').html(`@include('backend.templates.defaults.placeholders.appointment_letter')`);
                    break;
                case 'joining-letter':
                    $('#sample_content').html(`@include('backend.templates.defaults.joining_letter')`);
                    $('#short_code_variables').html(`@include('backend.templates.defaults.placeholders.joining_letter')`);
                    break;
                case 'increment-letter':
                    $('#sample_content').html(`@include('backend.templates.defaults.increment_letter')`);
                    $('#short_code_variables').html(`@include('backend.templates.defaults.placeholders.increment_letter')`);
                    break;
                case 'promotion-letter':
                    $('#sample_content').html(`@include('backend.templates.defaults.promotion_letter')`);
                    $('#short_code_variables').html(`@include('backend.templates.defaults.placeholders.promotion_letter')`);
                    break;
                case 'noc':
                    $('#sample_content').html(`@include('backend.templates.defaults.noc')`);
                    $('#short_code_variables').html(`@include('backend.templates.defaults.placeholders.noc')`);
                    break;
                default:
                    $('#sample_content').html('');
                    $('#short_code_variables').html('');
            }
            if (shouldUpdateContent || !hasTemplate) {
                console.log('Loading sample content based on selected type');
                $('#content').summernote('code', $('#sample_content').html());
            }
            $('#view_sample_code').show();
        }

        let previewTimeout = null;

        $('#content').on('summernote.change', function(we, contents) {
            showPreview(contents);
        });

        function showPreview(contents) {
            let preview = contents;
            let type = $('#type').val().trim();

            if (type === 'product_sticker') {
                preview = preview.replace(/\[\[supplier_name\]\]/g, 'AB Supplier');
                preview = preview.replace(/\[\[supplier_address\]\]/g, '123 Supplier Street, City, Country');
                preview = preview.replace(/\[\[product_name\]\]/g, 'Sample Product');
                preview = preview.replace(/\[\[product_price\]\]/g, '999.99');
                preview = preview.replace(/\[\[barcode\]\]/g,
                    '<img src="https://barcode.tec-it.com/barcode.ashx?data=123456789012&translate-esc=on&imagetype=Jpg" alt="Barcode" style="width: 100px; height: 30px;">'
                );
                preview = preview.replace(/\[\[po_number\]\]/g, 'PO1234565646548');
                preview = preview.replace(/\[\[exp_date\]\]/g, '2025-12-31');
            } else if (type === 'appointment-letter' || type === 'joining-letter' || type ===
                'increment-letter' || type === 'promotion-letter' || type === 'resignation-letter' || type ===
                'noc') {
                preview =
                    '<div style="text-align: center; padding: 20px;">No preview available for this template type</div>';
            }

            $('#preview').html(preview);
        }


        $('#create-btn').on('click', function(e) {
            e.preventDefault();
            let name = $('#name').val().trim();
            let type = $('#type').val().trim();
            let status = $('#status').val();
            let content = $('#content').val();

            if (name.length === 0 || type.length === 0 || content.length === 0) {
                AIZ.plugins.notify('danger', 'Please fill all required fields');
                return;
            }

            $('#create-template-form').submit();
        });
    });
</script>
