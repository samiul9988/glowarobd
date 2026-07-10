@php
    $link_type = $type ?? 'custom';
    $link_item = $item ?? null;
@endphp
<div class="row link-selector">
    {{-- Link Type --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="link_type">Link Type <span class="text-danger">*</span></label>
            <select class="form-control link_type aiz-selectpicker" name="link_type" required>
                <option value="">Select a link type</option>
                <option value="product" {{ $link_type == 'product' ? 'selected' : '' }}>Product</option>
                <option value="category" {{ $link_type == 'category' ? 'selected' : '' }}>Category</option>
                <option value="brand" {{ $link_type == 'brand' ? 'selected' : '' }}>Brand</option>
                <option value="custom" {{ $link_type == 'custom' ? 'selected' : '' }}>Custom</option>
            </select>
        </div>
    </div>

    {{-- Link Item --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="link_item">Link Item <span class="text-danger">*</span></label>
            <div class="link_item_section"  style="display: none;">
                <select class="form-control aiz-selectpicker" data-live-search="true" data-selected-item="{{ $link_item }}" id="link_item">
                    <option value="">Loading ...</option>
                </select>
            </div>
            <div class="custome_link_section" style="display: block;">
                <input type="text" class="form-control" placeholder="https://example.com" name="link_item" id="custom_link" value="{{ $link_item }}">
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        function toggleLinkItemSection() {
            var selectedType = $('input[name="link_type"]').val();
            if (selectedType === 'custom') {
                $('.link_item_section').hide();
                $('.custome_link_section').show();
            } else {
                $('.link_item_section').show();
                $('.custome_link_section').hide();
                loadLinkItems(selectedType);
            }
        }

        function loadLinkItems(type) {
            var selectedItem = $('#link_item').data('selected-item');
            $.ajax({
                url: '/api/v3/link-items',
                type: 'GET',
                data: { type: type },
                success: function(response) {
                    var options = '<option value="">Select an item</option>';
                    response.forEach(function(item) {
                        var selected = item.id == selectedItem ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                    });
                    $('#link_item').html(options);
                    $('#link_item').selectpicker('refresh');
                },
                error: function() {
                    console.log('Failed to load link items. Please try again.');
                }
            });
        }

        // Initial toggle based on the pre-selected value
        toggleLinkItemSection();

        // Event listener for link type change
        $('input[name="link_type"]').change(function() {
            toggleLinkItemSection();
        });
    });
</script>
