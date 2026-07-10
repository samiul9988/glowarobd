@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col">
			<h1 class="h3">{{ ('Website Header') }}</h1>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-8 mx-auto">
		<div class="card">
			<div class="card-header">
				<h6 class="mb-0">{{ ('Header Setting') }}</h6>
			</div>
			<div class="card-body">
				<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="form-group row">
	                    <label class="col-md-3 col-from-label">{{ ('Header Logo') }}</label>
						<div class="col-md-8">
		                    <div class=" input-group " data-toggle="aizuploader" data-type="image">
		                        <div class="input-group-prepend">
		                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
		                        </div>
		                        <div class="form-control file-amount">{{ ('Choose File') }}</div>
								<input type="hidden" name="types[]" value="header_logo">
		                        <input type="hidden" name="header_logo" class="selected-files" value="{{ get_setting('header_logo') }}">
		                    </div>
		                    <div class="file-preview"></div>
						</div>
	                </div>
                    <div class="form-group row">
						<label class="col-md-3 col-from-label">{{ ('Show Language Switcher?')}}</label>
						<div class="col-md-8">
							<label class="aiz-switch aiz-switch-success mb-0">
								<input type="hidden" name="types[]" value="show_language_switcher">
								<input type="checkbox" name="show_language_switcher" @if( get_setting('show_language_switcher') == 'on') checked @endif>
								<span></span>
							</label>
						</div>
					</div>
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{ ('Show Currency Switcher?')}}</label>
						<div class="col-md-8">
							<label class="aiz-switch aiz-switch-success mb-0">
								<input type="hidden" name="types[]" value="show_currency_switcher">
								<input type="checkbox" name="show_currency_switcher" @if( get_setting('show_currency_switcher') == 'on') checked @endif>
								<span></span>
							</label>
						</div>
					</div>
	                <div class="form-group row">
						<label class="col-md-3 col-from-label">{{ ('Enable stikcy header?')}}</label>
						<div class="col-md-8">
							<label class="aiz-switch aiz-switch-success mb-0">
								<input type="hidden" name="types[]" value="header_stikcy">
								<input type="checkbox" name="header_stikcy" @if( get_setting('header_stikcy') == 'on') checked @endif>
								<span></span>
							</label>
						</div>
					</div>
					<div class="border-top pt-3">
						<div class="form-group row">
		                    <label class="col-md-3 col-from-label">{{ ('Topbar Banner') }}</label>
							<div class="col-md-8">
			                    <div class=" input-group " data-toggle="aizuploader" data-type="image">
			                        <div class="input-group-prepend">
			                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
			                        </div>
			                        <div class="form-control file-amount">{{ ('Choose File') }}</div>
									<input type="hidden" name="types[]" value="topbar_banner">
			                        <input type="hidden" name="topbar_banner" class="selected-files" value="{{ get_setting('topbar_banner') }}">
			                    </div>
			                    <div class="file-preview"></div>
							</div>
		                </div>
		                <div class="form-group row">
							<label class="col-md-3 col-from-label">{{ ('Topbar Banner Link')}}</label>
							<div class="col-md-8">
								<div class="form-group">
									<input type="hidden" name="types[]" value="topbar_banner_link">
									<input type="text" class="form-control" placeholder="{{ ('Link with') }} http:// {{ ('or') }} https://" name="topbar_banner_link" value="{{ get_setting('topbar_banner_link') }}">
								</div>
							</div>
						</div>
					</div>
                    <div class="border-top pt-3">
                        <div class="form-group row">
							<label class="col-md-3 col-from-label">{{ ('Help line number')}}</label>
							<div class="col-md-8">
								<div class="form-group">
									<input type="hidden" name="types[]" value="helpline_number">
									<input type="text" class="form-control" placeholder="{{ ('Help line number') }}" name="helpline_number" value="{{ get_setting('helpline_number') }}">
								</div>
							</div>
						</div>
                    </div>

					{{-- <div class="border-top pt-3">
						<label class="">{{ ('Header Nav Menu')}}</label>
						<div class="header-nav-menu">
							<input type="hidden" name="types[]" value="header_menu_labels">
							<input type="hidden" name="types[]" value="header_menu_links">
							@if (get_setting('header_menu_labels') != null)
								@php
									$menu_labels = json_decode(get_setting('header_menu_labels'));
									$menu_links = json_decode(get_setting('header_menu_links'));
								@endphp
								@foreach (json_decode( get_setting('header_menu_labels'), true) as $key => $value)

									<div class="row gutters-5">
										<div class="col-4">
											<div class="form-group">
												<input type="text" class="form-control" placeholder="{{ ('Label')}}" name="header_menu_labels[]" value="{{ $value }}">

											</div>
										</div>
										<div class="col">
											<div class="form-group">
												<input type="text" class="form-control" placeholder="{{ ('Link with') }} http:// {{ ('or') }} https://" name="header_menu_links[]" value="{{ json_decode(App\Models\BusinessSetting::where('type', 'header_menu_links')->first()->value, true)[$key] }}">
											</div>
										</div>
										<div class="col-auto">
											<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
												<i class="las la-times"></i>
											</button>
										</div>
									</div>
								@endforeach
							@endif
						</div>
						<button
							type="button"
							class="btn btn-soft-secondary btn-sm"
							data-toggle="add-more"
							data-content='<div class="row gutters-5">
								<div class="col-4">
									<div class="form-group">
										<input type="text" class="form-control" placeholder="{{ ('Label')}}" name="header_menu_labels[]">
									</div>
								</div>
								<div class="col">
									<div class="form-group">
										<input type="text" class="form-control" placeholder="{{ ('Link with') }} http:// {{ ('or') }} https://" name="header_menu_links[]">
									</div>
								</div>
								<div class="col-auto">
									<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
										<i class="las la-times"></i>
									</button>
								</div>
							</div>'
							data-target=".header-nav-menu">
							{{ ('Add New') }}
						</button>
					</div> --}}

					<div class="text-right">
						<button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
					</div>
				</form>

				@php
					function submenu($v){
						$items .= '<ol class="dd-list">';
						foreach ($value["children"] as $key => $value) {
							$items .= '<li class="dd-item dd3-item" data-icon="' . @$value['icon'] . '" data-id="' . $value['id'] . '" data-label="' . $value['label'] . '" data-url="' . $value['url'] . '" data-background_color="'.@$value['background_color'].'" data-background_font_color="'.@$value['background_font_color'].'">
							<div class="dd-handle dd3-handle"> Drag</div>
							<div class="dd3-content">
								<span>' . $value['label'] . '</span>
								<div class="item-edit">Edit</div>
							</div>
							<div class="item-settings d-none">

								<p>
									<label for="">Navigation Label<br><input type="text" name="navigation_label" value="' . $value['label'] . '"></label>
								</p>
								<p>
									<label for="">Navigation Url<br><input type="text" name="navigation_url" value="' . $value['label'] . '"></label>
								</p>
								<p style="display:none !important">
									<label for="">Navigation Icon<br><input type="hidden" name="navigation_icon" value="' . @$value['icon'] . '"></label>
								</p>
								<p>
									<a class="item-delete" href="javascript:;">Remove</a> | <a class="item-close" href="javascript:;">Close</a>
								</p>
							</div>';

						}
						$items .= '</ol>';
						return $items;
					}
					$items = '';
					$menus = '';
                    $menusdata = get_setting('customs_menu_71');
                    if($menusdata && !empty($menusdata)){
                        $menus = $menusdata;
                    }

					$customs_menus = json_decode($menus, true);
                    //dd($customs_menus);
					if(is_array($customs_menus)):
						if (count($customs_menus) > 0) {
							$items .= '<ol class="dd-list">';
							foreach ($customs_menus as $key => $value) {
                                $check_lebel = @$value['background_color_status']=="on"?"checked":"";
                                $background_color_status_val = @$value['background_color_status']=="on"?"on":"off";
								$items .= '<li class="dd-item dd3-item pass_icon_val" data-icon="'. @$value['icon'] .'"  data-id="' . $value['id'] . '"  data-label="' . $value['label'] . '" data-url="' . $value['url'] . '" data-background_color="'.@$value['background_color'].'" data-background_font_color="'.@$value['background_font_color'].'" data-background_color_status="'.@$background_color_status_val.'">

										<div class="dd-handle dd3-handle"> Drag</div>
										<div class="dd3-content">
											<span class="label-'.$value['id'].'">' . $value['label'] . '</span>
											<div class="item-edit">Edit</div>
										</div>
										<div class="item-settings d-none">
                                            <p>
                                            <label for="">Menu Background </label><br><label class="aiz-switch aiz-switch-success mb-0"><input type="hidden" name="background_color_status" class="background_color_status_val" value="'.@$background_color_status_val.'"><input type="checkbox" name="show_background_color_status" class="change_background" '.@$check_lebel.'><span></span></label>
                                        </p>
                                            <p>
                                                <label for="">Menu Background Color<br><input type="color" name="background_color" value="' . @$value['background_color'] . '"></label>
                                            </p>
                                            <p>
                                                <label for="">Menu Background Font Color<br><input type="color" name="background_font_color" value="' . @$value['background_font_color'] . '"></label>
                                            </p>
											<p>
												<label for="">Navigation Label<br>
													<input type="text" name="navigation_label" data-id="'.$value['id'].'" value="' . $value['label'] . '">
												</label>
											</p>
											<p>
												<label for="">Navigation Url<br>
													<input type="text" name="navigation_url" value="' . $value['url'] . '">
												</label>
											</p>



											<div class="">
												<div class=" input-group " data-toggle="aizuploader" data-type="image">
													<div class="input-group-prepend">
														<div class="input-group-text bg-soft-secondary font-weight-medium">'. translate("Browse").'</div>
													</div>
													<div class="form-control file-amount">'. translate("Choose File").'</div>
													<input type="hidden" name="type" value="navigation_icon_2">
													<input type="hidden"  name="navigation_icon_2" class="edit_icon_get_val selected-files" value="'. @$value['icon'].'">
												</div>
												<div class="file-preview"></div>
											</div>

											<p>
												<a class="item-delete" href="javascript:;">Remove</a> |<a class="item-close" href="javascript:;">Close</a>
											</p>

										</div>';

									if(@$value["children"]){
										if (count($value["children"]) > 0) {
											$items .= '<ol class="dd-list">';
												foreach ($value["children"] as $key => $value) {
													$items .= '<li class="dd-item dd3-item item-'.$value['id'].'" data-icon="' . @$value['icon'] . '" data-id="' . $value['id'] . '" data-label="' . $value['label'] . '" data-url="' . $value['url'] . '" data-background_color="'.@$value['background_color'].'" data-background_font_color="'.@$value['background_font_color'].'">

														<div class="dd-handle dd3-handle"> Drag</div>
														<div class="dd3-content">
															<span class="label-'.$value['id'].'">' . $value['label'] . '</span>
															<div class="item-edit">Edit</div>
														</div>
														<div class="item-settings d-none">
															<p>
																<label for="">Navigation Label<br>
																	<input type="text" name="navigation_label" data-id="'.$value['id'].'" value="' . $value['label'] . '">
																</label>
															</p>
															<p>
																<label for="">Navigation Url<br><input type="text" name="navigation_url" data-id="'.$value['id'].'" value="' . $value['url'] . '"></label>
															</p>
															<p>
																<a class="item-delete" href="javascript:;">Remove</a> |<a class="item-close" href="javascript:;">Close</a>
															</p>
														</div>';

														if(@$value["children"]){
															if (count($value["children"]) > 0) {
																$items .= '<ol class="dd-list">';
																	foreach ($value["children"] as $key => $value) {
																		$items .= '<li class="dd-item dd3-item" data-icon="' . @$value['icon'] . '"  data-id="' . $value['id'] . '" data-label="' . $value['label'] . '" data-url="' . $value['url'] . '" data-background_color="'.@$value['background_color'].'" data-background_font_color="'.@$value['background_font_color'].'">

																			<div class="dd-handle dd3-handle"> Drag</div>
																			<div class="dd3-content">
																				<span class="label-'.$value['id'].'">' . $value['label'] . '</span>
																				<div class="item-edit">Edit</div>
																			</div>
																			<div class="item-settings d-none">
																				<p>
																					<label for="">Navigation Label<br>
																						<input type="text" name="navigation_label" data-id="'.$value['id'].'" value="' . $value['label'] . '">
																					</label>
																				</p>
																				<p>
																					<label for="">Navigation Url<br><input type="text" name="navigation_url" value="' . $value['url'] . '"></label>
																				</p>
																				<p>
																					<a class="item-delete" href="javascript:;">Remove</a> |<a class="item-close" href="javascript:;">Close</a>
																				</p>
																			</div>';

																			if(@$value["children"]){
																				if (count(@$value["children"]) > 0) {
																					$items .= '<ol class="dd-list">';
																						foreach (@$value["children"] as $key => $value) {
																							$items .= '<li class="dd-item dd3-item" data-icon="' . @$value['icon'] . '" data-id="' . $value['id'] . '" data-label="' . $value['label'] . '" data-url="' . $value['url'] . '" data-background_color="'.@$value['background_color'].'" data-background_font_color="'.@$value['background_font_color'].'">

																								<div class="dd-handle dd3-handle"> Drag</div>
																								<div class="dd3-content">
																									<span class="label-'.$value['id'].'">' . $value['label'] . '</span>
																									<div class="item-edit">Edit</div>
																								</div>
																								<div class="item-settings d-none">
																									<p>
																										<label for="">Navigation Label<br>
																											<input type="text" data-id="'.$value['id'].'" name="navigation_label" value="' . $value['label'] . '">
																										</label>
																									</p>
																									<p>
																										<label for="">Navigation Url<br><input type="text" name="navigation_url" value="' . $value['url'] . '"></label>
																									</p>
																									<p>
																										<a class="item-delete" href="javascript:;">Remove</a> |<a class="item-close" href="javascript:;">Close</a>
																									</p>
																								</div>';
																							$items .= '</li>';
																						}
																					$items .= '</ol>';
																				}
																			}
																		$items .= '</li>';
																	}
																$items .= '</ol>';
															}
														}

													$items .= '</li>';
												}
											$items .= '</ol>';
										}
									}

								$items .= '</li>';
							}
							$items .= '</ol>';
						}
					endif;
				@endphp

			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-8 mx-auto">
		<div class="card">
			<div class="card-header">
				<h6 class="mb-0">{{ ('Menu Setting') }}</h6>
			</div>
			<div class="card-body">
				<div class="row">
					<span class="message text-success col-12"></span>
					<div class="col-8">
						<div class="dd" id="nestable">
							{!! $items !!}
						</div>
					</div>
					<div class="col-4 pt-2 pl-0">
						<form id="add-item" enctype="multipart/form-data">
							<input type="text" name="name" class="form-control mb-2" placeholder="Name">
							<input type="text" name="url" class="form-control mb-2" placeholder="Url">
                            <br>
                            <label for="">Menu Background </label>
                            <br>
                            <label class="aiz-switch aiz-switch-success mb-0">
								<input type="hidden" name="background_color_status" value="off">
								<input type="checkbox" name="show_background_color_status" class="change_background">
								<span></span>
							</label>
                            <div class="menu_background_color d-none">
                                <br>
                            <label for="">Menu Background Color </label>
							<input type="color" name="background_color" class="form-control mb-2" placeholder="Menu Background Color">
                            <br>
                            <label for="">Menu Background Font Color </label>
							<input type="color" name="background_font_color" class="form-control mb-2" placeholder="Menu Background Font Color">
                            </div>


                            <div class="mb-2">
                                <div class=" input-group " data-toggle="aizuploader" data-type="image">
			                        <div class="input-group-prepend">
			                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ ('Browse') }}</div>
			                        </div>
			                        <div class="form-control file-amount">{{ ('Choose File') }}</div>
									<input type="hidden" name="type" value="changeIcon">
									<hr/>
			                        <input type="hidden" name="changeIcon" class="selected-files" value="{{ get_setting('changeIcon') }}">
			                    </div>
			                    <div class="file-preview"></div>
                            </div>

							<button type="submit" class="btn btn-primary btn-sm btn-block text-uppercase font-weight-bold">Add Item</button>
						</form>

						<form action="javascript:void(0)" method="post" class="mt-2">
							<input type="hidden" id="nestable-output" name="menu">
							<button type="submit" id="nst_menu" class="btn btn-info btn-sm btn-block text-uppercase font-weight-bold">Update Menu</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('script')
