@extends('backend.layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ 'Edit Template' }}</h5>
        </div>
        <div class="card-body">
            <form id="create-template-form" action="{{ route('templates.update', $template->id) }}" method="post"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('backend.templates.fields', [
                    'template' => $template,
                ])
                <div class="form-group text-right">
                    <button type="button" class="btn btn-success" id="create-btn">Update</button>
                </div>
            </form>
        </div>
    </div>

    @include('backend.templates.modal')
@endsection


@section('script')
    @include('backend.templates.scripts')
@endsection
