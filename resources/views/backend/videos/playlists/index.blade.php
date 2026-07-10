@extends('backend.layouts.app')

@section('content')
    <div class="card">
        <form class="" id="sort_orders" action="" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">All Categories</h5>
                </div>

                <div class="col-md-2 ml-auto">
                    <select class="form-control form-control-sm aiz-selectpicker" name="status">
                        <option value="" selected>Filter by status</option>
                        <option value="active" @if(request()->status === 'active') selected @endif>Active</option>
                        <option value="inactive" @if(request()->status === 'inactive') selected @endif>Inactive</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control form-control-sm" value="{{ request()->date }}" name="date" placeholder="Filter by date" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request()->search }}" placeholder="Type & Enter">
                    </div>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='{{ route('video-playlists.index') }}'">Reset</button>
                    <div class="dropdown mb-2 mb-md-0 d-inline-block">
                        <button type="button" data-toggle="dropdown" class="btn btn-light btn-sm p-0 py-1">
                            <i class="las la-ellipsis-v font-weight-bold fs-24"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item btn-item" href="{{ route('video-playlists.create') }}">
                                <i class="las la-plus text-success"></i> Create Category
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" onclick="bulk_change_status()">
                                <i class="las la-sync-alt text-danger"></i> Change Status
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th width="5%">
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>Name</th>
                        <th class="text-center">Video Count</th>
                        <th class="text-center">Last Updated At</th>
                        <th class="text-center">
                            Featured
                            @include('components.tooltip', [
                                'title' => 'If a playlist is featured, it will be displayed on the home page.'
                            ])
                        </th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="15%">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($playlists as $key => $playlist)
                        <tr>
                            <td>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]"
                                                value="{{ $playlist->id }}">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ Str::limit($playlist->name, 50) }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-inline badge-info font-weight-bold">
                                    {{ $playlist->videos_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                {{ $playlist->updated_at->diffForHumans() }}
                            </td>

                            <td class="text-center">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" id="playlist-featured-{{ $playlist->id }}" {{ $playlist->featured ? 'checked' : '' }} value="{{ $playlist->id }}" class="featured-change">
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" id="playlist-status-{{ $playlist->id }}" {{ $playlist->status ? 'checked' : '' }} value="{{ $playlist->id }}" class="status-change">
                                    <span class="slider round"></span>
                                </label>
                            </td>

                            <td class="text-center">
                                <a class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                    href="{{ route('video-playlists.edit', $playlist->id) }}"
                                    title="{{ ('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                                @if($playlist->videos_count == 0)
                                    <a href="javascript:;"
                                        class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                        data-href="{{ route('video-playlists.destroy', $playlist->id) }}"
                                        title="Delete">
                                        <i class="las la-trash"></i>
                                    </a>
                                @else
                                    <span class="btn btn-soft-danger btn-icon btn-circle btn-sm" style="opacity: 0.5">
                                        @include('components.tooltip', [
                                            'title' => "You can't delete this playlist. It contains videos.",
                                            'icon' => 'las la-trash'
                                        ])
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $playlists->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection
@section('modal')
    @include('modals.delete_modal')
    {{-- Change Status Modal --}}
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    {{ ('Choose status') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="min-height: 400px">
                <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                    id="update_status">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="change_status()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }
        });

        async function update_status(el){
            const response = await touchModel({ id: el.value, status: el.checked ? 1 : 0 });
            if(response){
                AIZ.plugins.notify('success', 'Status has been updated successfully');
            }
            else{
                AIZ.plugins.notify('danger', 'Something went wrong');
                el.checked = !el.checked;
            }
        }

        $(document).on("change", ".status-change", async function() {
            let id = this.value;
            let status = this.checked ? 1 : 0;

            await touchModel('status', {id: id, status: status});
        });
        $(document).on("change", ".featured-change", async function() {
            let id = this.value;
            let featured = this.checked ? 1 : 0;

            await touchModel('featured', {id: id, featured: featured});
        });

        async function touchModel(type, data) {
            // Disable the checkbox to prevent multiple clicks
            $(`#playlist-${type}-${data.id}`).prop('disabled', true);
            data['_token'] = '{{ csrf_token() }}';
            await $.ajax({
                url: `{{ route('video-playlists.touch') }}`,
                type: 'PUT',
                data: data,
                success: function(response){
                    $(`#playlist-${type}-${data.id}`).prop('disabled', false);
                    if(response.success){
                        AIZ.plugins.notify('success', response.message || 'Status has been updated successfully');
                    }
                    else{
                        $(`#playlist-${type}-${data.id}`).prop('checked', !$(`#playlist-${type}-${data.id}`).prop('checked'));
                        AIZ.plugins.notify('danger', response.message || 'Something went wrong');
                    }
                }, error: function(xhr, status, error) {
                    $(`#playlist-${type}-${data.id}`).prop('disabled', false);
                    console.error('Error:', error);
                    $(`#playlist-${type}-${data.id}`).prop('checked', !$(`#playlist-${type}-${data.id}`).prop('checked'));
                    AIZ.plugins.notify('danger', xhr.responseJSON.message || 'Something went wrong');
                }
            });
        }

        function change_status() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                $('#exampleModal').modal('hide');
                showAlert('error', '{{ ('Please select at least one item') }}');
                return;
            }
            let status = $('#update_status').val();

            $.ajax({
                type: "POST",
                url: "{{ route('video-playlists.bulk-status-update') }}",
                data: {
                    ids: ids,
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        $('#exampleModal').modal('hide');
                        showAlert('success', response.message, window.location.href);
                    } else {
                        $('#exampleModal').modal('hide');
                        showAlert('error', response.message || '{{ ('Something went wrong') }}');
                    }
                },
                error: function (xhr) {
                    $('#exampleModal').modal('hide');
                    showAlert('error', xhr.responseJSON.message);
                }
            });
        }

        function bulk_change_status(){
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                showAlert('error', '{{ ('Please select at least one item') }}');
                return;
            }
            $('#exampleModal').modal('show');
        }
    </script>
@endsection
