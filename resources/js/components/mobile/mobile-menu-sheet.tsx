import { useCurrentUrl } from '@/hooks/use-current-url';
import {
    allMenuSections,
    matchesPath,
    mobileTabs,
    type MobileMenuSection,
    type MobileSheetKey,
} from '@/lib/mobile-nav';
import { AccountPanel } from './account-panel';
import { BottomSheet } from './bottom-sheet';
import { MenuTile } from './menu-tile';
import { useMobileNav } from './mobile-nav-context';

const sheetMeta: Record<
    MobileSheetKey,
    { title: string; description: string }
> = {
    reports: {
        title: 'Laporan',
        description: 'Analisis penjualan, stok, dan performa',
    },
    sales: {
        title: 'Penjualan',
        description: 'Transaksi, rekap harian, dan promosi',
    },
    stock: {
        title: 'Stok',
        description: 'Kelola stok, laporan stok, dan produk',
    },
    account: {
        title: 'Akun',
        description: 'Profil, tampilan, dan keluar dari aplikasi',
    },
    menu: {
        title: 'Semua menu',
        description: 'Seluruh fitur backoffice',
    },
};

/** Satu sheet untuk tab bottom nav (selain Beranda) dan tombol ☰. */
export function MobileMenuSheet() {
    const { openSheet, setOpenSheet } = useMobileNav();
    const { currentUrl } = useCurrentUrl();

    const sections: MobileMenuSection[] =
        openSheet === 'menu'
            ? allMenuSections
            : (mobileTabs.find((tab) => tab.key === openSheet)?.sections ?? []);
    const meta = openSheet ? sheetMeta[openSheet] : undefined;

    return (
        <BottomSheet
            open={openSheet !== null}
            onOpenChange={(open) => !open && setOpenSheet(null)}
            title={meta?.title ?? ''}
            description={meta?.description}
        >
            <div className="space-y-6 pt-2">
                {openSheet === 'account' && <AccountPanel />}

                {sections.map((section) => (
                    <section key={section.title}>
                        <h3 className="mb-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                            {section.title}
                        </h3>
                        <div className="grid grid-cols-4 gap-x-2 gap-y-4">
                            {section.items.map((item, index) => (
                                <MenuTile
                                    key={`${item.href}-${index}`}
                                    {...item}
                                    active={matchesPath(currentUrl, item.href)}
                                    onClick={() => setOpenSheet(null)}
                                />
                            ))}
                        </div>
                    </section>
                ))}
            </div>
        </BottomSheet>
    );
}
