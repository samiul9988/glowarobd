@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ ('Mail Template')}} / <span style="font-size: 13px;">{{$mail_template->type}}</span></h5>
    </div>
    <div class="card-body">
        <form action="{{route('mail_template.update')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="">Mail Type</label>
                <input type="text" disabled value="{{$mail_template->type}}" class="form-control">
                <input type="hidden" name="id" value="{{$mail_template->id}}">
            </div>
            <div class="form-group">
                <label for="">Mail Subject<span class="text-danger">*</span> </label>
                <input type="text" name="subject" value="{{$mail_template->subject}}" class="form-control" required>
            </div>
            <div class="form-group">
                <div class="d-flex justify-content-between mb-1">
                    <label for="">Body<span class="text-danger">*</span> </label>
                    <button type="button" class="btn btn-sm btn-primary view_sample_code" data-toggle="modal" data-target="#sample_view_code" title="Sample code view">
                        <i class="las la-eye"></i>
                    </button>
                </div>
                <textarea name="content" class="form-control mail_content" required>{{$mail_template->content}}</textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-info">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- sample code view modal --}}
<div class="modal fade" id="sample_view_code" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Short code & Sample template</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="modal-body">

            {{-- short code variable --}}
            <div class="card">
                <div class="card-header">
                    <h6>Short Code Variable</h6>
                </div>
                <div class="card-body">
                    @if(is_iterable($data = json_decode($mail_template->code, true)))
                        @foreach ($data as $key => $value)
                            <div class="d-flex justify-content-between mt-3">
                                <div>
                                    <h6 style="border-left:3px solid rgb(150, 150, 153); padding: 5px 0 5px 10px;">{{$value}}</h6>
                                </div>
                                <p> <?= "{{" .$key. "}}" ?></p>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- sample code --}}
             <div class="card">
                <div class="card-header">
                    <h6>Sample template</h6>
                </div>
                <div class="card-body">
                    <div class="text-right">
                        <span type="button" class="copy_code">
                            <i title="Copy" class="las la-copy"></i>
                        </span>
                    </div>
                    <div class="content_body">
                        {{ $mail_template->sample_content; }}
                    </div>
                </div>
            </div>
        </div>


        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>

    </div>
  </div>
</div>

@endsection


@section('script')
    <script type="text/javascript">
        $(document).ready(function() {

            $('.mail_content').summernote();


            $('.copy_code').click(function(){
                if (navigator.clipboard) {
                const textToCopy = $('.content_body').text();

                navigator.clipboard.writeText(textToCopy)
                    .then(() => {
                        $(this).html('<i title="Copied" class="las la-check-double"></i>')
                    })
                    .catch((error) => {
                    console.error('Failed to copy text: ', error);
                    });
                } else {
                    console.log('Do copy manually');
                }

            })


        });
    </script>
@endsection

@push('cus_css')
    <style>
        .copy_code i{
            font-size: 16px;
            background: #777;
            padding: 5px;
            border-radius: 3px;
        }
        .view_sample_code i{
            font-size: 16px;
        }
    </style>
@endpush
