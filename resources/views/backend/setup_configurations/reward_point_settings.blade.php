@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-12">
            <h2>Points Program</h2>
            <span><strong>Customize Your Loyalty/Reward Program</strong></span>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-3">
            <h3>Reward Points Status</h3>
            <span><strong>Enable or disable reawrd points program</strong></span>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-body d-flex justify-content-between">
                    <h3 class="mb-0 h6 text-center">
                        @if(@get_setting('reward_point_system') == 1)
                            {{ ('Reward Point System is Currently Enabled')}}
                        @else
                            {{ ('Reward Point System is Currently Disabled')}}
                        @endif
                    </h3>
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input value="1" type="checkbox" onchange="updateSettings(this, 'reward_point_system')" <?php if(@get_setting('reward_point_system') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-3">
            <h3>Earn Points</h3>
            <p><strong>Create ways your customers can earn when they complete such activities to increase sales & traffic.</strong></p>
            {{-- <button class="btn btn-success my-2">Add way to earn points</button> --}}
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5>Customers will earn points through the actions bellow</h5>
                </div>
                <div class="card-body">
                    @foreach($rewardEarnActions as $action)
                        <div class="row justify-content-between">
                            <div class="col action-info d-flex">
                                <!-- <div class="action-icon p-2 border mr-2">
                                    <h6><i class="las la-luggage-cart"></i></h6>
                                </div> -->
                                <div class="action-name-info">
                                    <h6><strong>{{ $action->activity_title }}</strong></h6>
                                    <span>Earn {{ $action->earn_point }} points for every {{ currency_symbol() }}{{ @$action->spent_amount }} spent</span>
                                </div>
                            </div>
                            {{-- <div class="col action-point">
                                {{ $action->earn_point }}
                            </div>--}}
                            <div class="col action-actions d-flex">
                                <a href="javascript:void(0)" onclick="rewardEarnActionEdit();" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ ('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                                {{-- <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" title="{{ ('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>--}}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-3">
            <h3>Redeem Reward Points</h3>
            <p><strong>Create reawrd point redeem rules that will be applied when particular activity occurs</strong></p>
            {{-- <button class="btn btn-success my-2">Add way to redeem points</button> --}}
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5>Customers will redeem points through the actions bellow</h5>
                </div>
                <div class="card-body">
                    @foreach($rewardRedeemActions as $action)
                        <div class="row justify-content-between">
                            <div class="col action-info d-flex">
                                <!-- <div class="action-icon p-2 border mr-2">
                                    <h6><i class="las la-luggage-cart"></i></h6>
                                </div> -->
                                <div class="action-name-info">
                                    <h6><strong>{{ $action->activity_title }}</strong></h6>
                                    <span>Redeem {{ currency_symbol() }}{{ @$action->earn_amount }} for every {{ @$action->spent_point }} points spent</span>
                                </div>
                            </div>
                            {{-- <div class="col action-point">
                                {{ $action->earn_amount }}
                            </div>--}}
                            <div class="col action-actions d-flex">
                                <a href="javascript:void(0)" onclick="rewardRedeemActionEdit();" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ ('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                                {{-- <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" title="{{ ('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>--}}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

<!-- // Edit Earn Reward Modal -->
<div class="modal fade" id="rewardEarnActionEdit">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Earn Reward Point Settings')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        @php
                            $earnaction = $rewardEarnActions->first();
                        @endphp
                        <form class="form-horizontal" action="{{ route('reward-point.editEarnRewardAction', @$earnaction['id']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ @$earnaction['id'] }}">
                            <div class="form-group row">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col">
                                            <label for="">Amount</label>
                                            <div class="input-group mb-3">
                                                <input name="spent_amount" type="number" min="1" step="1" class="form-control" placeholder="100" aria-label="100" value="{{ @$earnaction['spent_amount'] }}">
                                            </div>
                                        </div>
                                        <div class="col">
                                            <label for="">Reward Point</label>
                                            <div class="input-group mb-3">
                                                <input name="earn_point" type="number" min="1" step="1" class="form-control" placeholder="1" aria-label="1" value="{{ @$earnaction['earn_point'] }}">
                                            </div>
                                        </div>
                                        {{-- <div class="col">
                                            <label for="">Validity (in days)</label>
                                            <div class="input-group mb-3">
                                                <input name="validity" type="number" min="1" step="1" class="form-control" placeholder="1" aria-label="1" value="{{ @$earnaction['validity'] }}">
                                            </div>
                                        </div> --}}
                                    </div>
                                    <div class="alert alert-info">
                                        Amount to convert in reward point
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- // Edit Redeem Reawrd Modal -->
<div class="modal fade" id="rewardRedeemActionEdit">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6">{{ ('Redeem Reward Point Settings')}}</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        @php
                            $redeemaction = $rewardRedeemActions->first();
                        @endphp
                        <form class="form-horizontal" action="{{ route('reward-point.editRedeemRewardAction', @$redeemaction['id']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ @$redeemaction['id'] }}">
                            <div class="form-group row">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col">
                                            <label for="">Reward Point</label>
                                            <div class="input-group mb-3">
                                                <input name="spent_point" type="number" min="1" step="1" class="form-control" placeholder="100" aria-label="100" value="{{ @$redeemaction['spent_point'] }}">
                                            </div>
                                        </div>
                                        <div class="col">
                                            <label for="">Amount</label>
                                            <div class="input-group mb-3">
                                                <input name="earn_amount" type="number" min="1" step="1" class="form-control" placeholder="1" aria-label="1" value="{{ @$redeemaction['earn_amount'] }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        Reawrd point to convert in discount
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ ('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript">
        function updateSettings(el, type){
            if($(el).is(':checked')){
                var value = 1;
            }
            else{
                var value = 0;
            }

            $.post('{{ route('business_settings.update.activation') }}', {_token:'{{ csrf_token() }}', type:type, value:value}, function(data){
                if(data == '1'){
                    AIZ.plugins.notify('success', '{{ ('Settings updated successfully') }}');
                    location.reload();
                }
                else{
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
        function rewardEarnActionEdit()
        {
            $('#rewardEarnActionEdit').modal('show', {backdrop: 'static'});
        }
        function rewardRedeemActionEdit()
        {
            $('#rewardRedeemActionEdit').modal('show', {backdrop: 'static'});
        }
    </script>
@endsection
