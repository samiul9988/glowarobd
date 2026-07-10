@extends('backend.layouts.app')

@section('content')

<div class="col-lg-7 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ('Update Head')}}</h5>
        </div>
        <form action="{{ route('heads.update', $head->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="card-body">
                <div class="form-group row gutters-5">
                    <div class="col-6">
                        <label class="col-form-label">{{ ('Select Parent Head')}} :</label>
                        <select class="select2 form-control aiz-selectpicker col-12" name="parent_head" id="parent_head" data-toggle="select2" data-placeholder="Select parent head" data-live-search="true" required>
                            <option value="" class="text-capitalize">Select Parent Head</option>
                            @foreach (\App\Models\AccHead::parentHeads() as $key => $phead)
                            <option value="{{ $phead }}" class="text-capitalize" @if($head->parent_head == $phead) selected @endif>{{ $phead }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="col-form-label">{{ ('Select Sub Head')}} :</label>
                        <select class="select2 form-control aiz-selectpicker col-12" name="sub_head" data-toggle="select2" data-placeholder="Select sub head" data-live-search="true"  id="sub_head_wrapper">
                            <option value="" class="text-capitalize">Loading...</option>
                        </select>
                    </div>
                    @error('parent_head')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="head">Head:</label>
                    <input type="text" class="form-control @error('head') is-invalid @enderror" id="head" placeholder="Enter head" value="{{ $head->head }}" name="head" required>
                    @error('head')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-sm btn-primary">{{ ('Save')}}</button>
                </div>
            </div>
        </from>
    </div>
</div>
@endsection

@section('script')
    <script>
        let parent_head = '{{ $head->parent_head }}';
        let sub_head = '{{ $head->sub_head }}';
        get_subheads(parent_head);
        async function get_subheads() {
            $('#sub_head_wrapper').html('<option value="" class="text-capitalize">Loading...</option>');
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('accounts.heads.get_subheads') }}",
                type: 'POST',
                data: {
                    parent_head  : parent_head
                },
                success: function (response) {
                    // loop on array
                    const data = response.data;
                    if(data.length > 0) {
                        let options = '<option value="" class="text-capitalize">Select Sub Head</option>';
                        data.forEach(function(item) {
                            let selected = item.shead === sub_head ? 'selected' : '';
                            options += `<option value="${item.shead}" class="text-capitalize" ${selected}>${item.shead}</option>`;
                        });
                        $('#sub_head_wrapper').html(options);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }

        $('#parent_head').on('change', async function() {
            parent_head = $(this).val();
            await get_subheads();
        });
    </script>
@endsection
