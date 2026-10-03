<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @if ($favicon)
        <link rel="icon" type="image/png" href="{{ $favicon }}">
    @endif
    <style>
        html,
        body {
            margin: 0;
            height: 100%;
            background: #525659;
            font-family: ui-sans-serif, system-ui, sans-serif;
        }

        .loading {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e5e7eb;
            font-size: 14px;
        }

        iframe {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
    </style>
</head>

<body>
    {{-- Pembungkus agar tab PDF punya title & favicon entity; PDF dimuat dari ?raw=1. --}}
    <div class="loading">Menyiapkan PDF...</div>
    <iframe src="{{ $src }}" title="{{ $title }}"></iframe>
</body>

</html>
