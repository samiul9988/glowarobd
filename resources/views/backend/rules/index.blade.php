@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="align-items-center">
            <h1 class="h3">{{ ('All Rewrite URLs') }}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header row gutters-5">
                    <div class="col text-center text-md-left">
                        <h5 class="mb-md-0 h6">{{ ('Rewrite URLs') }}</h5>
                    </div>
                    <div class="col-md-4">
                        <form class="" id="sort_rules" action="" method="GET">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="search"
                                    name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset
                                    placeholder="{{ ('Type name & Enter') }}">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                <th data-breakpoints="lg">#</th>
                                <th>{{ ('URL') }}</th>
                                <th data-breakpoints="lg">{{ ('Redirect URL') }}</th>
                                <th data-breakpoints="lg">{{ ('Status') }}</th>
                                <th data-breakpoints="lg">{{ ('Last Modified At') }}</th>
                                <th class="text-right">{{ ('Options') }}</th>
                            </tr>
                        </thead>
                        <tbody id="rewrite-rules">
                            @foreach ($urls as $key => $item)
                                {{-- @dd($item) --}}
                                <tr>
                                    <td>{{ $key + 1 + ($urls->currentPage() - 1) * $urls->perPage() }}</td>
                                    <td style="max-width: 100px;">{{ $item->url }}</td>
                                    <td style="max-width: 200px;">{{ $item->redirect_to }}</td>
                                    <td>
										<div class="row">
											<div class="col-md-8">
												<label class="aiz-switch aiz-switch-success mb-0">
													<input value="1" class="status" name="status" type="checkbox" @if($item->status) checked @endif>
													<span class="slider round"></span>
												</label>
											</div>
										</div>
									</td>
                                    <td>{{ $item->updated_at->format('d-m-Y H:i:s') }}</td>
                                    <td class="text-right">
                                        <a class="btn btn-soft-secondary btn-icon btn-circle btn-sm" href="{{ url($item->url) }}" target="_blank" title="{{ ('Open Link') }}">
                                            <i class="las la-globe"></i>
                                        </a>
                                        <a role="button" class="edit-rule btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ ('Edit') }}" data-id="{{ $item->id }}">
                                            <i class="las la-edit"></i>
                                        </a>
                                        <a href="#"
                                            class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                            data-href="{{ route('rewrite_url.destroy', $item->id) }}"
                                            title="{{ ('Delete') }}">
                                            <i class="las la-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="aiz-pagination">
                        {{ $urls->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 id="form-header" class="mb-0 h6">{{ ('Add New Rule') }}</h5>
                    <button id="help-button" type="button" class="btn btn-sm btn-warning">
                        {{ ('Help') }}
                        <i class="las la-question-circle"></i>
                    </button>
                </div>
                <div class="card-body">
                    <form id="rewrite-rule-form" action="{{ route('rewrite_url.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="url">{{ ('Url') }}</label>
                            <div class="input-group">
                                @php
                                    $url = config('app.frontend');
                                    if(!Str::endsWith($url, '/')){
                                        $url = $url.'/';
                                    }
                                @endphp
                                <span class="input-group-text">{{ $url }}</span>
                                <input type="text" placeholder="{{ ('Url') }}" name="url" id="url" class="form-control" value="{{ old('url') }}" required>
                            </div>
							<div class="text-danger" style="display: none" id="url_error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="redirect_to">{{ ('Redirect Url') }}</label>
                            <div class="input-group">
                                @php
                                    $url = config('app.frontend');
                                    if(!Str::endsWith($url, '/')){
                                        $url = $url.'/';
                                    }
                                @endphp
                                {{-- <span class="input-group-text">{{ $url }}</span> --}}
                                <input type="text" class="form-control" name="redirect_to" id="redirect_to" placeholder="{{ ('Redirect Url') }}" value="{{ old('redirect_to') }}" required>
                            </div>
                            {{-- <input type="text" class="form-control" name="redirect_to" id="redirect_to" placeholder="{{ ('Redirect Url') }}" required> --}}
							<div class="text-danger" style="display: none" id="redirect_to_error"></div>
                        </div>
                    </form>
                    <div class="form-group mb-3 text-right">
                        <button id="clear-btn" class="btn btn-secondary">{{ ('Clear') }}</button>
                        <button id="submit-btn" type="submit" class="btn btn-primary">{{ ('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

	<div style="display: none">
		<form action="" id="update-status-form" method="POST">
			@csrf
			<input type="hidden" name="status" id="status">
		</form>
	</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
    <!-- delete Modal -->
    <div id="help-modal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ ('Read The Instruction')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <p>{!! translate('You can add a new rule by entering the <strong>URL</strong> which is the previous url and the <strong>Redirect URL</strong> which is the latest url. The URL should be the path after the domain name. For example, if you want to redirect the URL "https://example.com/old-url" to "https://example.com/new-url", you should enter <code>"old-url"</code> in the <strong>URL</strong> field. And in the <strong>Redirect URL</strong> field you could enter <code>"new-url"</code> only or you could enter the full url <code>"https://example.com/new-url"</code>.') !!}</p>
                </div>
            </div>
        </div>
    </div><!-- /.modal -->
@endsection

@section('script')
    <script type="text/javascript">
        let action = '{{ route("rewrite_url.store") }}';
        $('#help-button').on('click', function() {
            $('#help-modal').modal('show');
        });
		$('#clear-btn').on('click', function(el) {
			el.preventDefault();
			reset();
		});
		function reset() {
            action = '{{ route("rewrite_url.store") }}';
			$('#url').val('');
			$('#redirect_to').val('');
			$('#form-header').text('{{ ('Add New Rule') }}');
			$('#submit-btn').text('{{ ('Save') }}');
            $('#url_error').text('').hide();
            $('#redirect_to_error').text('').hide();
		}

        $('#rewrite-rules').on('click', '.edit-rule', function() {
            let id = $(this).data('id');
			action = '{{ route("rewrite_url.update", ":id") }}'.replace(':id', id);
			var url = $(this).closest('tr').find('td').eq(1).text();
			var redirect_to = $(this).closest('tr').find('td').eq(2).text();
			$('#url').val(url);
			$('#redirect_to').val(redirect_to);
			$('#rewrite-rule-form').attr('action', action);
			$('#form-header').text('{{ ('Edit Rule') }}');
			$('#submit-btn').text('{{ ('Update') }}');
		});

		$('#rewrite-rules').on('click', '.status', function() {
			let id = $(this).closest('tr').find('.edit-rule').data('id');
			let action = '{{ route("rewrite_url.update_status", ":id") }}'.replace(':id', id);
			let status = $(this).prop('checked') ? 1 : 0;
			$('#status').val(status);
			$('#update-status-form').attr('action', action);
			$('#update-status-form').submit();
		});
        function sort_rules(el) {
            $('#sort_rules').submit();
        }

        $('#submit-btn').on('click', function() {
            process(action);
        });

        function process(action){
            var isValid = true;
            var url = $('#url').val();
            var redirect_to = $('#redirect_to').val();
            $('#url_error').text('').hide();
            $('#redirect_to_error').text('').hide();
            if(url == ''){
                $('#url_error').text('URL is required').show();
                isValid = false;
            }

            if(redirect_to == ''){
                $('#redirect_to_error').text('Redirect URL is required').show();
                isValid = false;
            }

            if(!isValid){
                return;
            }

            $.ajax({
                type: 'POST',
                url: action,
                data: {
                    _token: '{{ csrf_token() }}',
                    url: url,
                    redirect_to: redirect_to
                },
                success: function(response) {
                    if(response.success){
                        // Alert
                        if(action == '{{ route("rewrite_url.store") }}'){
                            AIZ.plugins.notify('success', '{{ ("New rule added successfully.") }}');
                        }else{
                            AIZ.plugins.notify('success', '{{ ("Rule updated successfully.") }}');
                        }
                        location.reload();
                    }else{
                        AIZ.plugins.notify('danger', '{{ ("Something went wrong.") }}');
                    }
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;

                        if (errors.url) {
                            $('#url_error').text(errors.url[0]).show();
                        }

                        if (errors.redirect_to) {
                            $('#redirect_to_error').text(errors.redirect_to[0]).show();
                        }
                    } else {
                        AIZ.plugins.notify('danger', '{{ ("Server Error.") }}');
                        console.log(xhr.responseText);
                    }
                }
            });
        }
    </script>
@endsection
