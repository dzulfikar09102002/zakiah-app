import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createElement, StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import '../css/app.css';
import { initializeTheme } from './hooks/use-appearance';
import { toast } from 'sonner';
import type { Branding } from './types';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Nama & logo entity dari shared props `branding` (lihat EntityBrandingService).
// Disinkronkan setiap halaman dirender, sehingga title & favicon ikut berubah
// tanpa reload penuh (mis. setelah login atau ganti user).
let brandName = appName;

const syncBranding = (branding?: Branding) => {
    brandName = branding?.name || appName;

    const favicon = document.getElementById(
        'app-favicon',
    ) as HTMLLinkElement | null;
    if (
        favicon &&
        branding?.logo &&
        favicon.getAttribute('href') !== branding.logo
    ) {
        favicon.setAttribute('href', branding.logo);
    }
};

createInertiaApp({
    title: (title) =>
        title
            ? `${title} | ${brandName} Backoffice`
            : `${brandName} Backoffice`,
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <StrictMode>
                <App {...props}>
                    {({ Component, props: pageProps, key }) => {
                        syncBranding(
                            pageProps.branding as Branding | undefined,
                        );

                        return createElement(Component, { key, ...pageProps });
                    }}
                </App>
            </StrictMode>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

router.on('invalid', (event) => {
    // 1. Cegah layar putih/pindah halaman
    event.preventDefault();

    // 2. Ambil data respon
    const response = event.detail.response;

    if (response.status === 500) {
        toast.error('Server Error', {
            description:
                'Silakan periksa kembali data yang Anda masukkan dan coba lagi sesaat atau hubungi admin',
        });
    } else if (response.status === 403) {
        toast.error('Akses ditolak (403).');
    }
});

// This will set light / dark mode on load...
initializeTheme();
