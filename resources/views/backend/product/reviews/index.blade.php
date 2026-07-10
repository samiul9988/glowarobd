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
        <div class="row flex-grow-1">
            <div class="col">
                <h5 class="mb-0 h6">{{ ('Product Reviews')}}</h5>

            </div>
            <div class="col-md-6 col-xl-4 ml-auto mr-0">
                <form class="" id="sort_by_rating" action="{{ route('reviews.index') }}" method="GET">
                    <div class="" style="min-width: 200px;">
                        <select class="form-control aiz-selectpicker" name="rating" id="rating" onchange="filter_by_rating()">
                            <option value="">{{ ('Filter by Rating')}}</option>
                            <option value="rating,desc">{{ ('Rating (High > Low)')}}</option>
                            <option value="rating,asc">{{ ('Rating (Low > High)')}}</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>{{ ('Product')}}</th>
                    <th data-breakpoints="lg">{{ ('Product Owner')}}</th>
                    <th data-breakpoints="lg">{{ ('Customer')}}</th>
                    <th>{{ ('Rating')}}</th>
                    <th>{{ ('Photos')}}</th>
                    <th data-breakpoints="sm">{{ ('Comment')}}</th>
                    <th data-breakpoints="lg">{{ ('Date')}}</th>
                    <th data-breakpoints="lg">{{ ('Published')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviews as $key => $review)
                    @if ($review->product != null)
                        <tr>
                            <td>{{ ($key+1) + ($reviews->currentPage() - 1)*$reviews->perPage() }}</td>
                            <td>
                                <a href="{{ route('product', $review->product->slug) }}" target="_blank" class="text-reset text-truncate-2">{{ $review->product->getTranslation('name') }}</a>
                            </td>
                            <td>{{ $review->product->added_by }}</td>
                            <td>{{ $review->name ?? $review->user->name ?? '' }} ({{ $review->email ?? $review->user->email ?? ''}})</td>
                            <td>{{ $review->rating }}</td>
                            <td>
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
                            <td style="max-width: 365px;">{{ $review->comment }}</td>
                            <td>{{ date('d-m-Y h:i A', strtotime($review->created_at)) }}</td>
                            <td><label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_published(this)" value="{{ $review->id }}" type="checkbox" <?php if($review->status == 1) echo "checked";?> >
                                <span class="slider round"></span></label>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $reviews->appends(request()->input())->links() }}
        </div>
    </div>
</div>

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
@endsection

@section('script')
    <script type="text/javascript">
        function update_published(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('reviews.published') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ ('Published reviews updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }
        function filter_by_rating(el){
            var rating = $('#rating').val();
            if (rating != '') {
                $('#sort_by_rating').submit();
            }
        }

        // For Review Settings
        function reviewSettingsModalShow(){
            $('#reviewSettingsModal').modal('show', {backdrop: 'static'});
        }
    </script>
@endsection
