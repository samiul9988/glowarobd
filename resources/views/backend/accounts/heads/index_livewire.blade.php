@extends('backend.layouts.app')

@section('content')
    @livewire('accounts.heads')
@endsection

@push('js')
<script type="text/javascript">

$(document).ready(function () {
    $('.aiz-selectpicker').select2();
});

</script>
@endpush 