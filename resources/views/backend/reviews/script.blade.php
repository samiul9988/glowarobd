<script>
    let selectedProduct = '{{ old('product', @$review->product_id) }}';
    let selectedCustomer = '{{ old('customer', @$review->user_id) }}';

    // Initial loads
    getProducts();
    getCustomers();
    // Generic debounce function
    function debounce(func, delay) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), delay);
        };
    }

    // Fetch products
    async function getProducts(search = '') {
        try {
            const params = new URLSearchParams({
                search,
                selected: selectedProduct,
            });
            const response = await fetch(`{{ route('reviews.fetch_products') }}?${params}`);
            if (!response.ok) throw new Error('Server Error');

            const data = await response.json();
            $('#product').empty().append('<option value="">Select a product</option>');
            $.each(data, (id, name) => {
                $('#product').append(
                    `<option value="${id}" ${selectedProduct == id ? 'selected' : ''}>${name}</option>`);
            });
            $('#product').selectpicker('refresh');
        } catch (error) {
            console.error('Error fetching products:', error);
        }
    }

    // Fetch customers
    async function getCustomers(search = '') {
        try {
            const params = new URLSearchParams({
                search,
                selected: selectedCustomer,
            });
            const response = await fetch(`{{ route('reviews.fetch_customers') }}?${params}`);
            if (!response.ok) throw new Error('Server Error');

            const data = await response.json();
            $('#customer').empty().append('<option value="">Select a customer</option>');
            $.each(data, (id, name) => {
                $('#customer').append(
                    `<option value="${id}" ${selectedCustomer == id ? 'selected' : ''}>${name}</option>`
                    );
            });
            $('#customer').selectpicker('refresh');
        } catch (error) {
            console.error('Error fetching customers:', error);
        }
    }

    // Attach individual listeners to each selectpicker search box
    $(document).on('shown.bs.select', function(e) {
        const $select = $(e.target);
        const selectId = $select.attr('id');

        setTimeout(() => {
            const $searchInput = $select.closest('.bootstrap-select').find('.bs-searchbox input');

            if (selectId === 'product') {
                $searchInput.off('input').on('input', debounce(function() {
                    getProducts(this.value);
                }, 300));
            } else if (selectId === 'customer') {
                $searchInput.off('input').on('input', debounce(function() {
                    getCustomers(this.value);
                }, 300));
            }
        }, 10); // slight delay to ensure DOM is ready
    });

    $(document).ready(function() {
        let reviewType = '{{ old('type', @$review->review_type) }}';
        toggleReviewSections(reviewType);

        $('#create-btn').click(function(e) {
            e.preventDefault();
            let isValid = true;
            if ($('#type').val() === '') {
                $('#type_error').text('Type is required');
                isValid = false;
            } else {
                $('#type_error').text('');
            }

            let type = $('#type').val();

            if (type === 'text') {
                if ($('#rating').val() === '') {
                    $('#rating_error').text('Rating is required');
                    isValid = false;
                } else {
                    $('#rating_error').text('');
                }
                if ($('#comment').val() === '') {
                    $('#comment_error').text('Comment is required');
                    isValid = false;
                } else {
                    $('#comment_error').text('');
                }
            } else if (type === 'image') {
                let photos = $('input[name="photos"]').val();
                if (photos.length === 0) {
                    $('#photos_error').text('Image is required');
                    isValid = false;
                } else {
                    $('#photos_error').text('');
                }
            } else if (type === 'video') {
                let videoUrls = $('input[name="videos[]"]').map(function () {
                    return $(this).val().trim();
                }).get().filter(url => url !== '');

                if (videoUrls.length === 0) {
                    $('#video_error').text('Video is required');
                    isValid = false;
                } else {
                    $('#video_error').text('');
                }
            }

            let reviewDate = $('#review_date').val();
            if (reviewDate) {
                let today = new Date().toISOString().split('T')[0];
                if (reviewDate > today) {
                    $('#review_date_error').text('Review date cannot be in the future');
                    isValid = false;
                } else {
                    $('#review_date_error').text('');
                }
            }

            if (isValid) {
                $('#review-form').submit();
            }
        });

        $('#clear-btn').click(function() {
            $('#review-form')[0].reset();
            $('.error').text('');
        });

        $('#type').on('change', function() {
            let type = $(this).val();
            if (type === 'image' || type === 'video') {
                $('.responsive').removeClass('col-md-4').addClass('col-md-6');
            } else {
                $('.responsive').removeClass('col-md-6').addClass('col-md-4');
            }
            toggleReviewSections(type);
        });

        function toggleReviewSections(type) {
            // let type = $('#type').val();
            $('.review-section').hide();
            if (type === 'text') {
                $('.text-review-section').show();
            } else if (type === 'image') {
                $('.image-review-section').show();
            } else if (type === 'video') {
                $('.video-review-section').show();
            } else {
                $('.rating-section').show();
                $('.text-review-section').show();
            }
        }
    });
</script>
