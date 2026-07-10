@php
    $linkableType = @$type ? class_basename(@$type) : 'Custom';
    $linkableId = @$linkable;
    $customLink = @$custom;
@endphp
<script>
    let selectedType = '{{ old('link_type', @$linkableType) ?? "custom" }}';
    let selectedItem = '{{ old('link_item', @$linkableId) }}';

    // Initial loads
    getItems(selectedType);

    // Fetch items
    async function getItems(type, search = '') {
        try {
            // const params = new URLSearchParams({
            //     search,
            //     selected: selectedItem,
            // });
            let url = '';
            if (type === 'product') {
                url = `{{ route('products.fetchAll') }}`;
            } else if (type === 'brand') {
                url = `{{ route('brands.fetchAll') }}`;
            } else if (type === 'category') {
                url = `{{ route('categories.fetchAll') }}`;
            }

            if (!url) return;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Server Error');

            const data = await response.json();
            $('#link_item').empty().append(`<option value="">Select a ${type}</option>`);
            $.each(data, (id, name) => {
                $('#link_item').append(
                    `<option value="${id}" ${selectedItem == id ? 'selected' : ''}>${name}</option>`);
            });
            $('#link_item').selectpicker('refresh');
        } catch (error) {
            console.error('Error fetching items:', error);
        }
    }

    // Generic debounce function
    function linkableDebounce(func, delay) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), delay);
        };
    }

    // Attach individual listeners to each selectpicker search box
    // $(document).on('shown.bs.select', function(e) {
    //     const $select = $(e.target);
    //     const selectId = $select.attr('id');

    //     setTimeout(() => {
    //         const $searchInput = $select.closest('.bootstrap-select').find('.bs-searchbox input');

    //         if (selectId === 'link_item') {
    //             $searchInput.off('input').on('input', linkableDebounce(function() {
    //                 getItems($('#link_type').val(), this.value);
    //             }, 300));
    //         }
    //     }, 10); // slight delay to ensure DOM is ready
    // });

    async function validateLinkable() {
        let linkableValid = true;
        if ($('#link_type').val() === '') {
            $('#link_type_error').text('Type is required');
            linkableValid = false;
        } else {
            $('#link_type_error').text('');
        }

        let type = $('#link_type').val();

        if (type === 'custom') {
            if ($('#custom_link').val() === '') {
                $('#custom_link_error').text('Custom link is required');
                linkableValid = false;
            } else {
                $('#custom_link_error').text('');
            }
        } else {
            if ($('#link_item').val() === '' || $('#link_item').val() === null) {
                $('#link_item_error').text('Please select a ' + type);
                linkableValid = false;
            } else {
                $('#link_item_error').text('');
            }
        }

        return linkableValid;
    }

    $(document).ready(function() {
        $('#link_type').on('change', function() {
            toggleLinkItemSection();
        });

        function toggleLinkItemSection() {
            var selectedType = $('#link_type').val();
            if (selectedType === 'custom') {
                $('#link_item').val('');
                $('.link_item_section').hide();
                $('.custome_link_section').show();
            } else {
                $('#custom_link').val('');
                $('.link_item_section').show();
                $('.custome_link_section').hide();
                getItems(selectedType);
            }
            AIZ.plugins.bootstrapSelect('refresh');
        }

        toggleLinkItemSection();

    });
</script>
