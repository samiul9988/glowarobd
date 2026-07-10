<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'label';
            src: url("{{ static_asset('assets/fonts/label.ttf') }}") format('truetype');
        }

        @page {
            size: 38mm 25mm;
            margin: 0;
        }

        body {
            font-family: 'label', sans-serif;
            font-size: 8pt;
            margin: 0;
            padding: 0;
        }

        .sticker {
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            page-break-after: always; /* Force each sticker on its own page */
        }

        .sticker:last-child {
            page-break-after: avoid; /* Prevent extra blank page at the end */
        }
    </style>
</head>
<body>
    @foreach ($renderTemplates as $renderTemplate)
        <div class="sticker">
            {!! $renderTemplate !!}
        </div>
    @endforeach
</body>
</html>
