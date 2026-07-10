@extends('backend.layouts.app')
@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="d-flex align-items-center justify-content-between">
		<h1 class="h3">{{ ('Product Reviews')}}</h1>
        <a href="javascript:void(0)" onclick="reviewSettingsModalShow()" style="font-size:medium;"><i class="las la-cogs"></i> Review Settings</a>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h5 class="mb-md-0 h6">{{ ('All Reviews') }}</h5>
        </div>
        <div>
            <a href="{{ route('reviews.create') }}" class="btn btn-success btn-sm float-right">
                <i class="las la-plus"></i> {{ ('Create Review') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('reviews.index') }}" method="get">
            <div class="row gutters-5">
                <div class="col-lg-2 mb-2 mb-lg-0">
                    <div class="form-group mb-0">
                        <select class="form-control aiz-selectpicker" name="rating" id="rating">
                            <option value="" selected>{{ ('Filter by Rating')}}</option>
                            <option value="desc" @if(request('rating') === 'desc') selected @endif>{{ ('Rating (High > Low)')}}</option>
                            <option value="asc" @if(request('rating') === 'asc') selected @endif>{{ ('Rating (Low > High)')}}</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 mb-2 mb-lg-0">
                    <div class="form-group mb-0">
                        <select class="form-control aiz-selectpicker" name="type" id="type">
                            <option value="" selected>{{ ('Filter by Type')}}</option>
                            <option value="text" @if(request('type') === 'text') selected @endif>Text</option>
                            <option value="image" @if(request('type') === 'image') selected @endif>Image</option>
                            <option value="video" @if(request('type') === 'video') selected @endif>Video</option>
                            <option value="feedback" @if(request('type') === 'feedback') selected @endif>Feedback</option>
                            <option value="self" @if(request('type') === 'self') selected @endif>Self (Customer)</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-4 mb-2 mb-lg-0">
                    <div class="form-group mb-0">
                        <select class="form-control aiz-selectpicker" data-live-search="true" name="product" id="product">
                            <option value="">{{ ('Filter by products') }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 mb-2 mb-lg-0">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control" value="{{ request('date') }}" name="date" placeholder="{{ ('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>
                <div class="col-auto">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">{{ ('Filter') }}</button>
                    </div>
                </div>
            </div>
        </form>
        <hr>
        <table class="table aiz-table mb-0 table-striped" id="review-table">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>Product</th>
                    <th data-breakpoints="lg" class="text-center">Review Type</th>
                    <th data-breakpoints="lg" class="text-center">Customer/Name</th>
                    <th style="width: 100px;" class="text-center">Rating</th>
                    <th data-breakpoints="lg" style="width: 100px;" class="text-center">Published</th>
                    <th data-breakpoints="lg" style="width: 100px;" class="text-center">Featured</th>
                    <th data-breakpoints="lg" class="text-center">Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviews as $key => $review)
                    @php
                        $data = [];
                        $photos = array_filter(explode(',', $review->photos ?? '') ?? []);
                        $data['product_name'] = $review->product?->name ?? '';
                        $data['product_img'] = uploaded_asset($review->product?->thumbnail_img);
                        $data['user'] = $review->name ?? $review->user?->name ?? '';
                        $data['rating'] = $review->rating ?? 0;
                        $data['review_type'] = $review->review_type ?? 0;
                        $data['comment'] = $review->comment ?? '';
                        $data['photos'] =  array_map(function($photo) {
                            return uploaded_asset($photo);
                        }, $photos);
                        $data['videos'] = array_map(function($video) {
                            return [
                                'link' => get_yt_embed($video),
                                'thumbnail' => get_yt_thumb($video)
                            ];
                        }, $review->videos ?? []);
                        $data['created_at'] = date('d-m-Y h:i A', strtotime($review->created_at));

                        $rowTitle = '';
                        if($review->createdBy) {
                            $rowTitle .= 'Created by: ' . $review->createdBy->name . PHP_EOL;
                        }
                        if($review->updatedBy) {
                            $rowTitle .= 'Last updated by: ' . $review->updatedBy->name;
                        }
                    @endphp
                    <tr title="{{ $rowTitle }}">
                        <td>{{ ($key+1) + ($reviews->currentPage() - 1)*$reviews->perPage() }}</td>
                        <td>
                            @if($review->product)
                                <a title="{{ $review->product?->name }}" href="{{ to_frontend(route('product', $review->product?->slug)) }}" target="_blank" class="text-primary">{{ limit_text($review->product?->name) }}</a>
                            @else
                                <span class="text-muted">{{ ('No Product') }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ ucfirst($review->review_type) }}</td>
                        <td class="text-center">
                            @if(blank($review->user) && blank($review->name))
                                <span class="text-muted">{{ ('Annonymous') }}</span>
                            @else
                                <span class="d-block">{{ $review->name ?? $review->user->name ?? '' }}</span>
                                <span class="d-block">{{ $review->email ?? $review->user->email ?? ''}}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($review->rating)
                                @foreach (range(1, $review->rating) as $i)
                                    <i class="las la-star text-warning"></i>
                                @endforeach
                            @else
                                <span class="text-muted">{{ ('No Rating') }}</span>
                            @endif
                        </td>
                        {{-- <td>
                            @php
                                $reviewPhotos = [];
                                isset($review->photos) ? $reviewPhotos = explode(',', $review->photos) : [];
                            @endphp
                            @if(count($reviewPhotos) > 0)
                            <div class="lbt-gallery" class="row gutters-10">
                                @foreach($reviewPhotos as $photo)
                                <div class="lbt-box">
                                    <img
                                        class="img-fluid lazyload"
                                        src="{{ uploaded_asset($photo) }}"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                        width="50"
                                    >
                                </div>
                                @endforeach
                            </div>
                            @else
                            <span>No Image</span>
                            @endif
                        </td>
                        <td style="max-width: 365px;">{{ $review->comment }}</td> --}}
                        <td class="text-center">
                            <label class="aiz-switch aiz-switch-success mb-0">
                            <input onchange="update_published(this)" value="{{ $review->id }}" type="checkbox" <?php if($review->status == 1) echo "checked";?> >
                            <span class="slider round"></span></label>
                        </td>
                        <td class="text-center">
                            <label class="aiz-switch aiz-switch-success mb-0">
                            <input onchange="update_featured(this)" value="{{ $review->id }}" type="checkbox" <?php if($review->featured == 1) echo "checked";?> >
                            <span class="slider round"></span></label>
                        </td>
                        <td class="text-center">{{ date('d-m-Y h:i A', strtotime($review->created_at)) }}</td>
                        <td class="text-center">
                            <button class="btn btn-soft-primary btn-icon btn-circle btn-sm view-btn" data-review="{{ json_encode($data) }}" title="{{ ('View Details') }}">
                                <i class="las la-eye"></i>
                            </button>
                            @if($review->review_type !== 'default')
                                <a class="btn btn-soft-success btn-icon btn-circle btn-sm" href="{{ route('reviews.edit', $review->id) }}" title="{{ ('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                            @else
                                <button class="btn btn-soft-success btn-icon btn-circle btn-sm" disabled title="{{ ('Not Editable') }}" style="cursor: not-allowed; opacity: 0.5;">
                                    <i class="las la-edit"></i>
                                </button>
                            @endif
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('reviews.delete', $review->id) }}" title="{{ ('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $reviews->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection
@section('modal')
    @include('modals.delete_modal')
    @include('modals.bulk_delete_modal')

    <!-- // Review Settings Modal -->
    <div class="modal fade" id="reviewSettingsModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title h6">{{ ('Review Settings')}}</h5>
                        <button type="button" class="close" data-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Who can post reviews?')}}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="who_can_post_reviews">
                                <input type="radio" id="radio-nine" name="who_can_post_reviews" value="all_registered_customers" @if( @get_setting('who_can_post_reviews') == 'all_registered_customers') checked @endif/>
                                <label for="radio-nine">All Registered Customers</label><br />
                                <input type="radio" id="radio-ten" name="who_can_post_reviews" value="all_registered_buyers" @if( @get_setting('who_can_post_reviews') == 'all_registered_buyers') checked @endif/>
                                <label for="radio-ten">Only Real Registered Buyers</label><br />
                                <input type="radio" id="radio-eleven" name="who_can_post_reviews" value="everyone" @if( @get_setting('who_can_post_reviews') == 'everyone') checked @endif/>
                                <label for="radio-eleven">All Customers/Everyone</label><br />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Auto Approve Reviews?')}}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="auto_approved_reviews">
                                    <input type="checkbox" name="auto_approved_reviews" @if( @get_setting('auto_approved_reviews') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Image upload option?')}}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="reviews_image_upload">
                                    <input type="checkbox" name="reviews_image_upload" @if( @get_setting('reviews_image_upload') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Image upload only for registerd user?')}}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="reviews_image_upload_only_user">
                                    <input type="checkbox" name="reviews_image_upload_only_user" @if( @get_setting('reviews_image_upload_only_user') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Max number of image')}}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="reviews_max_image">
                                <input type="number" name="reviews_max_image" min="1" class="form-control" value="{{ @get_setting('reviews_max_image') < 1 ? 1 : @get_setting('reviews_max_image') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
                        <button type="submit" class="btn btn-primary">{{ ('Save Settings')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Review Details Modal --}}
    <div class="modal fade" id="review-details-modal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h6">{{ ('Review Details')}}</h5>
                    <button type="button" class="close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="review-details-content">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Close')}}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="videoModal">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <button type="button" class="close text-light" onclick="closeVideoModal()"></button>
            </div>
          <div class="modal-body p-0">
            <div class="embed-responsive embed-responsive-16by9">
              <iframe id="videoIframe" class="embed-responsive-item" src="" allowfullscreen></iframe>
            </div>
          </div>
        </div>
      </div>
    </div>

    <style>
        .yt-thumb {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .yt-thumb img {
            display: block;
            border-radius: 5px;
        }
        .yt-thumb .play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3rem;
            color: white;
            opacity: 0.8;
        }
        .yt-thumb:hover .play-icon {
            opacity: 1;
        }
    </style>
@endsection
@section('script')
    <script>
        let selectedProduct = `{{ request('product') }}`;
        getProducts();
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

        $('#review-table').on('click', '.view-btn', function() {
            const review = $(this).data('review');
            let content = ``;
            console.log(review, review.photos.length, review.videos.length);
            if(review.product_name.length > 0) {
                content += `<div class="row">
                        <div class="col-md-12 d-flex p-0">
                            <img id="review-image" src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="${review.product_img}" alt="{{ ('Product Image')}}" class="img-fluid mb-3 rounded lazyload" style="max-height: 70px; max-width: 100%;">
                            <span class="ml-2 h5">
                                ${review.product_name ?? ''}
                            </span>
                        </div>
                    </div>`;
            }

            if(review.review_type === 'text' || review.review_type === 'default') {
                content += `<div class="row p-2 rounded mt-2" style="background-color: #eaebec;">
                        <div class="col-md-12">
                            <div class="d-flex align-items-center">
                                <img src="{{ static_asset('assets/img/user.png') }}" alt="Customer" class="img-fluid rounded-circle" style="width: 50px; height: 50px;">
                                <div class="ml-2">
                                    <span class="d-block">${review.user}</span>`;
                                    if(review.rating && review.rating > 0) {
                                        content += `<span class="d-block">`;
                                        for (let i = 1; i <= review.rating; i++) {
                                            content += `<i class="las la-star text-warning"></i>`;
                                        }
                                        content += `</span>`;
                                    }
                    content += `<span class="d-block text-muted" style="font-size: 10px;">${review.created_at}</span>`;
                    content += `</div>
                            </div>
                            <div class="mt-2">
                                <span class="text-muted">
                                    ${review.comment}
                                </span>
                            </div>
                        </div>
                    </div>`;
            }

            if(review.photos.length > 0) {
                content += `<div class="row mt-2">
                        <div class="col-md-12 d-flex p-0">`;
                review.photos.forEach(photo => {
                    content += `<div class="mr-2">
                                    <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="${photo}" alt="{{ ('Review Image')}}" class="img-fluid rounded lazyload" style="max-height: 100px; max-width: 100px;">
                                </div>`;
                });
                content += `</div></div>`;
            }

            if((review.review_type === 'video' || review.review_type === 'default') && review.videos.length > 0) {
                content += `<div class="row mt-4">
                        <div class="col-md-12 d-flex p-0">`;
                review.videos.forEach(video => {
                    content += `<div class="yt-thumb mr-2" data-video="${video.link}" title="Play Video">
                                    <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="${video.thumbnail}" alt="{{ ('Review Video')}}" class="img-fluid rounded lazyload" style="max-height: 150px; max-width: 150px;">
                                    <span class="play-icon">&#9658;</span>
                                </div>`;
                });
                content += `</div></div>`;
            }

            $('#review-details-content').html(content);
            $('#review-details-modal').modal('show');
        });

        $('#review-details-content').on('click', '.yt-thumb', function() {
            var videoUrl = $(this).data('video') + "?autoplay=1";
            $('#videoIframe').attr('src', videoUrl);
            $('#videoModal').modal('show');
        });

        function closeVideoModal() {
            $('#videoIframe').attr('src', '');
            $('#videoModal').modal('hide');
        }
    </script>
    <script type="text/javascript">
        function update_published(el){
            var status = el.checked ? 1 : 0;
            $.ajax({
                url: '{{ route('reviews.published') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: el.value,
                    status: status
                },
                success: function(data) {
                    if(data == 1){
                        AIZ.plugins.notify('success', 'Status updated successfully');
                    }
                    else{
                        el.checked = !el.checked;
                        AIZ.plugins.notify('danger', 'Something went wrong');
                    }
                },
                error: function() {
                    el.checked = !el.checked;
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
        function update_featured(el){
            var status = el.checked ? 1 : 0;
            $.ajax({
                url: '{{ route('reviews.featured') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: el.value,
                    status: status
                },
                success: function(data) {
                    if(data == 1){
                        AIZ.plugins.notify('success', 'Featured reviews updated successfully');
                    }
                    else{
                        el.checked = !el.checked;
                        AIZ.plugins.notify('danger', 'Something went wrong');
                    }
                },
                error: function() {
                    el.checked = !el.checked;
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }

        // For Review Settings
        function reviewSettingsModalShow(){
            $('#reviewSettingsModal').modal('show', {backdrop: 'static'});
        }
    </script>
@endsection
