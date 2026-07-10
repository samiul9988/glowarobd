@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h6 class="fw-600 mb-0">{{ ('Global SEO') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Meta Title') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="meta_title">
                                <input type="text" class="form-control" placeholder="{{ ('Title') }}"
                                    name="meta_title" value="{{ get_setting('meta_title') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Meta description') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="meta_description">
                                <textarea class="resize-off form-control" placeholder="{{ ('Description') }}" name="meta_description">{{ get_setting('meta_description') }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Keywords') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="meta_keywords">
                                <textarea class="resize-off form-control" placeholder="{{ ('Keyword, Keyword') }}" name="meta_keywords">{{ get_setting('meta_keywords') }}</textarea>
                                <small class="text-muted">{{ ('Separate with coma') }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Meta Author') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="meta_author">
                                <input type="text" class="form-control" placeholder="{{ ('Author') }}"
                                    name="meta_author" value="{{ get_setting('meta_author', env('APP_NAME')) }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Meta Image') }}</label>
                            <div class="col-md-8">
                                <div class="input-group " data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="types[]" value="meta_image">
                                    <input type="hidden" name="meta_image" value="{{ get_setting('meta_image') }}"
                                        class="selected-files">
                                </div>
                                <div class="file-preview box"></div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Open Graph Title') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="og_title">
                                <input type="text" class="form-control" placeholder="{{ ('Title') }}"
                                    name="og_title" value="{{ get_setting('og_title') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Open Graph Description') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="og_description">
                                <textarea class="resize-off form-control" placeholder="{{ ('Description') }}" name="og_description">{{ get_setting('og_description') }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Open Graph Image') }}</label>
                            <div class="col-md-8">
                                <div class="input-group " data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="types[]" value="og_image">
                                    <input type="hidden" name="og_image" value="{{ get_setting('og_image') }}"
                                        class="selected-files">
                                </div>
                                <div class="file-preview box"></div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Twitter Title') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="twitter_title">
                                <input type="text" class="form-control" placeholder="{{ ('Title') }}"
                                    name="twitter_title" value="{{ get_setting('twitter_title') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Twitter Description') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="twitter_description">
                                <textarea class="resize-off form-control" placeholder="{{ ('Description') }}" name="twitter_description">{{ get_setting('twitter_description') }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ ('Twitter Image') }}</label>
                            <div class="col-md-8">
                                <div class="input-group " data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ ('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                    <input type="hidden" name="types[]" value="twitter_image">
                                    <input type="hidden" name="twitter_image"
                                        value="{{ get_setting('twitter_image') }}" class="selected-files">
                                </div>
                                <div class="file-preview box"></div>
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
    <script></script>
@endsection
