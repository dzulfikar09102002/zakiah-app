<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    {{-- PWA: manifest, warna status bar, dan dukungan "Add to Home Screen" di iOS --}}
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#cc7455">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Zakiah">
    <link rel="apple-touch-icon" href="/pwa/apple-touch-icon.png?v=2">

    {{-- Splash statis iOS saat app PWA dibuka (sama dengan frame pertama partials/splash).
         [lebar, tinggi, pixel ratio] layar iPhone dalam px CSS, portrait. --}}
    @foreach ([[440, 956, 3], [430, 932, 3], [428, 926, 3], [414, 896, 3], [414, 896, 2], [414, 736, 3], [402, 874, 3], [393, 852, 3], [390, 844, 3], [375, 812, 3], [375, 667, 2], [320, 568, 2]] as [$w, $h, $dpr])
        @foreach (['light', 'dark'] as $scheme)
            <link rel="apple-touch-startup-image"
                media="(device-width: {{ $w }}px) and (device-height: {{ $h }}px) and (-webkit-device-pixel-ratio: {{ $dpr }}) and (orientation: portrait) and (prefers-color-scheme: {{ $scheme }})"
                href="/pwa/splash/iphone-{{ $w * $dpr }}x{{ $h * $dpr }}-{{ $scheme }}.png">
        @endforeach
    @endforeach

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function () {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }

            // Splash (partials/splash): saat app PWA dibuka atau pertama kali di HP, sekali per sesi.
            try {
                const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
                const phone = window.matchMedia('(max-width: 767px)').matches;

                if ((standalone || phone) && !sessionStorage.getItem('splash-shown')) {
                    document.documentElement.classList.add('show-splash');

                    // iOS PWA sudah menampilkan gambar splash statis dengan logo di posisi
                    // yang sama, jadi logo langsung tampil (tanpa animasi muncul) agar menyambung.
                    if (standalone && /iphone|ipad|ipod/i.test(navigator.userAgent)) {
                        document.documentElement.classList.add('sp-static-start');
                    }
                }
            } catch (e) {}
        })();
    </script>

    {{-- Inline style to set the HTML background color based on our theme in app.css --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    @php
        $branding = $page['props']['branding'] ?? null;
    @endphp
    <title inertia>{{ ($branding['name'] ?? config('app.name', 'Laravel')) . ' Backoffice' }}</title>

    {{-- Favicon = logo entity (sama dengan kop PDF); diperbarui di app.tsx saat navigasi. --}}
    <link id="app-favicon" rel="icon" type="image/png" href="{{ $branding['logo'] ?? '/favicon.ico' }}">

    {{-- Font disamakan dengan NewZakicaPOS (Views/Shared/_LayoutAuth.cshtml) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Lato:wght@300;400;700&family=Playfair+Display:ital,wght@0,600;1,400;1,600&display=swap" rel="stylesheet">

    @viteReactRefresh
    @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @include('partials.splash')
    @inertia
</body>

</html>