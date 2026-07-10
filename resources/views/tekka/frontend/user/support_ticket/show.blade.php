@extends(config('app.theme').'frontend.layouts.user_panel')

@section('meta')
<x-seo />
@endsection

@section('panel_content')
    <div class="card shadow-none border-0 support-details-page">
        <div class="card-header row  mx-0 align-items-basline">
            <div class="col-12 col-md-8 pl-0">
                <h5 class=" h5 ">{{ $ticket->subject }} </h5>
                <div class="fs-14">
                    #{{ $ticket->code }}
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex text-start text-md-right flex-column pr-0 pl-0 pl-md-auto">
                <div class="">
                    <span class="badge">{{ ucfirst($ticket->status) }}</span>
                </div>
                <div class="mt-2 d-block"> {{ $ticket->created_at }} </div>
            </div>
            <!-- <div class="text-center text-md-left">

               <div class="mt-2">
                   <span> {{ $ticket->user->name }} </span>


               </div>
            </div> -->
        </div>
        <div class="card-body">
            <div class="pad-top">
                <ul class="list-group list-group-flush">
                    @php
                        // dd($ticket->ticketReplies);
                    @endphp
                    @foreach($ticket->ticketReplies as $ticketreply)
                        {{-- @if($ticket->user_id == $ticketreply->user_id)
                        @endif --}}
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
                                        <span class="text-bold h6 text-muted">{{ $ticketreply->user->name }}</span>
                                        <p class="text-muted text-sm fs-11">{{$ticketreply->created_at}}</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                @php echo $ticketreply->reply; @endphp
                                <br>

                                    <div class="image-preview-wrapper">
                                        <div class="row">
                                            @foreach ((explode(",",$ticketreply->files)) as $key => $file)
                                            @php $file_detail = \App\Models\Upload::where('id', $file)->first(); @endphp
                                            @if($file_detail != null)
                                            <div class="col-6 col-md-4 pr-md-0">

                                                <div id="imagePreview" style="background-image: url({{ uploaded_asset($file) }});">
                                                    <a href="{{ uploaded_asset($file) }}" download="" class="download-img ">
                                                        <i class="fas fa-arrow-down"></i>
                                                        Download
                                                     </a>
                                                </div>
                                            </div>
                                            @endif
                                         @endforeach
                                        </div>
                                    </div>

                            </div>
                        </li>
                    @endforeach
                    <li class="list-group-item px-0">
                        <div class="media">
                            <a class="media-left" href="#">
                                @if($ticket->user->avatar_original != null)
                                    <span class="avatar avatar-sm mr-3">
                                        <img src="{{ uploaded_asset($ticket->user->avatar_original) }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                                    </span>
                                @else
                                    <span class="avatar avatar-sm mr-3">
                                        <img src="{{ static_asset('assets/img/avatar-place.png') }}">
                                    </span>
                                @endif
                            </a>
                            <div class="media-body">
                                <div class="comment-header">
                                    <span class="text-bold h6 text-muted">{{ $ticket->user->name }}</span>
                                    <p class="text-muted text-sm fs-11">{{ $ticket->created_at }}</p>
                                </div>
                            </div>
                         </div>
                        <div>
                        <div class="image-preview-wrapper">
                            <div class="row">
                            @foreach ((explode(",",$ticket->files)) as $key => $file)
                                @php $file_detail = \App\Models\Upload::where('id', $file)->first(); @endphp
                                @if($file_detail != null)
                                <div class="col-6 col-md-4 pr-md-0">

                                    <div id="imagePreview" style="background-image: url({{ uploaded_asset($file) }});">
                                        <a href="{{ uploaded_asset($file) }}" download="" class="download-img ">
                                            <i class="fas fa-arrow-down"></i>
                                            Download
                                         </a>
                                    </div>
                                </div>
                                @endif
                             @endforeach
                            </div>
                        </div>
                        <div class="content-wrapper row py-2 mt-2">
                            <p class="col-12">
                                @php echo $ticket->details; @endphp
                            </p>
                        </div>
                    </li>
                </ul>
             </div>
            <form action="{{route('support_ticket.seller_store')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="ticket_id" value="{{$ticket->id}}" required>
                <input type="hidden" name="user_id" value="{{$ticket->user_id}}">
                <div class="form-group">
                    <textarea class="aiz-text-editor" name="reply" data-buttons='[["font", ["bold", "underline", "italic"]],["para", ["ul", "ol"]],["view", ["undo","redo"]]]' required></textarea>
                </div>
                <div class="form-group row">
                    <div class="col-md-12">

                        <div class="input-group" >
                             <button type="submit" class="btn btn-sm btn-primary" onclick="submit_reply('pending')">{{ ('Send Reply') }}<i class="fas fa-chevron-right"></i></button>
                             <div  class="file-upload-system" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                 <div class="input-group-prepend">
                                     <div class="input-group-text  font-weight-medium"><i class="fas fa-image"></i></div>
                                 </div>
                                 <div class="form-control file-amount"><i class="fas fa-paperclip"></i></div>
                                 <input type="hidden" name="attachments" class="selected-files">
                             </div>
                        </div>
                        <div class="file-preview box sm">
                        </div>
                    </div>
                </div>
                <div class="form-group mb-0 text-right">

                </div>
            </form>
        </div>
        </div>
    </div>
@endsection
