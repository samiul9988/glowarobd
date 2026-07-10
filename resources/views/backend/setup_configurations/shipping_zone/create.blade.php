@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Shipping Zone')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('shipping_zone.store') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-3 control-label" for="name">{{ ('Title')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Title')}}" id="title" name="title" class="form-control" required>
                        </div>
                    </div>

                    @if(!\App\Models\ShippingZone::where('rest_of_the_world',1)->exists())
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{ ('Rest of the world')}}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="rest_of_the_world" value="1">
                                <span></span>
                            </label>
                        </div>
                    </div>
                    @endif

                    <div class="form-group row mb-3" id="area_box">
                        <label class="col-sm-3 control-label" for="products">{{ ('Areas')}}</label>
                        <div class="col-sm-9">
                            <select name="area_ids[]" id="area_ids" class="form-control aiz-selectpicker" multiple required data-placeholder="{{ ('Choose Areas') }}" data-live-search="true" data-selected-text-format="count">
                                @foreach(\App\Models\Area::orderBy('created_at', 'desc')->get() as $area)
                                    <option value="{{$area->id}}">{{ $area->getTranslation('name') }} / {{ $area->city->name }} / {{ $area->city->state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-danger">
                        {{ ('If any product has discount or exists in another flash deal, the discount will be replaced by this discount & time limit.') }}
                    </div>
                    <br>



                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ ('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function(){

            $('input[name="rest_of_the_world"]').on('click', function(){
                if($('input[name="rest_of_the_world"]').is(':checked')){
                    $('#area_box').hide();
                    $('#area_ids').removeAttr('required');
                }else{
                    $('#area_box').show();
                    $('#area_ids').addAttr('required');
                }
            });


        });
    </script>
@endsection
