import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import { navIcon } from '@/lib/nav-icons';
import { mainNavItems, settingNavItems } from '@/lib/nav-items';
import type { NavItem } from '@/types';

/** Bentuk menu dari server (MenuAccessService::navigation). */
type ServerNavItem = {
    title: string;
    href: string;
    icon?: string | null;
    items?: ServerNavItem[];
};

type SharedProps = {
    navigation?: { main: ServerNavItem[]; setting: ServerNavItem[] } | null;
    access?: { enforced: boolean; permissions: string[] } | null;
};

const toNavItems = (items: ServerNavItem[]): NavItem[] =>
    items.map((item) => ({
        title: item.title,
        href: item.href,
        icon: navIcon(item.icon),
        items: item.items?.length ? toNavItems(item.items) : undefined,
    }));

const collectHrefs = (items: ServerNavItem[], into: Set<string>) => {
    items.forEach((item) => {
        if (item.href !== '#') {
            into.add(item.href);
        }

        collectHrefs(item.items ?? [], into);
    });

    return into;
};

/**
 * Sidebar dari tabel menu (sudah disaring Hak Akses di server). Selama tabel menu belum
 * ada di database, pakai menu statis di nav-items.ts.
 */
export function useNavigation(): { main: NavItem[]; setting: NavItem[] } {
    const { navigation } = usePage<SharedProps>().props;

    return useMemo(
        () =>
            navigation
                ? {
                      main: toNavItems(navigation.main),
                      setting: toNavItems(navigation.setting),
                  }
                : { main: mainNavItems, setting: settingNavItems },
        [navigation],
    );
}

/**
 * Penyaring link untuk menu yang tidak berasal dari server (menu mobile, pintasan
 * beranda): saat pengecekan Hak Akses aktif, hanya halaman yang ada di sidebar.
 */
export function useCanVisit(): (href: string) => boolean {
    const { navigation, access } = usePage<SharedProps>().props;

    if (!navigation || !access?.enforced) {
        return () => true;
    }

    const hrefs = collectHrefs(
        [...navigation.main, ...navigation.setting],
        new Set<string>(),
    );

    return (href: string) => hrefs.has(href.split('?')[0]);
}
