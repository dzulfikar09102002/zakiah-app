@php
    $status = (int) ($status ?? 500);
    $titles = [
        400 => 'Permintaan Tidak Valid',
        401 => 'Silakan Login Dulu',
        403 => 'Akses Ditolak',
        404 => 'Halaman Tidak Ditemukan',
        405 => 'Aksi Tidak Didukung',
        408 => 'Waktu Habis',
        413 => 'File Terlalu Besar',
        419 => 'Sesi Kedaluwarsa',
        429 => 'Terlalu Banyak Permintaan',
        500 => 'Terjadi Kesalahan',
        502 => 'Server Tidak Merespons',
        503 => 'Sedang Perbaikan',
        504 => 'Server Terlalu Lama',
    ];
    $title = $titles[$status] ?? ($status < 500 ? $titles[400] : $titles[500]);
    $description = $message ?? ($status === 503
        ? 'Aplikasi sedang dalam perbaikan. Silakan kembali beberapa saat lagi.'
        : 'Silakan kembali atau muat ulang halaman ini beberapa saat lagi.');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    {{-- Sengaja tanpa Vite: halaman ini dipakai ketika aplikasi sendiri sedang bermasalah. --}}
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100dvh; display: grid; place-items: center; padding: 16px;
            font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: #1f2a3c;
            /* Palet perpaduan Zakiah (navy, emas) & Secaca (teal): halaman ini tidak tahu entity user. */
            background: radial-gradient(ellipse at top, #e6eef3, #f3efe7 55%, #e7dccb);
        }
        .card {
            position: relative; width: 100%; max-width: 420px; overflow: hidden; text-align: center;
            padding: 32px 24px; border-radius: 24px; border: 1px solid rgba(255,255,255,.8);
            background: rgba(255,253,249,.85); box-shadow: 0 24px 48px -18px rgba(31,42,60,.35);
        }
        .card::before {
            content: ''; position: absolute; inset: 0 0 auto; height: 4px;
            background: linear-gradient(90deg, #2f3e56, #c9a066, #1f7f8c, #e5545a);
        }
        .orb { width: 112px; height: 112px; margin: 0 auto 8px; position: relative; animation: bob 4s ease-in-out infinite; }
        .orb span { position: absolute; inset: 0; border-radius: 50%; border: 2px solid #1f7f8c; animation: ring 2.6s ease-out infinite; }
        .orb span + span { animation-delay: 1.3s; }
        .orb b { position: absolute; inset: 20px; border-radius: 50%; background: #c9a06640; display: grid; place-items: center; font-size: 34px; color: #2f3e56; }
        .code { display: flex; justify-content: center; font-size: 56px; font-weight: 800; color: #2f3e56; margin: 8px 0 4px; }
        .code i { font-style: normal; display: inline-block; animation: drop .75s cubic-bezier(.34,1.56,.64,1) both; }
        .code i:nth-child(2) { animation-delay: .12s; }
        .code i:nth-child(3) { animation-delay: .24s; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        p { margin: 0 0 24px; color: #5f6b80; font-size: 14px; line-height: 1.6; }
        .actions { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
        a, button {
            min-height: 44px; padding: 0 16px; border-radius: 12px; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;
            display: inline-flex; align-items: center; text-decoration: none; border: 1px solid rgba(255,255,255,.8);
        }
        .primary { background: linear-gradient(90deg, #2f3e56, #1f7f8c); color: #fff; border-color: transparent; }
        .secondary { background: rgba(255,255,255,.7); color: #1f2a3c; }
        @keyframes bob { 50% { transform: translateY(-8px); } }
        @keyframes ring { from { opacity: .55; transform: scale(.7); } to { opacity: 0; transform: scale(1.35); } }
        @keyframes drop {
            0% { opacity: 0; transform: translateY(-42px) rotate(-8deg) scale(.8); }
            60% { opacity: 1; transform: translateY(6px) rotate(2deg) scale(1.04); }
            100% { opacity: 1; transform: none; }
        }
        @media (prefers-reduced-motion: reduce) { .orb, .orb span, .code i { animation: none; } }
    </style>
</head>
<body>
    <main class="card">
        <div class="orb" aria-hidden="true"><span></span><span></span><b>{{ $status >= 500 ? '!' : '?' }}</b></div>
        <div class="code" aria-label="Kode {{ $status }}">
            @foreach (str_split((string) $status) as $digit)
                <i aria-hidden="true">{{ $digit }}</i>
            @endforeach
        </div>
        <h1>{{ $title }}</h1>
        <p>{{ $description }}</p>
        <div class="actions">
            <button type="button" class="secondary" onclick="history.back()">Kembali</button>
            <button type="button" class="primary" onclick="location.reload()">Muat Ulang</button>
        </div>
    </main>
</body>
</html>
