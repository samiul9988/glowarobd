@extends(config('app.theme').'frontend.layouts.user_panel')

@section('panel_content')

    <div class="background-black">
        <div class="points-left">
            <div class="small-text">My Balance Points</div>
            <div class="big-text">{{number_format($user->point_balance)}}</div>
            <div class="ex-small-text"><i>Points will be expire at {{date("j M Y h:i:s a", strtotime($user->reward_point_expires_at))}}</i></div>
        </div>
    </div>


    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('My Reward Points History')}}</h5>
        </div>
        <div class="card-body">
                @forelse ($rewardPointLogs as $rewardPointLog)
                    <div class="point-lister">
                        <div class="point">
                            @if($rewardPointLog->activity_type === 'Earned')
                                +{{$rewardPointLog->earned}}
                            @else
                             -{{$rewardPointLog->spent}}
                            @endif
                        </div>
                        <div style="margin-left: 16px;">
                            <div class="text">{{$rewardPointLog->activity_str}}</div>
                            <div class="sub-text"><i>{{$rewardPointLog->created_at->diffForHumans()}}</i></div>
                        </div>
                    </div>
                @empty
                   <div class="text-center">
                        <p class="text-danger"> {{translate('Your Reward points log is empty now !!!')}}</p>
                    </div>
                @endforelse
            <div class="aiz-pagination">
                {{ $rewardPointLogs->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection



@section('script')
    <script>

    </script>
@endsection

@push('styles22')
    <style>
        .background-black {
            width: 100%;
            background-color: rgb(255, 20, 147);
            border-radius: 10px;
            display: flex;
            -webkit-box-pack: justify;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .background-black .points-left {
            margin-left: 20px;
        }
        .background-black .small-text {
            padding-top: 26px;
            padding-bottom: 10px;
            font-size: 14px;
            font-weight: 500;
            line-height: 18px;
            color: rgb(238, 238, 238);
        }
   
        .background-black .big-text {
            font-size: 53px;
            font-weight: 900;
            line-height: 56px;
            color: rgb(238, 238, 238);
        }
        .background-black .ex-small-text {
            padding-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
        }
        .point-lister {
            display: flex;
            position: relative;
            margin-bottom: 30px;
        }
        .point-lister::before {
            content: "";
            height: 30px;
            background-color: rgb(238, 238, 238);
            width: 2px;
            border-radius: 5px;
            position: absolute;
            top: 35px;
            left: 23px;
        }
        .point-lister .point {
            font-size: 12px;
            font-weight: 400;
            line-height: 16px;
            margin-bottom: 0px;
            padding: 4px 14px;
            background-color: rgb(255, 20, 147);
            color: white;
            width: fit-content;
            height: fit-content;
            border-radius: 50px;
        }
        .point-lister .text {
            font-size: 14px;
            font-weight: 900;
            line-height: 18px;
            color: rgb(51, 51, 51);
            padding-bottom: 10px;
        }

        .point-lister .sub-text {
            font-size: 12px;
            font-weight: 400;
            line-height: 18px;
            color: rgb(49, 49, 49);
        }
    </style>
@endpush
