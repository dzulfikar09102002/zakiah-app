import { AnimatePresence, motion } from 'framer-motion';
import { WifiOff } from 'lucide-react';
import { useEffect, useSyncExternalStore } from 'react';
import { useAppearance } from '@/hooks/use-appearance';
import { useIsMobile } from '@/hooks/use-mobile';

// Warna status bar per tema entity (samakan dengan token di app.css).
const THEME_COLORS = {
    zakiah: { brand: '#2f3e56', light: '#faf7f1', dark: '#12161f' },
    secaca: { brand: '#1f7f8c', light: '#fdf6ee', dark: '#0f1719' },
    duo: { brand: '#2f3e56', light: '#faf7f1', dark: '#11161d' },
} as const;

const currentBrand = (): keyof typeof THEME_COLORS => {
    const brand = document.documentElement.dataset.brand;

    return brand === 'secaca' || brand === 'duo' ? brand : 'zakiah';
};

/**
 * Samakan warna status bar HP dengan header halaman: warna brand untuk
 * beranda (header berwarna), warna latar untuk halaman lain.
 */
export function useThemeColor(requested: 'brand' | 'surface') {
    const { resolvedAppearance } = useAppearance();
    const isMobile = useIsMobile();
    const variant = isMobile ? requested : 'surface';

    useEffect(() => {
        const meta = document.querySelector<HTMLMetaElement>(
            'meta[name="theme-color"]',
        );

        if (!meta) {
            return;
        }

        const apply = () => {
            const colors = THEME_COLORS[currentBrand()];
            meta.content =
                variant === 'brand' ? colors.brand : colors[resolvedAppearance];
        };

        apply();
        // Tema entity bisa berganti tanpa reload (login/logout), lihat syncBranding di app.tsx.
        document.addEventListener('brand-change', apply);

        return () => document.removeEventListener('brand-change', apply);
    }, [variant, resolvedAppearance]);
}

const subscribeOnline = (callback: () => void) => {
    window.addEventListener('online', callback);
    window.addEventListener('offline', callback);

    return () => {
        window.removeEventListener('online', callback);
        window.removeEventListener('offline', callback);
    };
};

export function useOnline(): boolean {
    return useSyncExternalStore(
        subscribeOnline,
        () => navigator.onLine,
        () => true,
    );
}

/** Pil kecil di atas bottom nav saat koneksi terputus. */
export function OfflineBanner() {
    const online = useOnline();

    return (
        <AnimatePresence>
            {!online && (
                <motion.div
                    initial={{ opacity: 0, y: 16 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 16 }}
                    role="status"
                    className="fixed inset-x-0 bottom-[calc(env(safe-area-inset-bottom)+76px)] z-40 flex justify-center px-4 md:hidden"
                >
                    <span className="flex items-center gap-2 rounded-full bg-foreground px-4 py-2 text-xs font-medium text-background shadow-lg">
                        <WifiOff className="size-3.5" />
                        Kamu sedang offline
                    </span>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
