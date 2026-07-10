@extends('backend.layouts.app')
@php
	$lang = '';
@endphp
@section('content')

    <div class="aiz-titlebar text-left mt-2 mb-3">
    	<div class="row align-items-center">
    		<div class="col">
    			<h1 class="h3">{{ ('Website Dashboard') }}</h1>
    		</div>
    	</div>
    </div>
    <div class="card">
    	<div class="card-header">
    		<h6 class="fw-600 mb-0">{{ ('General Settings') }}</h6>
    	</div>
    	<div class="card-body">
    		<div class="row gutters-10">
                <div class="col-lg-12">
					<form action="{{ route('business_settings.update') }}" method="POST">
                        @csrf
						<div class="form-group">
                            <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Enable Dashboard Caching') }}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="enable_dashboard_cache">
                                    <input type="checkbox" name="enable_dashboard_cache" value="1"
                                        @if (get_setting('enable_dashboard_cache', 0)) checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Dashboard Cache Time') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="dashboard_cache_time">
                                <input type="text" name="dashboard_cache_time" class="form-control" placeholder="Enter cache time in minutes" value="{{ get_setting('dashboard_cache_time') }}">
                                <small class="text-muted">{{ ('This will set the cache time for the dashboard (default 10 minutes)') }}</small>
                            </div>
                        </div>
						</div>
						<div class="form-group">
							<div class="text-right">
								<button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
							</div>
						</div>
					</form>
				</div>
    		</div>
    	</div>
    </div>
	<div class="card">
    	<div class="card-header">
    		<h6 class="fw-600 mb-0">{{ ('Shortcut Modules') }}</h6>
    	</div>
    	<div class="card-body">
    		<div class="row gutters-10">
                <div class="col-lg-12">
					<form action="{{ route('admin.shortcut_modules.store') }}" method="POST">
						@csrf
						<div class="form-group">
							<label>{{ ('Modules') }} </label>
							<div class="w3-modules-target">
								@foreach ($modules as $module)
									<div class="row gutters-5">
										<input type="hidden" name="module_ids[]" value="{{ $module->id }}">
										<div class="col-4">
											<div class="form-group">
												<input type="text" class="form-control" placeholder="{{ ('Name')}}" name="modules[]" value="{{ $module->name }}">
											</div>
										</div>
										<div class="col">
											<div class="form-group">
												<select class="form-control" name="module_dashboards[]">
													<option value="customer_care_dashboard" @if($module->dashboard == 'customer_care_dashboard') selected @endif>{{ ('Customer Care Dashboard') }}</option>
													<option value="packaging_dashboard" @if($module->dashboard == 'packaging_dashboard') selected @endif>{{ ('Packaging Dashboard') }}</option>
													<option value="account_inventory_dashboard" @if($module->dashboard == 'account_inventory_dashboard') selected @endif>{{ ('Account & Inventory Dashboard') }}</option>
													{{-- <option value="admin_dashboard" @if($module->dashboard == 'admin_dashboard') selected @endif>{{ ('Admin Dashboard') }}</option> --}}
												</select>
											</div>
										</div>
										<div class="col">
											<div class="form-group">
												<select class="form-control" name="module_statuses[]">
													<option value="1" @if($module->status == 1) selected @endif>{{ ('Active') }}</option>
													<option value="0" @if($module->status == 0) selected @endif>{{ ('Inactive') }}</option>
												</select>
											</div>
										</div>
										<div class="col-auto">
											<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
												<i class="las la-times"></i>
											</button>
										</div>
									</div>
								@endforeach
							</div>
							<button
								type="button"
								class="btn btn-soft-secondary btn-sm"
								data-toggle="add-more"
								data-content='<div class="row gutters-5">
									<input type="hidden" name="module_ids[]" value="">
									<div class="col-4">
										<div class="form-group">
											<input type="text" class="form-control" placeholder="{{ ('Name')}}" name="modules[]">
										</div>
									</div>
									<div class="col">
										<div class="form-group">
											<select class="form-control" name="module_dashboards[]">
												<option value="customer_care_dashboard">{{ ('Customer Care Dashboard') }}</option>
												<option value="packaging_dashboard">{{ ('Packaging Dashboard') }}</option>
												<option value="account_inventory_dashboard">{{ ('Account & Inventory Dashboard') }}</option>
												{{-- <option value="admin_dashboard">{{ ('Admin Dashboard') }}</option> --}}
											</select>
										</div>
									</div>
									<div class="col">
										<div class="form-group">
											<select class="form-control" name="module_statuses[]">
												<option value="1">{{ ('Active') }}</option>
												<option value="0">{{ ('Inactive') }}</option>
											</select>
										</div>
									</div>
									<div class="col-auto">
										<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
											<i class="las la-times"></i>
										</button>
									</div>
								</div>'
								data-target=".w3-modules-target">
								{{ ('Add New') }}
							</button>
						</div>
						<div class="form-group">
							<div class="text-right">
								<button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
							</div>
						</div>
					</form>
				</div>
    		</div>
    	</div>
    </div>
    <div class="card">
    	<div class="card-header">
    		<h6 class="fw-600 mb-0">{{ ('Quick Shortcuts') }}</h6>
    	</div>
    	<div class="card-body">
    		<div class="row gutters-10">
                <div class="col-lg-12">
					<form action="{{ route('admin.shortcuts.store') }}" method="POST" enctype="multipart/form-data">
						@csrf
						<div class="form-group">
							<label>{{ ('Links') }} - ({{ ('Shortcuts') }})</label>
							<div class="w3-links-target">
								@foreach ($shortcuts as $shortcut)
									<div class="row gutters-5">
										<input type="hidden" name="ids[]" value="{{ $shortcut->id }}">
										<div class="col">
											<div class="form-group">
												<input type="text" class="form-control" placeholder="{{ ('Label')}}" name="labels[]" value="{{ $shortcut->name }}">
											</div>
										</div>
										<div class="col">
											<div class="form-group">
												<select class="form-control" name="modules[]">
													@foreach ($modules as $module)
														<option value="{{ $module->id }}" @if($module->id == $shortcut->shortcut_module_id) selected @endif>{{ $module->name }} - ({{ ucwords(str_replace('_',' ',$module->dashboard)) }})</option>
													@endforeach
												</select>
											</div>
										</div>
										<div class="col">
											<div class="form-group">
												<input type="text" class="form-control" placeholder="http://" name="urls[]" value="{{ $shortcut->url }}">
											</div>
										</div>
										<div class="col">
											<div class="form-group">
												<div class="col">
													<div class="input-group" data-toggle="aizuploader" data-type="image">
														<div class="input-group-prepend">
															<div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
														</div>
														<div class="form-control file-amount">{{ ('Choose Files') }}</div>
														<input type="hidden" name="icons[]" value="{{ $shortcut->icon }}" class="selected-files">
													</div>
													<div class="file-preview box sm"></div>
												</div>
											</div>
										</div>
										<div class="col-auto">
											<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
												<i class="las la-times"></i>
											</button>
										</div>
									</div>
								@endforeach
							</div>
							<button
								type="button"
								class="btn btn-soft-secondary btn-sm"
								data-toggle="add-more"
								data-content='<div class="row gutters-5">
									<input type="hidden" name="ids[]" value="">
									<div class="col">
										<div class="form-group">
											<input type="text" class="form-control" placeholder="{{ ('Label')}}" name="labels[]">
										</div>
									</div>
									<div class="col">
										<div class="form-group">
											<select class="form-control" name="modules[]">
												@foreach ($modules as $module)
													<option value="{{ $module->id }}">{{ $module->name }} - ({{ ucwords(str_replace('_',' ',$module->dashboard)) }})</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col">
										<div class="form-group">
											<input type="text" class="form-control" placeholder="http://" name="urls[]">
										</div>
									</div>
									<div class="col">
										<div class="form-group">
											<div class="col">
												<div class="input-group" data-toggle="aizuploader" data-type="image">
													<div class="input-group-prepend">
														<div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
													</div>
													<div class="form-control file-amount">{{ ('Choose Files') }}</div>
													<input type="hidden" name="icons[]" class="selected-files">
												</div>
												<div class="file-preview box sm"></div>
											</div>
										</div>
									</div>
									<div class="col-auto">
										<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
											<i class="las la-times"></i>
										</button>
									</div>
								</div>'
								data-target=".w3-links-target">
								{{ ('Add New') }}
							</button>
						</div>
						<div class="form-group">
							<div class="text-right">
								<button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
							</div>
						</div>
					</form>
				</div>
    		</div>
    	</div>
    </div>
@endsection
