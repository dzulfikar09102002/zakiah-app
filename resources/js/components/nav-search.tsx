import { router } from '@inertiajs/react';
import { CornerDownLeft, Search } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { SidebarMenuButton } from '@/components/ui/sidebar';
import { useNavigation } from '@/lib/navigation';
import { cn } from '@/lib/utils';
import type { NavItem } from '@/types';

const OPEN_EVENT = 'nav-search:open';

/** Buka pencarian menu dari mana saja (tombol di header, sidebar). */
export const openNavSearch = () => window.dispatchEvent(new Event(OPEN_EVENT));

const isMac = () =>
    typeof navigator !== 'undefined' &&
    /Mac|iPhone|iPad/.test(navigator.platform);

type Entry = {
    title: string;
    href: string;
    trail: string;
    icon?: NavItem['icon'];
};

/** Ratakan menu jadi daftar halaman beserta jejaknya (Laporan › Penjualan). */
function flatten(
    items: NavItem[],
    trail: string[] = [],
    icon?: NavItem['icon'],
): Entry[] {
    return items.flatMap((item) => {
        const itemIcon = item.icon ?? icon;

        if (item.items?.length) {
            return flatten(item.items, [...trail, item.title], itemIcon);
        }

        return item.href === '#'
            ? []
            : [
                  {
                      title: item.title,
                      href: item.href,
                      trail: trail.join(' › '),
                      icon: itemIcon,
                  },
              ];
    });
}

/**
 * Cari menu (Ctrl/⌘ + K). Isinya sama dengan sidebar, jadi sudah tersaring Hak Akses.
 */
export function NavSearchDialog() {
    const [open, setOpen] = useState(false);
    const navigation = useNavigation();

    const sections = useMemo(
        () =>
            [...navigation.main, ...navigation.setting].map((header) => ({
                title: header.title,
                entries: header.items?.length
                    ? flatten(header.items, [], header.icon)
                    : flatten([header]),
            })),
        [navigation],
    );

    useEffect(() => {
        const onKey = (event: KeyboardEvent) => {
            if (
                event.key.toLowerCase() === 'k' &&
                (event.metaKey || event.ctrlKey)
            ) {
                event.preventDefault();
                setOpen((current) => !current);
            }
        };
        const onOpen = () => setOpen(true);

        window.addEventListener('keydown', onKey);
        window.addEventListener(OPEN_EVENT, onOpen);

        return () => {
            window.removeEventListener('keydown', onKey);
            window.removeEventListener(OPEN_EVENT, onOpen);
        };
    }, []);

    const go = (href: string) => {
        setOpen(false);
        router.visit(href);
    };

    return (
        <CommandDialog
            open={open}
            onOpenChange={setOpen}
            title="Cari menu"
            description="Ketik nama halaman yang ingin dibuka"
        >
            <CommandInput placeholder="Cari menu, mis. kartu stok, karyawan…" />
            <CommandList className="max-h-[min(60dvh,420px)]">
                <CommandEmpty>Menu tidak ditemukan.</CommandEmpty>
                {sections.map((section) =>
                    section.entries.length ? (
                        <CommandGroup
                            key={section.title}
                            heading={section.title}
                        >
                            {section.entries.map((entry) => {
                                const Icon = entry.icon;

                                return (
                                    <CommandItem
                                        key={entry.href}
                                        value={`${entry.title} ${entry.trail} ${section.title}`}
                                        onSelect={() => go(entry.href)}
                                        className="group/item min-h-11 gap-3 md:min-h-9"
                                    >
                                        {Icon ? (
                                            <Icon className="text-muted-foreground" />
                                        ) : (
                                            <span className="size-4" />
                                        )}
                                        <span className="flex min-w-0 flex-col">
                                            <span className="truncate">
                                                {entry.title}
                                            </span>
                                            {entry.trail && (
                                                <span className="truncate text-xs text-muted-foreground">
                                                    {entry.trail}
                                                </span>
                                            )}
                                        </span>
                                        <CornerDownLeft
                                            aria-hidden="true"
                                            className="ml-auto opacity-0 group-data-[selected=true]/item:opacity-60"
                                        />
                                    </CommandItem>
                                );
                            })}
                        </CommandGroup>
                    ) : null,
                )}
            </CommandList>
        </CommandDialog>
    );
}

export function ShortcutHint({ className }: { className?: string }) {
    return (
        <kbd
            className={cn(
                'pointer-events-none inline-flex h-5 items-center gap-0.5 rounded border bg-background/70 px-1.5 font-sans text-[10px] font-medium text-muted-foreground select-none',
                className,
            )}
        >
            {isMac() ? '⌘' : 'Ctrl'} K
        </kbd>
    );
}

/** Tombol "Cari menu" di sidebar; saat sidebar diringkas tinggal ikon. */
export function NavSearchButton() {
    return (
        <SidebarMenuButton
            tooltip="Cari menu (Ctrl K)"
            onClick={openNavSearch}
            className="h-9 border border-white/15 bg-white/10 text-sidebar-muted hover:bg-white/15 hover:text-sidebar-foreground"
        >
            <Search />
            <span>Cari menu…</span>
            <ShortcutHint className="ml-auto border-white/20 bg-white/10 text-sidebar-muted group-data-[collapsible=icon]:hidden" />
        </SidebarMenuButton>
    );
}
