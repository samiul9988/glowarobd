@extends('backend.layouts.app')

@section('content')

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="align-items-center">
            <h1 class="h3">{{ ('All Colors') }}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <form class="" id="sort_colors" action="" method="GET">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ ('Colors') }}</h5>
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <input type="text" class="form-control form-control-sm" id="search" name="search"
                                    @isset($sort_search) value="{{ $sort_search }}" @endisset
                                    placeholder="{{ ('Type color name & Enter') }}">
                            </div>
                        </div>
                    </div>
                </form>

                <div class="card-body">
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ ('Name') }}</th>
                                <th class="text-right">{{ ('Options') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($colors as $key => $color)
                                <tr>
                                    <td>{{ ($key+1) + ($colors->currentPage() - 1)*$colors->perPage() }}</td>
                                    <td>{{ $color->name }}</td>
                                    <td class="text-right">
                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                            href="{{ route('colors.edit', ['id' => $color->id, 'lang' => env('DEFAULT_LANGUAGE')]) }}"
                                            title="{{ ('Edit') }}">
                                            <i class="las la-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                            data-href="{{ route('colors.destroy', $color->id) }}"
                                            title="{{ ('Delete') }}">
                                            <i class="las la-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="aiz-pagination">
                        {{ $colors->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ ('Add New Color') }}</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('colors.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name">{{ ('Name') }}</label>
                            <input type="text" placeholder="{{ ('Name') }}" id="name" name="name"
                                class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="name">{{ ('Color Code') }}</label>
                            <input type="text" placeholder="{{ ('Color Code') }}" id="code" name="code"
                                class="form-control" value="{{ old('code') }}" required>
                        </div>
                        <div class="form-group mb-3 text-right">
                            <button type="submit" class="btn btn-primary">{{ ('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0 h6">{{ ('Color filter activation')}}</h3>
                </div>
                <div class="card-body text-center">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'color_filter_activation')" <?php if(get_setting('color_filter_activation') == 1) echo "checked";?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('modal')
    @include('modals.delete_modal')
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
                }
                else{
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
    </script>
@endsection
