@extends(config('app.theme').'frontend.layouts.user_panel')

@section('panel_content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Notifications')}}</h5>
        </div>
        <div class="card-body">
            <div class="notifications" style="cursor: pointer;">
                @forelse ($notifications as $notification)
                @php
                    $unread_noti = 'unread_noti';
                    if(App\Models\UserNotificationRead::where('notification_id',$notification->id)->exists()){
                        $unread_noti = 'read_noti';
                    }
                @endphp
                    <div class="notification {{$unread_noti}}" data-id={{$notification->id}}>
                        <div class="notification-icon" style="min-width:10%; height:auto">
                            <img class="card-img-left example-card-img-responsive w-100" src="{{ uploaded_asset($notification->image) }}"/>
                        </div>
                        <div class="notification-content">
                            <h4>{{$notification->title}}</h4>
                            @php
                                $data['message'] = $notification->message;
                                $data['max_length'] = 50;
                                // $data['btn'] = ' <a class="text-danger" href="">view more</a>';
                            @endphp
                            <span  class="parag d-none" hidden><?= $notification->message; ?></span>
                            <p>{!! custom_text_replace($data) !!}</p>
                        </div>
                        <div class="notification-time text-right" style="min-width:10%">
                            <span> <i>{{$notification->created_at->diffForHumans()}}</i></span>
                        </div>
                    </div>
                @empty
                   <div class="text-center">
                        <p class="text-danger"> {{translate('Your Notification box is empty now !!!')}}</p>
                    </div>
                @endforelse


              </div>
            <div class="aiz-pagination">
                {{ $notifications->appends(request()->input())->links() }}
            </div>

        </div>
    </div>
@endsection

@section('modal')
<div class="modal fade" id="notification_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Notification</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h4 class="push_h4"></h4>
        <p class="push_parag"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            $('.notification').click(function(){
                //let heading = $(this).find('.notification-content h4').text()
                let id = $(this).attr('data-id');
                let this_obj = $(this);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('user_notification.details')}}"+"?id="+id,
                    type: 'GET',
                    //data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        // console.log(response);
                        var response_obj = JSON.parse(response);
                        var response_data = response_obj.data[0];
                        // console.log(`here-${response_obj.success}`);
                        if(response_obj.success == true) {
                            let n_title = '';
                                n_title = `<a target="_blank" href="${response_data.web_url}">${response_data.title}</a>`;
                            $('.push_parag').html(response_data.message);
                            $('.push_h4').html(n_title);
                            $('#notification_modal').modal('show');
                            $('.notification_unread').html(response_data.total_unread);
                            this_obj.removeClass('unread_noti');
                            this_obj.addClass('read_noti');
                            //location.reload();
                        }
                    }
                });
            })
        })
    </script>
@endsection


