<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    {{-- <meta charset="utf-8"> --}}
    <style>
        @font-face {
            font-family: 'label';
            src: url("{{ static_asset('assets/fonts/label.ttf') }}") format('truetype');
        }
        @page {
            size: 80mm 140mm;
            margin: 2mm;
        }

        body {
            font-size: 8pt;
            margin: 0;
        }

        .container {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        @foreach ($renderTemplates as $renderTemplate)
            {!! $renderTemplate !!}
        @endforeach
    </div>
</body>
</html>
