import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import '../css/app.css';
import { initializeTheme } from './hooks/use-appearance';
import { toast } from 'sonner';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const rawEntityName = (window as any).entityName || appName;

const entityName =
    rawEntityName === rawEntityName.toUpperCase() ||
    rawEntityName === rawEntityName.toLowerCase()
        ? rawEntityName
              .toLowerCase()
              .replace(/\b\w/g, (c: string) => c.toUpperCase())
        : rawEntityName;
createInertiaApp({
    title: (title) =>
        title
            ? `${title} | ${entityName} Backoffice`
            : `${entityName} Backoffice`,
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <StrictMode>
                <App {...props} />
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
