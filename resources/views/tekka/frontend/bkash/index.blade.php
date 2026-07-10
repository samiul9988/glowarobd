@extends(config('app.theme').'frontend.layouts.app')

@section('content')

    {{-- <button id="bKash_button" class="d-none">Pay With bKash</button> --}}
    @include('bkash::payment')

@endsection

@section('script')
<script>
  window.onload = function () {
      document.getElementById("bKash_button").click(); 
  };
</script>
@endsection
