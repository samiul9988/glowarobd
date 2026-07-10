
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <link rel="stylesheet" href="{{ static_asset('assets/css/vendors.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/css/aiz-core.css') }}">
    <link rel="stylesheet" href="{{ static_asset("assets/".str_replace('.','',config('app.theme'))."/frontend/css/custom-style.css") }}">
</head>
<body>
  @include('bkash::payment')
  <script>
    window.onload = function () {
        document.getElementById("bKash_button").click(); 
    };
  </script>
</body>
</html>
