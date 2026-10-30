import { Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useCurrentUrl } from '@/hooks/use-current-url';
import {
    activeTabFor,
    mobileTabs,
    type MobileTab,
    type MobileTabKey,
} from '@/lib/mobile-nav';
import { cn } from '@/lib/utils';
import { useMobileNav } from './mobile-nav-context';

/** Navigasi bawah ala aplikasi e-wallet; hanya tampil di layar < md. */
export function MobileBottomNav() {
    const { currentUrl } = useCurrentUrl();
    const { openSheet, setOpenSheet } = useMobileNav();
    // Sheet "semua menu" (☰) bukan tab, jadi tetap sorot tab halaman aktif.
    const activeKey =
        openSheet && openSheet !== 'menu'
            ? openSheet
            : activeTabFor(currentUrl);

    const renderTab = (tab: MobileTab) => {
        const isActive = activeKey === tab.key;
        const Icon = tab.icon;

        const inner = (
            <>
                <span className="relative flex h-8 w-14 items-center justify-center">
                    {isActive && (
                        <motion.span
                            layoutId="mobile-nav-pill"
                            className="absolute inset-0 rounded-full bg-primary/14 dark:bg-primary/22"
                            transition={{
                                type: 'spring',
                                stiffness: 500,
                                damping: 38,
                            }}
                        />
                    )}
                    <Icon
                        className={cn(
                            'relative size-[22px] transition-colors',
                            isActive ? 'text-primary' : 'text-muted-foreground',
                        )}
                        strokeWidth={isActive ? 2.3 : 1.8}
                    />
                </span>
                <span
                    className={cn(
                        'text-[11px] leading-none transition-colors',
                        isActive
                            ? 'font-semibold text-primary'
                            : 'font-medium text-muted-foreground',
                    )}
                >
                    {tab.title}
                </span>
            </>
        );

        const className =
            'flex flex-1 flex-col items-center gap-1 pt-2 pb-1.5 outline-none active:scale-95 transition-transform';

        if (tab.href) {
            return (
                <Link
                    key={tab.key}
                    href={tab.href}
                    className={className}
                    aria-current={isActive ? 'page' : undefined}
                    onClick={() => setOpenSheet(null)}
                >
                    {inner}
                </Link>
            );
        }

        // Tab tanpa href selalu membuka sheet (Beranda punya href).
        const sheetKey = tab.key as Exclude<MobileTabKey, 'home'>;

        return (
            <button
                key={tab.key}
                type="button"
                className={className}
                aria-expanded={openSheet === sheetKey}
                onClick={() =>
                    setOpenSheet(openSheet === sheetKey ? null : sheetKey)
                }
            >
                {inner}
            </button>
        );
    };

    return (
        <nav
            aria-label="Navigasi utama"
            className="fixed inset-x-0 bottom-0 z-40 border-t border-border/60 bg-background/85 pb-[env(safe-area-inset-bottom)] backdrop-blur-xl md:hidden"
        >
            <div className="mx-auto flex max-w-lg">
                {mobileTabs.map(renderTab)}
            </div>
        </nav>
    );
}
