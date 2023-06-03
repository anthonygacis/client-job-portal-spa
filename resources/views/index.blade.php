<!doctype html>
<html lang="en">
<head>
    <title>Job Portal</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="api-url" content="{{ config('app.url') }}">
    <link href="{{ asset('logo-circle.svg') }}" rel="icon" type="image/svg+xml"/>
    <style>
        @media print {
            body {
                height: 0 !important;
                min-height: 0 !important;
            }
        }
    </style>
</head>
<body>
<div id="app" class="page"></div>
<div id="external"></div>
@vite('resources/js/app.js')
</body>
</html>
