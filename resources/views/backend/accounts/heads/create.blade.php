@extends('backend.layouts.app')
@section('meta_title'){{ 'Create New Account/Head' }}@stop
@section('content')

<div class="col-lg-7 mx-auto">
    <div class="card" x-data="app()" x-cloak>
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ('Create Head')}}</h5>
        </div>
        <form action="{{ route('heads.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group row gutters-5">
                    <div class="" :class="showSubSelect ? 'col-6' : 'col-12'">
                        <label class="col-form-label">{{ ('Select Parent Head')}} :</label>
                        <select x-on:change="get_subheads()" class="select2 form-control aiz-selectpicker col-12" name="parent_head" x-model="parent_head" data-toggle="select2" data-placeholder="Select parent head" data-live-search="true" required>
                            <option value="" class="text-capitalize">Select Parent Head</option>
                            @foreach (\App\Models\AccHead::parentHeads() as $key => $phead)
                            <option value="{{ $phead }}" class="text-capitalize">{{ $phead }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6" x-show="showSubSelect">
                        <label class="col-form-label">{{ ('Select Sub Head')}} :</label>
                        <select class="select2 form-control aiz-selectpicker col-12" name="sub_head" x-model="sub_head" x-data="subheads" data-toggle="select2" data-placeholder="Select sub head" data-live-search="true"  id="sub_head_wrapper">

                        </select>
                    </div>
                    @error('parent_head')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="head">Head:</label>
                    <input type="text" class="form-control @error('head') is-invalid @enderror" id="head" name="head" placeholder="Enter head" x-model="head">
                    @error('head')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                </div>
            </div>
        </from>
        <script type="text/javascript">
            function app() {
                return {
                    parent_head: null,
                    sub_head: null,
                    head: null,
                    showSubSelect: false,
                    subheads: {},

                    async get_subheads() {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: "{{route('accounts.heads.get_subheads')}}",
                            type: 'POST',
                            data: {
                                parent_head  : this.parent_head
                            },
                            success: function (response) {
                                var obj = JSON.parse(response.html);
                                if(obj != '') {
                                    $('#sub_head_wrapper').html(obj);
                                    AIZ.plugins.bootstrapSelect('refresh');
                                }
                            }
                        });
                        this.showSubSelect = true;
                    },
                }
            }
        </script>
    </div>
</div>
@endsection
