{{--
    Splash screen saat aplikasi dibuka (PWA / HP), sekali per sesi.
    Cairan terakota naik dari bawah memenuhi layar; logo Zakiah & Secaca
    berubah putih saat terendam. Ditutup oleh window.__hideSplash()
    (dipanggil dari resources/js/app.tsx setelah React selesai render).
    Ditampilkan hanya bila <html> punya class "show-splash" (lihat app.blade.php).
--}}
<style>
    #app-splash { display: none; }

    .show-splash #app-splash {
        --sp-bg: #fff1ea;
        --sp-ink: #cc7455;
        --sp-liquid-top: #d98260;
        --sp-liquid-bottom: #9b462c;
        --sp-rise: 1.7s;
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        display: block;
        overflow: hidden;
        background: var(--sp-bg);
        transition: opacity .45s ease, transform .55s cubic-bezier(.4, 0, .2, 1);
    }

    .dark.show-splash #app-splash { --sp-bg: #0e0705; --sp-ink: #e0997c; }

    .show-splash body { overflow: hidden; }

    #app-splash.sp-done { opacity: 0; transform: scale(1.06); pointer-events: none; }

    .sp-stage {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 22px;
        color: var(--sp-ink);
    }

    .sp-logos { display: flex; align-items: center; gap: 22px; }

    .sp-logo {
        display: block;
        background: currentColor;
        -webkit-mask: var(--sp-src) center / contain no-repeat;
        mask: var(--sp-src) center / contain no-repeat;
        opacity: 0;
        animation: sp-pop .7s cubic-bezier(.34, 1.56, .64, 1) forwards;
    }

    .sp-logo--zakiah { --sp-src: url('/assets/images/zakiah-mark.png'); width: 86px; height: 62px; animation-delay: .1s; }
    .sp-logo--secaca { --sp-src: url('/assets/images/secaca.png'); width: 92px; height: 62px; animation-delay: .25s; }

    .sp-divider {
        width: 1.5px;
        height: 48px;
        border-radius: 2px;
        background: currentColor;
        opacity: .35;
        transform: scaleY(0);
        animation: sp-grow .5s ease-out .2s forwards;
    }

    .sp-caption {
        font-family: 'Lato', 'Instrument Sans', system-ui, sans-serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .38em;
        text-transform: uppercase;
        padding-left: .38em;
        opacity: 0;
        animation: sp-fade .6s ease-out .45s forwards;
    }

    /* Cairan: tinggi naik dari 0 sampai melewati layar. */
    .sp-liquid {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 0;
        animation: sp-rise var(--sp-rise) cubic-bezier(.6, .05, .3, 1) .35s forwards;
    }

    .sp-fill {
        position: absolute;
        inset: 0;
        overflow: hidden;
        background: linear-gradient(to bottom, var(--sp-liquid-top), var(--sp-liquid-bottom) 70vh);
    }

    /* Lapisan logo putih, sejajar dengan lapisan dasar, hanya terlihat di dalam cairan. */
    .sp-fill .sp-stage { top: auto; height: 100vh; height: 100dvh; color: #fff; }

    .sp-waves { position: absolute; left: 0; right: 0; bottom: 100%; height: 34px; overflow: hidden; margin-bottom: -1px; }

    .sp-wave {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 200%;
        height: 100%;
        animation: sp-wave 2.4s linear infinite;
    }

    .sp-wave--back { opacity: .45; animation-duration: 3.6s; animation-direction: reverse; height: 85%; }

    /* Gelembung kecil yang naik di dalam cairan. */
    .sp-bubble {
        position: absolute;
        bottom: -20px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .22);
        animation: sp-bubble 2.8s ease-in infinite;
    }

    .sp-loader { display: flex; gap: 6px; opacity: 0; animation: sp-fade .4s ease-out 1.6s forwards; }
    .sp-loader span { width: 6px; height: 6px; border-radius: 50%; background: currentColor; animation: sp-dot 1s ease-in-out infinite; }
    .sp-loader span:nth-child(2) { animation-delay: .15s; }
    .sp-loader span:nth-child(3) { animation-delay: .3s; }

    @keyframes sp-pop { from { opacity: 0; transform: translateY(14px) scale(.8); } to { opacity: 1; transform: none; } }
    @keyframes sp-grow { to { transform: scaleY(1); } }
    @keyframes sp-fade { to { opacity: 1; } }
    @keyframes sp-rise { to { height: calc(100% + 40px); } }
    @keyframes sp-wave { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    @keyframes sp-bubble { 0% { transform: translateY(0) scale(.6); opacity: 0; } 20% { opacity: 1; } 100% { transform: translateY(-70vh) scale(1.1); opacity: 0; } }
    @keyframes sp-dot { 0%, 100% { transform: translateY(0); opacity: .5; } 50% { transform: translateY(-5px); opacity: 1; } }

    /* Lanjutan dari splash statis iOS: logo sudah terlihat sejak awal. */
    .sp-static-start .sp-logo,
    .sp-static-start .sp-divider { animation: none; opacity: 1; transform: none; }
    .sp-static-start .sp-divider { opacity: .35; }

    @media (prefers-reduced-motion: reduce) {
        .show-splash #app-splash { --sp-rise: .01s; }
        .sp-wave, .sp-bubble, .sp-loader span { animation: none; }
    }
</style>

<div id="app-splash" aria-hidden="true">
    <div class="sp-stage">
        <div class="sp-logos">
            <span class="sp-logo sp-logo--zakiah"></span>
            <span class="sp-divider"></span>
            <span class="sp-logo sp-logo--secaca"></span>
        </div>
        <div class="sp-caption">Zakiah &middot; Secaca</div>
    </div>

    <div class="sp-liquid">
        <div class="sp-waves">
            <svg class="sp-wave sp-wave--back" viewBox="0 0 1200 34" preserveAspectRatio="none">
                {{-- Periode gelombang harus membagi 600 (geser -50% dari 1200) agar loop mulus. --}}
                <path fill="#d98260" d="M0 17 Q 50 2 100 17 T 200 17 T 300 17 T 400 17 T 500 17 T 600 17 T 700 17 T 800 17 T 900 17 T 1000 17 T 1100 17 T 1200 17 V 34 H 0 Z" />
            </svg>
            <svg class="sp-wave" viewBox="0 0 1200 34" preserveAspectRatio="none">
                <path fill="#d98260" d="M0 20 Q 75 4 150 20 T 300 20 T 450 20 T 600 20 T 750 20 T 900 20 T 1050 20 T 1200 20 V 34 H 0 Z" />
            </svg>
        </div>

        <div class="sp-fill">
            <span class="sp-bubble" style="left: 18%; width: 10px; height: 10px; animation-delay: .6s"></span>
            <span class="sp-bubble" style="left: 42%; width: 6px; height: 6px; animation-delay: 1.3s"></span>
            <span class="sp-bubble" style="left: 67%; width: 12px; height: 12px; animation-delay: .9s"></span>
            <span class="sp-bubble" style="left: 84%; width: 7px; height: 7px; animation-delay: 1.7s"></span>

            <div class="sp-stage">
                <div class="sp-logos">
                    <span class="sp-logo sp-logo--zakiah"></span>
                    <span class="sp-divider"></span>
                    <span class="sp-logo sp-logo--secaca"></span>
                </div>
                <div class="sp-caption">Zakiah &middot; Secaca</div>
                <div class="sp-loader"><span></span><span></span><span></span></div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var root = document.documentElement;
        var splash = document.getElementById('app-splash');

        if (!root.classList.contains('show-splash') || !splash) {
            splash && splash.remove();
            window.__hideSplash = function () {};
            return;
        }

        // Tampil minimal sampai cairan penuh, supaya animasi tidak terpotong.
        var MIN_VISIBLE = 2300;
        var startedAt = Date.now();
        var hidden = false;

        try { sessionStorage.setItem('splash-shown', '1'); } catch (e) {}

        window.__hideSplash = function () {
            if (hidden) { return; }
            hidden = true;

            setTimeout(function () {
                splash.classList.add('sp-done');
                setTimeout(function () {
                    splash.remove();
                    root.classList.remove('show-splash');
                }, 600);
            }, Math.max(0, MIN_VISIBLE - (Date.now() - startedAt)));
        };

        // Pengaman: jangan pernah menutupi aplikasi lebih dari 8 detik.
        setTimeout(window.__hideSplash, 8000);
    })();
</script>
