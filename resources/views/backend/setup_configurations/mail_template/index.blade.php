@extends('backend.layouts.app')

@section('content')


<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ ('Mail Template')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>{{ ('Type')}}</th>
                    <th data-breakpoints="lg">{{ ('Subject')}}</th>
                    <th data-breakpoints="lg">{{ ('Status')}}</th>
                    <th class="text-right" width="15%">{{ ('Action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mail_templates as $mail_template)
                    <tr>
                        <td>{{$loop->index + 1}}</td>
                        <td>{{$mail_template->type}}</td>
                        <td>{{$mail_template->subject}}</td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                            <input onchange="update_status(this)" value="{{$mail_template->id}}" type="checkbox" {{$mail_template->status == 1 ? 'checked' : ''}}>
                            <span class="slider round"></span></label>
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('mail_template.edit', $mail_template->id)}}" title="{{ ('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function update_status(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('mail_template.update_status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    // location.reload();
                    AIZ.plugins.notify('success', '{{ ('Template status updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ ('Something went wrong') }}');
                }
            });
        }
    </script>
@endsection