<script src="{{ static_asset('assets/js/nestable/jquery.nestable.js') }}"></script>
<script src="{{ static_asset('assets/js/nestable/script.nestable.js') }}"></script>
<script>
	$(document).ready(function(){

		$(document).on('click', '.change_background', function(e){
            if($(this).prop('checked')){
                $(this).parent().parent().find('.menu_background_color').removeClass('d-none');
                $(this).parent().find('input[name="background_color_status"]').val('on');
            }else{
                $(this).parent().parent().find('.menu_background_color').addClass('d-none');
                $(this).parent().find('input[name="background_color_status"]').val('off');
            }
        })
		$(document).on('click', '#nst_menu', function(e){
			e.preventDefault();
			//ajax setup start
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			//ajax setup end
			$.ajax({
				url: "{{ route('business_settings.updatenestate') }}",
				method: 'POST',
				data: {
					menu: $('#nestable-output').val()
				},
				success: function(data){
					if (data.status == 'success') {
						$('.message').html('<div class="alert alert-success alert-dismissible fade show">'+data.message+'</div>');
					}
				}
			});
		});
	})
</script>
@endsection

{{--
	<li class="dd-item dd3-item" data-id="1638591603511" data-label="ffffffffffff" data-url="ssss">
		<button class="dd-collapse" data-action="collapse" type="button">Collapse</button>
		<button class="dd-expand" data-action="expand" type="button">Expand</button>
		<div class="dd-handle dd3-handle"> Drag</div>
		<div class="dd3-content"><span>1</span><div class="item-edit">Edit</div></div>
		<div class="item-settings d-none">
			<p><label for="">Navigation Label<br><input type="text" name="navigation_label" value="ffffffffffff"></label></p><p><label for="">Navigation Url<br><input type="text" name="navigation_url" value="ssss"></label></p><p><a class="item-delete" href="javascript:;">Remove</a> |<a class="item-close" href="javascript:;">Close</a></p>
		</div>
		<ol class="dd-list">
			<li class="dd-item dd3-item" data-id="1638591621623" data-label="ffffffffffff" data-url="fffffffffff">
				<button class="dd-collapse" data-action="collapse" type="button">Collapse</button>
				<button class="dd-expand" data-action="expand" type="button">Expand</button>
				<div class="dd-handle dd3-handle"> Drag</div>
				<div class="dd3-content"><span>2</span><div class="item-edit">Edit</div></div>
				<div class="item-settings d-none">
					<p><label for="">Navigation Label<br><input type="text" name="navigation_label" value="ffffffffffff"></label></p><p><label for="">Navigation Url<br><input type="text" name="navigation_url" value="fffffffffff"></label></p><p><a class="item-delete" href="javascript:;">Remove</a> |<a class="item-close" href="javascript:;">Close</a></p>
				</div>
				<ol class="dd-list">
					<li class="dd-item dd3-item" data-id="1638592614327" data-label="ssssss" data-url="ssss">
						<button class="dd-collapse" data-action="collapse" type="button">Collapse</button>
						<button class="dd-expand" data-action="expand" type="button">Expand</button>
						<div class="dd-handle dd3-handle"> Drag</div>
						<div class="dd3-content"><span>3</span><div class="item-edit">Edit</div></div>
						<div class="item-settings d-none"><p><label for="">Navigation Label<br><input type="text" name="navigation_label" value="ssssss"></label></p><p><label for="">Navigation Url<br><input type="text" name="navigation_url" value="ssss"></label></p><p><a class="item-delete" href="javascript:;">Remove</a> |<a class="item-close" href="javascript:;">Close</a></p>
						</div>
						<ol class="dd-list">
							<li class="dd-item dd3-item" data-id="1638592649495" data-label="4" data-url="44">
								<div class="dd-handle dd3-handle"> Drag</div>
								<div class="dd3-content"><span>4</span>
									<div class="item-edit">Edit</div>
								</div>
								<div class="item-settings d-none">
									<p><label for="">Navigation Label<br><input type="text" name="navigation_label" value="4"></label></p><p><label for="">Navigation Url<br><input type="text" name="navigation_url" value="44"></label></p><p><a class="item-delete" href="javascript:;">Remove</a> |<a class="item-close" href="javascript:;">Close</a></p>
								</div>
							</li>
						</ol>
					</li>
				</ol>
			</li>
		</ol>
	</li>

--}}
