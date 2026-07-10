<style>
    /* Video Preview Card */
    .video-preview-wrapper {
        background: #f9fafc;
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .video-container {
        position: relative;
        width: 100%;
        overflow: hidden;
        border-radius: 0.75rem;
        background: #000;
        aspect-ratio: 16/9;
        /* modern browsers handle this */
    }

    /* Fallback for aspect-ratio (Bootstrap 4 compatible) */
    @supports not (aspect-ratio: 16/9) {
        .video-container::before {
            content: "";
            display: block;
            padding-top: 56.25%;
            /* 16:9 ratio */
        }

        .video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    }

    .video-container video {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 0.75rem;
    }

    .video-meta {
        margin-top: 1rem;
        text-align: left;
    }

    .video-meta label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #555;
    }

    .video-meta p {
        margin-bottom: 0.5rem;
        color: #666;
        word-break: break-all;
    }

    @media (max-width: 991.98px) {
        .video-preview-wrapper {
            margin-top: 2rem;
        }
    }
</style>
@php
    $playlists = Cache::remember('filter_video_playlists', now()->addHours(6), function () {
        return \App\Models\VideoPlaylist::pluck('name', 'id');
    });
    $products = Cache::remember('crm_products', now()->addHours(3), function () {
        return App\Models\Product::published()->pluck('name', 'id');
    });
@endphp
<form id="videoCreateForm" method="post" action="" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="video_id" id="video_id" value="">
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="row gutters-5">
                <!-- Form Fields -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="Enter a title"
                            value="{{ old('title', @$video->title) }}" required>
                        <span class="text-danger error" id="title_error">
                            @error('title')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control"
                            placeholder="Slug generates automatically" value="{{ old('slug', @$video->slug) }}">
                        <span class="text-danger error" id="slug_error">
                            @error('slug')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="4" placeholder="Enter description here">{{ old('description', @$video->description) }}</textarea>
                        <span class="text-danger error" id="description_error">
                            @error('description')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="playlist">Category <span class="text-danger">*</span></label>
                        <select class="form-control aiz-selectpicker" data-live-search="true" data-multiple="true" name="playlists[]" id="playlists" multiple required>
                            <option value="" disabled>Select Categories</option>
                            @foreach ($playlists as $id => $name)
                                <option value="{{ $id }}" {{ in_array($id, @$selectedPlaylists ?? []) ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger error" id="playlists_error">
                            @error('playlists')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="products">Product</label>
                        <select class="form-control aiz-selectpicker" data-live-search="true" data-multiple="true" name="products[]" id="products" multiple>
                            <option value="" disabled>Select Products</option>
                            @foreach ($products as $id => $name)
                                <option value="{{ $id }}" {{ in_array($id, @$selectedProducts ?? []) ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger error" id="products_error">
                            @error('products')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>

                <div class="col-md-6" id="video-upload-section">
                    <div class="form-group">
                        <label for="video">Video <span class="text-danger">*</span></label>
                        <div id="aiz-video-uploader" class="input-group" data-toggle="aizuploader" data-type="video" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary">Browse</div>
                            </div>
                            <div class="form-control file-amount">Choose File</div>
                            <input type="hidden" name="attachment" id="video" class="selected-files" value="{{ @$videoFile->id }}">
                        </div>
                        <div class="file-preview box sm"></div>
                        <span class="text-danger error" id="video_error">
                            @error('video')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="thumbnail">Thumbnail <span class="text-danger">*</span></label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary">Browse</div>
                            </div>
                            <div class="form-control file-amount">Choose Image</div>
                            <input type="hidden" name="thumbnail" id="thumbnail" class="selected-files" value="{{ old('thumbnail', @$video->thumbnail) }}">
                        </div>
                        <div class="file-preview box sm"></div>
                        <span class="text-danger error" id="thumbnail_error">
                            @error('thumbnail')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select class="form-control" name="type" id="type">
                            <option value="default" {{ @$video?->type == 'default' ? 'selected' : '' }}>Default</option>
                            <option value="reel" {{ @$video?->type == 'reel' ? 'selected' : '' }}>Reels</option>
                        </select>
                        <span class="text-danger error" id="type_error">
                            @error('type')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" name="status" id="status">
                            <option value="1" {{ @$video?->status == 1 ? 'selected' : '' }}>Publish</option>
                            <option value="0" {{ @$video?->status == 0 ? 'selected' : '' }}>Draft</option>
                        </select>
                        <span class="text-danger error" id="status_error">
                            @error('status')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <div class="form-group row">
                        <div class="col-md-2">
                            <label for="is_featured" class="col-from-label">Featured ?</label>
                        </div>
                        <div class="col-md-10">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" id="is_featured" name="featured" value="1" onchange="this.value = this.checked ? 1 : 0" {{ (@$video && $video->featured == 1) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Preview -->
        <div class="col-lg-4 col-md-12 mb-md-4">
            <div class="video-preview-wrapper">
                <div class="video-container">
                    <div class="video-loader" id="video_loader"></div>
                    <video id="video_preview" controls preload="metadata" src="{{ @$videoFile->full_url ?? '' }}">
                    </video>
                </div>

                <div class="video-meta">
                    <label>Title</label>
                    <p id="video_title">{{ @$videoFile->file_original_name ?? 'No video selected' }}</p>

                    <label>URL</label>
                    <p id="video_url">{{ @$videoFile->full_url ?? 'No URL Available' }}</p>
                </div>
            </div>
        </div>
    </div>
</form>
