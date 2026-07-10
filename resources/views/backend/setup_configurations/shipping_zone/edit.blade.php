@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Shipping Zone')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('shipping_zone.update', $shipping_zone->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" value="PATCH">

                    <div class="form-group row">
                        <label class="col-sm-3 control-label" for="name">{{ ('Title')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ ('Title')}}" id="title" name="title" value="{{ $shipping_zone->title }}" class="form-control" required>
                        </div>
                    </div>


                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{ ('Rest of the world')}}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="rest_of_the_world" value="1" @if($shipping_zone->rest_of_the_world==1) checked @endif>
                                <span></span>
                            </label>
                        </div>
                    </div>


                    <div class="form-group row mb-3" id="area_box" @if($shipping_zone->rest_of_the_world) style="display:none" @endif>
                        <label class="col-sm-3 control-label" for="products">{{ ('Areas')}}</label>
                        <div class="col-sm-9">
                            <select name="area_ids[]" id="area_ids" class="form-control aiz-selectpicker" multiple required data-placeholder="{{ ('Choose Areas') }}" data-live-search="true" data-selected-text-format="count">
                                @foreach(\App\Models\Area::orderBy('created_at', 'desc')->get() as $area)
                                    <option value="{{$area->id}}" <?php if (in_array($area->id, explode(',',$shipping_zone->area_ids))) echo 'selected' ?>>{{ $area->getTranslation('name') }} / {{ $area->city->name }} / {{ $area->city->state->name }}</option>
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
