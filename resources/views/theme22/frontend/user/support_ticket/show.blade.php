@extends(config('app.theme').'frontend.layouts.user_panel')

@section('panel_content')
    <div class="card">
        <div class="card-header d-block">
            <div class="row gutters-5">
                <div class="col">
                </div>
                <div class="col text-center">
                    <span class="d-block font-weight-bold h6">{{ Str::headline($ticket->issue) }}</span>
                    <span class="d-block">{{ $ticket->subject }}</span>
                </div>
                <div class="col d-flex justify-content-end">
                    <div class="text-center">
                        <span class="d-block">
                            @php
                                $class = match($ticket->status) {
                                    'open' => 'warning',
                                    'working' => 'info',
                                    'closed' => 'danger',
                                    default => 'danger',
                                };
                            @endphp
                            <span class="badge badge-inline badge-{{ $class }} font-weight-bold">
                                {{ ($ticket->status) }} 
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($ticket->status == 'closed')
                <div class="alert alert-danger">
                    {{ ('This ticket is closed. You cannot reply to it.') }}
                </div>
            @endif
            <div class="my-2 fs-15 border rounded p-4">
                {{ $ticket->details }}

                <div class="mt-2">
                    @foreach ((explode(",",$ticket->files)) as $key => $file)
                        @php $file_detail = \App\Models\Upload::where('id', $file)->first(); @endphp
                        @if($file_detail != null)
                            {{-- <a href="{{ uploaded_asset($file) }}" download="" class="badge badge-lg badge-inline badge-light mb-1">
                                <i class="las la-download text-muted">{{ $file_detail->file_original_name.'.'.$file_detail->extension }}</i>
                            </a>
                            <br> --}}
                            <img class="preview rounded mb-1" src="{{ uploaded_asset($file) }}" alt="{{ $file_detail->file_original_name.'.'.$file_detail->extension }}" data-file="{{ uploaded_asset($file) }}" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'; $(this).data('file', '');" style="width: 100px; height: 100px; cursor: pointer;">
                        @endif
                    @endforeach
                </div>
            </div>
            @if($ticket->status != 'closed')
                @if ($ticket->ticketReplies->isNotEmpty())
                    <div class="pad-top">
                        <ul class="list-group list-group-flush">
                            @php
                                $ticketreply = $ticket->ticketReplies->first();
                            @endphp
                            <li class="list-group-item px-0">
                                <div class="media">
                                    <a class="media-left" href="#">
                                        @if($ticketreply->user->avatar_original != null)
                                            <span class="avatar avatar-sm mr-3"><img src="{{ uploaded_asset($ticketreply->user->avatar_original) }}"></span>
                                        @else
                                            <span class="avatar avatar-sm mr-3"><img src="{{ static_asset('assets/img/avatar-place.png') }}"></span>
                                        @endif
                                    </a>
                                    <div class="media-body">
                                        <div class="">
                                            <span class="font-weight-bold fs-14">{{ $ticketreply->user->name }}</span>
                                            @if($ticketreply->user->user_type != 'customer')
                                                <span class="text-muted text-sm font-weight-bold fs-11 d-block">
                                                    {{ $ticketreply->user->staff?->role?->name ?? translate($ticketreply->user->user_type) }}
                                                </span>
                                            @endif
                                            <p class="text-muted text-sm fs-11">{{ $ticketreply->created_at->format('d-m-Y h:i A') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="">
                                    @php echo $ticketreply->reply; @endphp

                                    <div class="mt-3">
                                    @foreach ((explode(",",$ticketreply->files)) as $key => $file)
                                        @php $file_detail = \App\Models\Upload::where('id', $file)->first(); @endphp
                                        @if($file_detail != null)
                                            <img class="preview rounded mb-1" src="{{ uploaded_asset($file) }}" alt="{{ $file_detail->file_original_name.'.'.$file_detail->extension }}" data-file="{{ uploaded_asset($file) }}" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'; $(this).data('file', '');" style="width: 80px; height: 80px; cursor: pointer;">
                                        @endif
                                    @endforeach
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                @endif
                <form action="{{ route('tickets.seller_store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ticket_id" value="{{$ticket->id}}" required>
                    <input type="hidden" name="user_id" value="{{$ticket->user_id}}">
                    <div class="form-group">
                        <textarea class="aiz-text-editor" name="reply" data-buttons='[["font", ["bold", "underline", "italic"]],["para", ["ul", "ol"]],["view", ["undo","redo"]]]' required></textarea>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse')}}</div>
                                </div>
                                <div style="border-radius: 0 !important;" class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="attachments" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary" onclick="submit_reply('working')">{{ ('Send Reply') }}</button>
                    </div>
                </form>
            @endif
            <hr>
            <div class="pad-top">
                <ul class="list-group list-group-flush">
                    @forelse($ticket->ticketReplies as $ticketreply)
                        @if($ticket->status != 'closed' && $loop->index == 0)
                            @continue
                        @endif
                        <li class="list-group-item px-0">
                            <div class="media">
                                <a class="media-left" href="#">
                                    @if($ticketreply->user->avatar_original != null)
                                        <span class="avatar avatar-sm mr-3">
                                            <img src="{{ uploaded_asset($ticketreply->user->avatar_original) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                                        </span>
                                    @else
                                        <span class="avatar avatar-sm mr-3">
                                            <img src="{{ static_asset('assets/img/avatar-place.png') }}">
                                        </span>
                                    @endif
                                </a>
                                <div class="media-body">
                                    <div class="comment-header">
                                        <span class="font-weight-bold fs-14">{{ $ticketreply->user->name }}</span>
                                        @if($ticketreply->user->user_type != 'customer')
                                            <span class="text-muted text-sm font-weight-bold fs-11 d-block">
                                                {{ $ticketreply->user->staff?->role?->name ?? translate($ticketreply->user->user_type) }}
                                            </span>
                                        @endif
                                        <p class="text-muted text-sm fs-11">{{ $ticketreply->created_at->format('d-m-Y h:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                @php echo $ticketreply->reply; @endphp
                                @foreach ((explode(",",$ticketreply->files)) as $key => $file)
                                    @php $file_detail = \App\Models\Upload::where('id', $file)->first(); @endphp
                                    @if($file_detail != null)
                                        {{-- <a href="{{ uploaded_asset($file) }}" download="" class="badge badge-lg badge-inline badge-light mb-1">
                                            <i class="las la-download text-muted">{{ $file_detail->file_original_name.'.'.$file_detail->extension }}</i>
                                        </a>
                                        <br> --}}
                                        <img class="preview rounded mb-1" src="{{ uploaded_asset($file) }}" alt="{{ $file_detail->file_original_name.'.'.$file_detail->extension }}" data-file="{{ uploaded_asset($file) }}" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'; $(this).data('file', '');" style="width: 80px; height: 80px; cursor: pointer;">
                                    @endif
                                @endforeach
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item px-0 text-center font-weight-bold fs-14">
                            No replies yet.
                        </li>
                    @endforelse
                </ul>
            </div>
            @if (is_null($ticket->rating) && $ticket->status == 'closed')
                <div id="rating-section" class="d-flex justify-content-center">
                    <div class="card shadow-sm px-2 py-4 w-50">
                        <div class="row gutters-5">
                            <div class="col-12 text-center">
                                <h5 class="mb-0 h6 font-weight-bold">{{ ('Rate Us') }}</h5>
                                <div class="mb-0">
                                    <div class="star-rating">
                                        <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 stars"></label>
                                        <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars"></label>
                                        <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars"></label>
                                        <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars"></label>
                                        <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star"></label>
                                    </div>
                                </div>
                                <div class="input-group mb-0">
                                    <input type="text" class="form-control" style="border-radius: 0 !important;" id="comments" name="comments" placeholder="Please share your feedback about our service...">
                                    <div class="input-group-prepend">
                                        <span role="button" id="submit-rating" class="input-group-text"><i class="lab la-telegram-plane"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .star-rating {
                        direction: rtl;
                        display: inline-block;
                        padding: 20px;
                    }
                    
                    .star-rating input[type=radio] {
                        display: none;
                    }
                    
                    .star-rating label {
                        color: #bbb;
                        font-size: 30px;
                        padding: 0;
                        cursor: pointer;
                        -webkit-transition: all 0.3s ease-in-out;
                        transition: all 0.3s ease-in-out;
                    }
                    
                    .star-rating label:before {
                        content: "\2605";
                    }
                    
                    .star-rating input[type=radio]:checked ~ label {
                        color: #f8ca00;
                    }
                    
                    .star-rating label:hover,
                    .star-rating label:hover ~ label {
                        color: #f8ca00;
                    }
                    
                    .star-rating input[type=radio]:checked + label:hover,
                    .star-rating input[type=radio]:checked ~ label:hover,
                    .star-rating label:hover ~ input[type=radio]:checked ~ label,
                    .star-rating input[type=radio]:checked ~ label:hover ~ label {
                        color: #ffd700;
                        transform: scale(1.2);
                    }
                </style>
            @endif
        </div>
    </div>
@endsection
@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const starLabels = document.querySelectorAll('.star-rating label');
        
        starLabels.forEach(label => {
            label.addEventListener('click', function() {
                // Add a small animation when star is selected
                this.classList.add('animate__animated', 'animate__bounceIn');
                
                setTimeout(() => {
                    this.classList.remove('animate__animated', 'animate__bounceIn');
                }, 1000);
            });
        });
    });

    function submit_reply(status){
        $('input[name=status]').val(status);
        let reply = $('textarea[name=reply]').val();
        let attachments = $('input[name=attachments]').val();
        if(reply.length > 0){
            $('#ticket-reply-form').submit();
        } else {
            showAlert('error', '{{ ('Please write a reply') }}');
        }
    }

    $('#submit-rating').on('click', function(){
        var rating = $('input[name="rating"]:checked').val();
        var comments = $('#comments').val();
        var ticket_id = {{ $ticket->id }};

        if (rating) {
            $.post('{{ route('tickets.rating') }}', {
                _token: '{{ csrf_token() }}',
                ticket_id: ticket_id,
                rating: rating,
                review: comments
            }, function(response) {
                if (response.success) {
                    $('#rating-section').remove();
                    showAlert('success', response.message || 'Thank you for your feedback!');
                } else {
                    showAlert('error', 'Something went wrong. Please try again.');
                }
            });
        } else {
            showAlert('error', 'Please select a rating before submitting.');
        }
    });
    $('.preview').on('click', function() {
        let file = $(this).data('file');
        if (!file) {
            showAlert('error', '{{ ('File not found') }}');
            return;
        }
        window.open(file, '_blank');
    });
</script>
@endsection