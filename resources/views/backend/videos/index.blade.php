@extends('backend.layouts.app')
@php
    $playlists = Cache::remember('filter_video_playlists', now()->addHours(6), function () {
        return \App\Models\VideoPlaylist::pluck('name', 'id');
    });
@endphp
@section('content')
    <div class="card">
        <form class="" id="sort_orders" action="" method="GET">
            <div class="card-header row gutters-5">
                <div class="col">
                    <h5 class="mb-md-0 h6">All Videos</h5>
                </div>

                <div class="col-md-2 ml-auto mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="category" data-live-search="true">
                        <option value="">Filter By Category</option>
                        @foreach ($playlists as $id => $name)
                            <option value="{{ $id }}" @if ($id == request()->category) selected @endif>
                                {{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 ml-auto mb-2 mb-md-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="status">
                        <option value="" selected>Filter By Status</option>
                        <option value="1" @if (request()->status === 1) selected @endif>Published</option>
                        <option value="0" @if (request()->status === 0) selected @endif>Draft</option>
                    </select>
                </div>

                <div class="col-md-2 mb-2 mb-md-0">
                    <div class="form-group mb-0">
                        <input type="text" class="aiz-date-range form-control form-control-sm"
                            value="{{ request()->date }}" name="date" placeholder="Filter By Date" data-format="DD-MM-Y"
                            data-separator=" to " data-advanced-range="true" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-2 mb-2 mb-md-0">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control form-control-sm" id="search" name="search"
                            value="{{ request()->search }}" placeholder="Search...">
                    </div>
                </div>

                <div class="col-auto mb-2 mb-md-0">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="window.location.href='{{ route('videos.index') }}'">Reset</button>
                    <div class="dropdown mb-2 mb-md-0 d-inline-block">
                        <button type="button" data-toggle="dropdown" class="btn btn-light btn-sm p-0 py-1">
                            <i class="las la-ellipsis-v font-weight-bold fs-24"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item btn-item" href="javascript:;" id="create-btn">
                                <i class="las la-plus text-success"></i> Create Video
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" onclick="bulk_change_status()">
                                <i class="las la-sync-alt text-info"></i> Change Status
                            </a>
                            <a class="dropdown-item btn-item" href="javascript:;" onclick="bulk_delete_modal()">
                                <i class="las la-trash text-danger"></i> Delete selection
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
                        <th>Title</th>
                        <th class="text-center">Categories</th>
                        <th data-breakpoints="md" class="text-center">Products Count</th>
                        <th data-breakpoints="md" class="text-center">Views Count</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Featured</th>
                        <th class="text-center" width="15%">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($videos as $key => $video)
                        <tr>
                            <td>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]"
                                                value="{{ $video->id }}">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ Str::limit($video->title, 50) }}
                                @if (strlen($video->title) > 50)
                                    @include('components.tooltip', [
                                        'title' => $video->title,
                                    ])
                                @endif
                                {{-- <span class="d-block text-primary fs-10 font-weight-bold">
                                    Last Update: {{ $video->updated_at->diffForHumans() }}
                                </span> --}}
                            </td>
                            @php
                                $videoPlaylists = $video->playlists->pluck('name')->toArray();
                            @endphp
                            <td class="text-center">
                                @if ($video->playlists_count > 1)
                                    Sync with <strong>{{ $video->playlists_count ?? 0 }}</strong> categories
                                    @include('components.tooltip', [
                                        // 'title' => implode('<br>', $videoPlaylists),
                                        'title' => implode(', ', $videoPlaylists),
                                        'html' => false, // set to true if using <br> in title
                                    ])
                                @else
                                    {{ $video->playlists_count == 1 ? $videoPlaylists[0] : 'No Category' }}
                                @endif
                            </td>
                            <td class="text-center">
                                Sync with <strong>{{ $video->products_count ?? 0 }}</strong> products
                            </td>
                            <td class="text-center font-weight-bold">
                                <span class="badge badge-inline badge-info font-weight-bold fs-12">
                                    <i class="las la-eye mr-1"></i>
                                    {{ readableNumber($video->views ?? rand(0, 99999)) }}
                                </span>
                                @if($video->last_viewed_at)
                                    <span class="d-block text-muted font-weight-bold fs-10">
                                        Last Viewed: {{ $video->last_viewed_at->diffForHumans() }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-inline badge-{{ $video->status == 1 ? 'success' : 'danger' }} font-weight-bold">{{ ucfirst(($video->status == 1 ? 'Published' : 'Draft')) }}</span>
                            </td>
                            <td class="text-center">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" id="video-featured-{{ $video->id }}" {{ $video->featured ? 'checked' : '' }} value="{{ $video->id }}" class="featured-change">
                                    <span class="slider round"></span>
                                </label>
                            </td>

                            <td class="text-center">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm d-none d-md-inline-block" href="#" title="Not Ready">
                                    <i class="las la-eye"></i>
                                </a>
                                <a href="{{ route('videos.edit', $video->id) }}" class="btn btn-soft-success btn-icon btn-circle btn-sm" title="Edit">
                                    <i class="las la-edit"></i>
                                </a>
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                    data-href="{{ route('videos.destroy', $video->id) }}" title="{{ 'Delete' }}">
                                    <i class="las la-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $videos->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection
@section('modal')
    @include('modals.delete_modal')
    @include('modals.bulk_delete_modal')
    {{-- Change Status Modal --}}
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Choose status
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="min-height: 400px">
                    <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                        id="update_status">
                        <option value="">Select Status</option>
                        <option value="1">Published</option>
                        <option value="0">Drafts</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="change_status()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Video Upload Modal --}}
    <div class="modal fade" id="uploadVideoModal" tabindex="-1" role="dialog" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 1700px !important;">
            <div class="modal-content border-0 shadow-lg rounded-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title font-weight-bold text-dark">Upload Video</h5>
                    <button type="button" class="btn btn-icon text-danger opacity-40 close-modal" onclick="closeUploadModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="m12 13.4l2.9 2.9q.275.275.7.275t.7-.275t.275-.7t-.275-.7L13.4 12l2.9-2.9q.275-.275.275-.7t-.275-.7t-.7-.275t-.7.275L12 10.6L9.1 7.7q-.275-.275-.7-.275t-.7.275t-.275.7t.275.7l2.9 2.9l-2.9 2.9q-.275.275-.275.7t.275.7t.7.275t.7-.275zm0 8.6q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="uploadZone" class="p-5 rounded-lg border border-secondary bg-light d-flex align-items-center justify-content-center" style="cursor: pointer; min-height: 550px;" data-action="1">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <div class="mb-3" id="uploadIcon">
                                <svg class="text-primary" xmlns="http://www.w3.org/2000/svg" width="70" height="70" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path fill="currentColor" fill-opacity="0" stroke-dasharray="20" stroke-dashoffset="20" d="M12 15h2v-6h2.5l-4.5 -4.5M12 15h-2v-6h-2.5l4.5 -4.5"><animate attributeName="d" begin="0.5s" dur="1.5s" repeatCount="indefinite" values="M12 15h2v-6h2.5l-4.5 -4.5M12 15h-2v-6h-2.5l4.5 -4.5;M12 15h2v-3h2.5l-4.5 -4.5M12 15h-2v-3h-2.5l4.5 -4.5;M12 15h2v-6h2.5l-4.5 -4.5M12 15h-2v-6h-2.5l4.5 -4.5"/><animate fill="freeze" attributeName="fill-opacity" begin="0.7s" dur="0.5s" values="0;1"/><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.4s" values="20;0"/></path><path stroke-dasharray="14" stroke-dashoffset="14" d="M6 19h12"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.5s" dur="0.2s" values="14;0"/></path></g></svg>
                            </div>
                            <p id="uploadText" class="mb-0 text-dark font-weight-medium">Click to upload video</p>
                        </div>
                    </div>

                    <div id="formZone" style="display: none;">
                        <div class="form-group">
                            <div id="aiz-video-uploader" class="input-group" data-toggle="aizuploader" data-type="video" data-multiple="false">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary">Browse</div>
                                </div>
                                <div class="form-control file-amount">Choose File</div>
                                <input type="hidden" name="attachment" id="video" class="selected-files" value="">
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            closeUploadModal();
            const $videoInput = $('#video');
            const $uploadZone = $('#uploadZone');
            const $formZone = $('#formZone');

            const observer = new MutationObserver(async function () {
                const fileId = $videoInput.val();
                if (fileId) {
                    $('#uploadZone').data('action', 0);
                    $('#uploadIcon').html('<svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="70" height="70" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path stroke-dasharray="2 4" stroke-dashoffset="6" d="M12 21c-4.97 0 -9 -4.03 -9 -9c0 -4.97 4.03 -9 9 -9"><animate attributeName="stroke-dashoffset" dur="0.6s" repeatCount="indefinite" values="6;0"/></path><path stroke-dasharray="32" stroke-dashoffset="32" d="M12 3c4.97 0 9 4.03 9 9c0 4.97 -4.03 9 -9 9"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.1s" dur="0.4s" values="32;0"/></path><path stroke-dasharray="10" stroke-dashoffset="10" d="M12 16v-7.5"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.5s" dur="0.2s" values="10;0"/></path><path stroke-dasharray="6" stroke-dashoffset="6" d="M12 8.5l3.5 3.5M12 8.5l-3.5 3.5"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.7s" dur="0.2s" values="6;0"/></path></g></svg>');
                    $('#uploadText').text('Video Uploaded. Redirecting...');
                    // await new Promise(resolve => setTimeout(resolve, 1500));

                    window.location.href = '{{ route('videos.create') }}' + '?video_id=' + fileId;
                } else {
                    $('#uploadZone').data('action', 1);
                    $('#uploadIcon').html('<svg class="text-primary" xmlns="http://www.w3.org/2000/svg" width="70" height="70" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path fill="currentColor" fill-opacity="0" stroke-dasharray="20" stroke-dashoffset="20" d="M12 15h2v-6h2.5l-4.5 -4.5M12 15h-2v-6h-2.5l4.5 -4.5"><animate attributeName="d" begin="0.5s" dur="1.5s" repeatCount="indefinite" values="M12 15h2v-6h2.5l-4.5 -4.5M12 15h-2v-6h-2.5l4.5 -4.5;M12 15h2v-3h2.5l-4.5 -4.5M12 15h-2v-3h-2.5l4.5 -4.5;M12 15h2v-6h2.5l-4.5 -4.5M12 15h-2v-6h-2.5l4.5 -4.5"/><animate fill="freeze" attributeName="fill-opacity" begin="0.7s" dur="0.5s" values="0;1"/><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.4s" values="20;0"/></path><path stroke-dasharray="14" stroke-dashoffset="14" d="M6 19h12"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.5s" dur="0.2s" values="14;0"/></path></g></svg>');
                    $('#uploadText').text('Click to upload video');
                }
            });

            observer.observe($videoInput[0], { attributes: true, attributeFilter: ['value'] });
        });

        $(document).on("change", ".check-all", function() {
            if (this.checked) {
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }
        });

        function change_status() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                $('#exampleModal').modal('hide');
                showAlert('error', '{{ 'Please select at least one item' }}');
                return;
            }
            let status = $('#update_status').val();

            $.ajax({
                type: "POST",
                url: "{{ route('videos.bulk-status-update') }}",
                data: {
                    ids: ids,
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#exampleModal').modal('hide');
                        showAlert('success', response.message, window.location.href);
                    } else {
                        $('#exampleModal').modal('hide');
                        showAlert('error', response.message || '{{ 'Something went wrong' }}');
                    }
                },
                error: function(xhr) {
                    $('#exampleModal').modal('hide');
                    showAlert('error', xhr.responseJSON.message);
                }
            });
        }

        function bulk_delete() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                $('#bulk_delete-modal').modal('hide');
                showAlert('error', '{{ 'Please select at least one item' }}');
                return;
            }
            $.ajax({
                type: "POST",
                url: "{{ route('videos.bulk-delete') }}",
                data: {
                    ids: ids,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#bulk_delete-modal').modal('hide');
                        showAlert('success', response.message, window.location.href);
                    } else {
                        $('#bulk_delete-modal').modal('hide');
                        showAlert('error', response.message || '{{ 'Something went wrong' }}');
                    }
                },
                error: function(xhr) {
                    $('#bulk_delete-modal').modal('hide');
                    showAlert('error', xhr.responseJSON.message);
                }
            });
        }

        function bulk_change_status() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                showAlert('error', '{{ 'Please select at least one item' }}');
                return;
            }
            $('#exampleModal').modal('show');
        }

        function bulk_delete_modal() {
            let ids = $('input[name="id[]"]:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                showAlert('error', '{{ 'Please select at least one item' }}');
                return;
            }
            $('#bulk_delete-modal').modal('show');
        }

        $('#create-btn').on('click', function(e) {
            e.preventDefault();
            $('#uploadVideoModal').modal('show');
        });

        $('.close-modal').hover(function() {
            $(this).removeClass('opacity-40');
        }, function() {
            $(this).addClass('opacity-40');
        });

        $('#uploadZone').on('click', function() {
            if ($(this).data('action') === 0) {
                return;
            }
            triggerUploader();
        });

        function triggerUploader() {
            let elem = $('#aiz-video-uploader');
            let multiple = elem.data("multiple");
            let type = elem.data("type");
            let oldSelectedFiles = elem.find(".selected-files").val();

            multiple = !multiple ? "" : multiple;
            type = !type ? "" : type;
            oldSelectedFiles = !oldSelectedFiles ? "" : oldSelectedFiles;

            AIZ.uploader.trigger(
                elem,
                "input",
                type,
                oldSelectedFiles,
                multiple
            );
        }

        function closeUploadModal() {
            $('#uploadVideoModal').modal('hide');
        }

        $(document).on("change", ".featured-change", async function() {
            let id = this.value;
            let featured = this.checked ? 1 : 0;

            await touchModel('featured', {id: id, featured: featured});
        });

        async function touchModel(type, data) {
            // Disable the checkbox to prevent multiple clicks
            $(`#video-${type}-${data.id}`).prop('disabled', true);
            data['_token'] = '{{ csrf_token() }}';
            await $.ajax({
                url: `{{ route('videos.touch') }}`,
                type: 'PUT',
                data: data,
                success: function(response){
                    $(`#video-${type}-${data.id}`).prop('disabled', false);
                    if(response.success){
                        AIZ.plugins.notify('success', response.message || 'Request successful');
                    }
                    else{
                        $(`#video-${type}-${data.id}`).prop('checked', !$(`#video-${type}-${data.id}`).prop('checked'));
                        AIZ.plugins.notify('danger', response.message || 'Something went wrong');
                    }
                }, error: function(xhr, status, error) {
                    $(`#video-${type}-${data.id}`).prop('disabled', false);
                    console.error('Error:', error);
                    $(`#video-${type}-${data.id}`).prop('checked', !$(`#video-${type}-${data.id}`).prop('checked'));
                    AIZ.plugins.notify('danger', xhr.responseJSON.message || 'Something went wrong');
                }
            });
        }
    </script>
@endsection
