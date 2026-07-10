<script>

    async function getVideoSource(fileId) {
        try {
            let response = await fetch(`/aiz-uploader/get_file_by_id?id=${fileId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            let data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching video source');
            return null;
        }
    }

    $(document).ready(function () {
        const $videoInput = $('#video');
        const $videoElement = $('#video_preview');
        const $loader = $('#video_loader');
        const $titleElement = $('#video_title');
        const $urlElement = $('#video_url');

        // Show loader while video loads
        $videoElement.on('loadstart', function () {
            $loader.show();
            $videoElement.hide();
        });

        // Hide loader once video is ready
        $videoElement.on('loadeddata', function () {
            $loader.hide();
            $videoElement.fadeIn(300);
        });

        const observer = new MutationObserver(async function () {
            const fileId = $videoInput.val();
            if (fileId) {
                // Show loader while updating video
                $loader.show();
                $videoElement.hide();

                // Set new video source (adjust your base path if needed)
                const videoData = await getVideoSource(fileId);

                if (!videoData) {
                    showAlert('error', 'Failed to load video data');
                    $loader.hide();
                    $titleElement.text('No video selected');
                    $urlElement.text('No URL Available');
                    return;
                }
                $videoElement.attr('src', videoData.full_url || '')[0].load();

                // Update title and URL dynamically
                const videoTitle = videoData.file_original_name + '.' + videoData.extension;
                $titleElement.text(videoData.file_original_name ? videoTitle : 'Untitled Video');
                $urlElement.text(videoData.full_url || 'No URL Available');
            }
        });

        observer.observe($videoInput[0], { attributes: true, attributeFilter: ['value'] });
    });

    // Field Handling
    // let selectedProducts = @json($selectedProducts ?? []);

    // Initial loads
    // getProducts();
    // Generic debounce function
    function debounce(func, delay) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), delay);
        };
    }

    // $(document).on('shown.bs.select', function(e) {
    //     const $select = $(e.target);
    //     const selectId = $select.attr('id');
    //     setTimeout(() => {
    //         const $searchInput = $select.closest('.bootstrap-select').find('.bs-searchbox input');

    //         if (selectId === 'products') {
    //             $searchInput.off('input').on('input', debounce(function() {
    //                 getProducts(this.value);
    //             }, 300));
    //         }
    //     }, 10); // slight delay to ensure DOM is ready
    // });

    // Fetch products
    async function getProducts(search = '') {
        try {
            const params = new URLSearchParams({
                search,
                selected: selectedProducts,
            });
            const response = await fetch(`{{ route('reviews.fetch_products') }}?${params}`);
            if (!response.ok) throw new Error('Server Error');

            const data = await response.json();
            $('#products').empty().append('<option value="" disabled>Select a product</option>');
            $.each(data, (id, name) => {
                id = parseInt(id);
                $('#products').append(
                    `<option value="${id}" ${selectedProducts.includes(id) ? 'selected' : ''}>${name}</option>`);
            });
            $('#products').selectpicker('refresh');
        } catch (error) {
            console.error('Error fetching products');
        }
    }

    function resetForm() {
        $('#title').val('');
        $('#slug').val('');
        $('#description').val('');
        $('#status').val('1');
        $('#type').val('default');
        $('#is_featured').prop('checked', false).val(0);
        $('#products').val('').selectpicker('refresh');
        $('#playlists').val('').selectpicker('refresh');
        $('#thumbnail').val('');
        $('#video').val('');
        AIZ.uploader.previewGenerate();
        $('.error').text('');
        $('#video_id').val('');
        $('#video_preview').attr('src', '');
        $('#video_title').text('No video selected');
        $('#video_url').text('No URL Available');
        // selectedProducts = [];
        // getProducts()
    }



    $(document).ready(function() {
        $('#title').on('input', function() {
            let title = $(this).val().trim();
            if (title) {
                $('#slug').val(title.toLowerCase().replace(/[^a-z0-9]+/g, '-'));
            } else {
                $('#slug').val('');
            }
        });

        function validateForm() {
            let isValid = true;
            if ($('#title').val() === '') {
                $('#title_error').text('Title is required');
                isValid = false;
            } else {
                $('#title_error').text('');
            }

            if ($('#playlists').val() === '') {
                $('#playlists_error').text('Category is required');
                isValid = false;
            } else {
                $('#playlists_error').text('');
            }

            if ($('#thumbnail').val() === '') {
                $('#thumbnail_error').text('Thumbnail is required');
                isValid = false;
            } else {
                $('#thumbnail_error').text('');
            }
            return isValid;
        }

        $('#create-btn').click(async function(e) {
            e.preventDefault();
            if (validateForm()) {
                $('#video-form').submit();
            }
        });
    });
</script>
