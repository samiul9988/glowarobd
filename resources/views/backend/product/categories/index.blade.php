@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ 'All Categories' }}</h1>
            </div>
            <div class="col-md-6 text-md-right">
                <a href="{{ route('categories.create') }}" class="btn btn-primary">
                    <span>{{ 'Add New category' }}</span>
                </a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-block d-md-flex">
            <h5 class="mb-0 h6">Categories</h5>
            <form class="" id="sort_categories" action="" method="GET">
                <div class="d-flex">
                    <div class="box-inline pad-rgt pull-left mr-2">
                        <div class="" style="min-width: 200px;">
                            <select name="ctype" id="ctype" class="form-control form-control-sm aiz-selectpicker">
                                <option value="" selected>All</option>
                                <option value="parent" {{ request()->ctype === 'parent' ? 'selected' : '' }}>Only Parents</option>
                                <option value="child" {{ request()->ctype === 'child' ? 'selected' : '' }}>Only Childrens</option>
                                <option value="featured" {{ request()->ctype === 'featured' ? 'selected' : '' }}>Only Featured</option>
                            </select>
                        </div>
                    </div>
                    <div class="box-inline pad-rgt pull-left">
                        <div class="" style="min-width: 200px;">
                            <input type="text" class="form-control" id="search" name="search" value="{{ request()->search }}" placeholder="{{ 'Type name & Enter' }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th data-breakpoints="lg">#</th>
                        <th>{{ 'Name' }}</th>
                        <th data-breakpoints="lg">{{ 'Parent Category' }}</th>
                        <th data-breakpoints="lg">{{ 'Order Level' }}</th>
                        <th data-breakpoints="lg">{{ 'Level' }}</th>
                        {{-- <th data-breakpoints="lg">{{ ('Banner')}}</th>
                    <th data-breakpoints="lg">{{ ('Icon')}}</th> --}}
                        <th data-breakpoints="lg">{{ 'Start Date' }}</th>
                        <th data-breakpoints="lg">{{ 'End Date' }}</th>
                        <th data-breakpoints="lg">{{ 'Discount Status' }}</th>
                        <th data-breakpoints="lg">{{ 'Featured' }}</th>
                        <th data-breakpoints="lg">{{ 'Commission' }}</th>
                        <th width="10%" class="text-right">{{ 'Options' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $key => $category)
                        <tr>
                            <td>{{ $key + 1 + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                            <td>{{ $category->name }}</td>
                            <td>
                                @if ($category->parentCategory)
                                    {{ $category->parentCategory->name }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $category->order_level }}</td>
                            <td>{{ $category->level }}</td>
                            <td>{{ $category->start_date != 0 ? date('d-m-Y H:i:s', $category->start_date) : '0000-00-00 00:00' }}
                            </td>
                            <td>{{ $category->end_date != 0 ? date('d-m-Y H:i:s', $category->end_date) : '0000-00-00 00:00' }}
                            </td>
                            <td>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input onchange="update_category_status(this)" value="{{ $category->id }}"
                                        type="checkbox" <?php if ($category->status == 1) {
                                            echo 'checked';
                                        } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" onchange="update_featured(this)" value="{{ $category->id }}"
                                        <?php if ($category->featured == 1) {
                                            echo 'checked';
                                        } ?>>
                                    <span></span>
                                </label>
                            </td>
                            <td>{{ $category->commision_rate }} %</td>
                            <td class="text-right">
                                @if ($category->parent_id === 0)
                                    <a class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                        href="{{ route('admin.categories.edit-content', ['id' => $category->id, 'lang' => env('DEFAULT_LANGUAGE')]) }}"
                                        title="{{ 'Modify Content' }}">
                                        <i class="las la-pen-square"></i>
                                    </a>
                                @endif
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                    href="{{ route('categories.edit', ['id' => $category->id, 'lang' => env('DEFAULT_LANGUAGE')]) }}"
                                    title="{{ 'Edit' }}">
                                    <i class="las la-edit"></i>
                                </a>
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                    data-href="{{ route('categories.destroy', $category->id) }}"
                                    title="{{ 'Delete' }}">
                                    <i class="las la-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $categories->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection


@section('modal')
    @include('modals.delete_modal')
@endsection


@section('script')
    <script type="text/javascript">
        $('#ctype').on('change', function() {
            $('#sort_categories').submit();
        });

        function update_category_status(el) {
            if (el.checked) {
                var status = 1;
                var alertmsg =
                    `{{ 'If any product has discount or exists in flash deal, the discount will be replaced by this Category discount & time limit.' }}`;
            } else {
                var status = 0;
                var alertmsg =
                    `{{ 'If any product has discount or exists in flash deal, the discount will be removed.' }}`;
            }
            if (confirm(alertmsg)) {
                $.post('{{ route('categories.update_status') }}', {
                    _token: '{{ csrf_token() }}',
                    id: el.value,
                    status: status
                }, function(data) {
                    if (data == 1) {
                        location.reload();
                    } else {
                        AIZ.plugins.notify('danger', '{{ 'Something went wrong' }}');
                    }
                });
            }
        }

        function update_featured(el) {
            if (el.checked) {
                var status = 1;
            } else {
                var status = 0;
            }
            $.post('{{ route('categories.featured') }}', {
                _token: '{{ csrf_token() }}',
                id: el.value,
                status: status
            }, function(data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ 'Featured categories updated successfully' }}');
                } else {
                    AIZ.plugins.notify('danger', '{{ 'Something went wrong' }}');
                }
            });
        }
    </script>
@endsection
