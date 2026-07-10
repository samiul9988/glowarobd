@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{ ('General Settings')}}</h1>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('System Name')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="site_name">
                                <input type="text" name="site_name" class="form-control" value="{{ get_setting('site_name') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('Site Url')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="site_url">
                                <input type="text" name="site_url" class="form-control" value="{{ get_setting('site_url', config('app.url')) }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">
                                File URL
                                @include('components.tooltip', [
                                    'title' => 'This will use for files or images base URL',
                                ])
                            </label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="file_url">
                                <input type="text" name="file_url" class="form-control" value="{{ get_setting('file_url', config('app.url')) }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">
                                Video File Driver
                                @include('components.tooltip', [
                                    'title' => 'Select the storage driver for video files. Choose "Local" to store videos on the local server or "Amazon S3" to use Amazon S3 cloud storage.',
                                ])
                            </label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="video_file_driver">
                                <select name="video_file_driver" id="video_file_driver" class="form-control aiz-selectpicker">
                                    <option value="local" @if (get_setting('video_file_driver', 'local') == 'local') selected @endif>Local</option>
                                    <option value="s3" @if (get_setting('video_file_driver', 'local') == 's3') selected @endif>Amazon S3</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">
                                Video URL
                                @include('components.tooltip', [
                                    'title' => 'This will show the base URL according to the selected video file driver',
                                ])
                            </label>
                            <div class="col-sm-9">
                                <input type="text" id="video_url" class="form-control" value="{{ get_setting('video_url', config('app.url')) }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('System Logo - White')}}</label>
                            <div class="col-sm-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose Files') }}</div>
                                    <input type="hidden" name="types[]" value="system_logo_white">
                                    <input type="hidden" name="system_logo_white" value="{{ get_setting('system_logo_white') }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm"></div>
                                <small>{{ ('Will be used in admin panel side menu') }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('System Logo - Black')}}</label>
                            <div class="col-sm-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose Files') }}</div>
                                    <input type="hidden" name="types[]" value="system_logo_black">
                                    <input type="hidden" name="system_logo_black" value="{{ get_setting('system_logo_black') }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm"></div>
                                <small>{{ ('Will be used in admin panel topbar in mobile + Admin login page') }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('System Timezone')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="timezone">
                                <select name="timezone" class="form-control aiz-selectpicker" data-live-search="true">
                                    @foreach (timezones() as $key => $value)
                                        <option value="{{ $value }}" @if (app_timezone() == $value)
                                            selected
                                        @endif>{{ $key }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{ ('Admin login page background')}}</label>
                            <div class="col-sm-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose Files') }}</div>
                                    <input type="hidden" name="types[]" value="admin_login_background">
                                    <input type="hidden" name="admin_login_background" value="{{ get_setting('admin_login_background') }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">User Login Page Banner</label>
                            <div class="col-sm-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose Files') }}</div>
                                    <input type="hidden" name="types[]" value="user_login_banner">
                                    <input type="hidden" name="user_login_banner" value="{{ get_setting('user_login_banner') }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">User Registration Page Banner</label>
                            <div class="col-sm-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose Files') }}</div>
                                    <input type="hidden" name="types[]" value="user_registration_banner">
                                    <input type="hidden" name="user_registration_banner" value="{{ get_setting('user_registration_banner') }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm"></div>
                            </div>
                        </div>
                        <div class="text-right">
    						<button type="submit" class="btn btn-primary">{{ ('Update') }}</button>
    					</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $(document).ready(function () {
            // Initialize on page load
            setVideoUrlBasedOnDriver($('#video_file_driver').val());

            // Handle change event
            $('#video_file_driver').on('change', function() {
                var driver = $(this).val();
                setVideoUrlBasedOnDriver(driver);
            });

            function setVideoUrlBasedOnDriver(driver) {
                var videoUrl = '';

                switch(driver) {
                    case 'local':
                        videoUrl = "{{ url('/') }}"; // or env('APP_URL')
                        break;
                    case 's3':
                        videoUrl = "{{ config('filesystems.disks.s3.url') }}";
                        break;
                    default:
                        videoUrl = '';
                }

                $('#video_url').val(videoUrl);
            }
        });
    </script>
@endsection
