@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ ('Blog Information')}}</h5>
            </div>
            <div class="card-body">
                <form id="add_form" class="form-horizontal" action="{{ route('blog.update',$blog->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            {{ ('Blog Title')}}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{ ('Blog Title')}}" onkeyup="makeSlug(this.value)" id="title" name="title" value="{{ $blog->title }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row" id="category">
                        <label class="col-md-3 col-from-label">
                            {{ ('Category')}}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <select
                                class="form-control aiz-selectpicker"
                                name="category_id"
                                id="category_id"
                                data-live-search="true"
                                required
                                @if($blog->category != null)
                                data-selected="{{ $blog->category->id }}"
                                @endif
                            >
                                <option>--</option>
                                @foreach ($blog_categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->category_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Slug')}}</label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{ ('Slug')}}" name="slug" id="slug" value="{{ $blog->slug }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label class="col-from-label">{{ ('Rewrite Url')}}</label>
                        </div>
                        <div class="col-sm-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input value="1" id="rewrite_url" name="rewrite_url" type="checkbox">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">
                            {{ ('Banner')}}
                            <small>(1300x650)</small>
                        </label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                        {{ ('Browse')}}
                                    </div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="banner" class="selected-files" value="{{ $blog->banner }}">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            {{ ('Short Description')}}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <textarea name="short_description" rows="5" class="form-control">{{ $blog->short_description }}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">
                            {{ ('Description')}}
                        </label>
                        <div class="col-md-9">
                            <textarea class="aiz-text-editor" name="description">{{ $blog->description }}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Meta Title')}}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="meta_title" value="{{ $blog->meta_title }}" placeholder="{{ ('Meta Title')}}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">
                            {{ ('Meta Image')}}
                            <small>(200x200)+</small>
                        </label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                        {{ ('Browse')}}
                                    </div>
                                </div>
                                <div class="form-control file-amount">{{ ('Choose File') }}</div>
                                <input type="hidden" name="meta_img" class="selected-files" value="{{ $blog->meta_img }}">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ ('Meta Description')}}</label>
                        <div class="col-md-9">
                            <textarea name="meta_description" rows="5" class="form-control">{{ $blog->meta_description }}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            {{ ('Meta Keywords')}}
                        </label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ $blog->meta_keywords }}" placeholder="{{ ('Meta Keywords')}}">
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">
                            {{ ('Save')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    let old_slug = "{{ $blog->slug }}";

    $('#slug').on('input', function() {
        var slug = $(this).val();
        if (old_slug != slug) {
            $('#rewrite_url').prop('checked', true);
        }else{
            $('#rewrite_url').prop('checked', false);
        }
    });
    function makeSlug(val) {
        let str = val;
        let output = str.replace(/\s+/g, '-').toLowerCase();
        $('#slug').val(output);
    }
</script>
@endsection
