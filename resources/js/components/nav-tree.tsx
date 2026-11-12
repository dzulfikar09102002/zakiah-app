import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { useState } from 'react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { matchesPath } from '@/lib/mobile-nav';
import { readOpenSections, writeOpenSection } from '@/lib/sidebar-sections';
import { cn } from '@/lib/utils';
import type { NavItem } from '@/types';

/**
 * Sidebar tiga lapis: header > group > sub menu.
 * - Header dan group bisa dibuka-tutup; posisinya diingat sampai logout berikutnya
 *   (direset di halaman login, jadi setelah login semua mulai tertutup).
 * - Saat sidebar diringkas (hanya ikon), isi header muncul sebagai panel di samping.
 */

const isLink = (item: NavItem) => item.href !== '#' && !item.items?.length;

function isActive(item: NavItem, path: string): boolean {
    if (isLink(item)) {
        return matchesPath(path, item.href);
    }

    return (item.items ?? []).some((child) => isActive(child, path));
}

export function NavSection({
    label,
    items,
    className,
}: {
    label: string;
    items: NavItem[];
    className?: string;
}) {
    if (!items.length) {
        return null;
    }

    return (
        <SidebarGroup className={className}>
            <SidebarGroupLabel className="text-[10px] font-semibold tracking-[0.18em] text-sidebar-muted uppercase">
                {label}
            </SidebarGroupLabel>
            <SidebarMenu className="gap-0.5">
                {items.map((item) => (
                    <NavHeader key={item.title} item={item} />
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}

function NavHeader({ item }: { item: NavItem }) {
    const { currentUrl } = useCurrentUrl();
    const { state, isMobile } = useSidebar();
    const active = isActive(item, currentUrl);
    const Icon = item.icon;

    if (isLink(item)) {
        return (
            <SidebarMenuItem>
                <SidebarMenuButton
                    asChild
                    isActive={active}
                    tooltip={item.title}
                    className="h-10 rounded-lg px-3 text-[13px] font-semibold tracking-wide uppercase data-[active=true]:bg-sidebar-primary data-[active=true]:font-semibold data-[active=true]:text-sidebar-primary-foreground data-[active=true]:shadow-sm data-[active=true]:shadow-primary/30 data-[active=true]:hover:bg-sidebar-primary data-[active=true]:hover:text-sidebar-primary-foreground"
                >
                    <Link href={item.href} prefetch>
                        {Icon && <Icon />}
                        <span>{item.title}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        );
    }

    if (state === 'collapsed' && !isMobile) {
        return <NavFlyout item={item} active={active} />;
    }

    return <NavCollapsible item={item} active={active} />;
}

/**
 * Status buka-tutup header/group: diingat per kunci, dan terbuka sendiri saat pindah ke
 * halaman di dalamnya.
 */
function useOpenSection(key: string, active: boolean) {
    // Halaman aktif ada di dalamnya: selalu terbuka saat dimuat.
    const [open, setOpen] = useState(
        () => active || (readOpenSections()[key] ?? false),
    );
    const [wasActive, setWasActive] = useState(active);

    if (active !== wasActive) {
        setWasActive(active);

        if (active) {
            setOpen(true);
        }
    }

    const toggle = (next: boolean) => {
        setOpen(next);
        writeOpenSection(key, next);
    };

    return [open, toggle] as const;
}

function NavCollapsible({ item, active }: { item: NavItem; active: boolean }) {
    const { currentUrl } = useCurrentUrl();
    const [open, toggle] = useOpenSection(item.title, active);
    const Icon = item.icon;

    return (
        <Collapsible asChild open={open} onOpenChange={toggle}>
            <SidebarMenuItem className="group/collapsible">
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton
                        tooltip={item.title}
                        className={cn(
                            'h-10 rounded-lg px-3 text-[13px] font-semibold tracking-wide uppercase transition-colors data-[state=open]:text-sidebar-accent-foreground',
                            active &&
                                'bg-sidebar-accent/70 text-sidebar-accent-foreground [&>svg:first-child]:text-sidebar-primary',
                        )}
                    >
                        {Icon && <Icon />}
                        <span>{item.title}</span>
                        <ChevronRight
                            aria-hidden="true"
                            className="ml-auto text-sidebar-muted transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90 motion-reduce:transition-none"
                        />
                    </SidebarMenuButton>
                </CollapsibleTrigger>

                <CollapsibleContent className="overflow-hidden data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down motion-reduce:animate-none">
                    <SidebarMenuSub className="mr-0 gap-0.5 border-sidebar-border py-1 pr-0">
                        {(item.items ?? []).map((child) =>
                            child.items?.length ? (
                                <NavGroup
                                    key={child.title}
                                    group={child}
                                    parent={item.title}
                                    path={currentUrl}
                                />
                            ) : (
                                <NavLeaf
                                    key={child.title}
                                    item={child}
                                    path={currentUrl}
                                />
                            ),
                        )}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuItem>
        </Collapsible>
    );
}

/** Group: label kecil, lalu sub menunya langsung terlihat. */
/** Group = lapis bersarang kedua: bisa dibuka-tutup, sub menunya di dalam. */
function NavGroup({
    group,
    parent,
    path,
}: {
    group: NavItem;
    parent: string;
    path: string;
}) {
    const active = isActive(group, path);
    const [open, toggle] = useOpenSection(`${parent} › ${group.title}`, active);

    return (
        <Collapsible asChild open={open} onOpenChange={toggle}>
            <SidebarMenuSubItem className="group/subsection">
                <CollapsibleTrigger asChild>
                    <SidebarMenuSubButton asChild>
                        <button
                            type="button"
                            className={cn(
                                'h-8 w-full cursor-pointer rounded-lg text-[13px] text-sidebar-foreground',
                                active &&
                                    'font-semibold text-sidebar-accent-foreground',
                            )}
                        >
                            <span>{group.title}</span>
                            <ChevronRight
                                aria-hidden="true"
                                className="ml-auto size-3.5! text-sidebar-muted transition-transform duration-200 group-data-[state=open]/subsection:rotate-90 motion-reduce:transition-none"
                            />
                        </button>
                    </SidebarMenuSubButton>
                </CollapsibleTrigger>

                <CollapsibleContent className="overflow-hidden data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down motion-reduce:animate-none">
                    <SidebarMenuSub className="mx-2 mr-0 gap-0.5 py-1 pr-0">
                        {(group.items ?? []).map((child) => (
                            <NavLeaf
                                key={child.title}
                                item={child}
                                path={path}
                            />
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuSubItem>
        </Collapsible>
    );
}

function NavLeaf({ item, path }: { item: NavItem; path: string }) {
    const active = matchesPath(path, item.href);

    return (
        <SidebarMenuSubItem
            className={cn(
                // Penanda aktif menempel di garis pemandu kiri. Dipasang di <li>, karena
                // tombolnya overflow-hidden dan akan memotong penanda.
                "before:absolute before:top-1/2 before:-left-[11px] before:z-10 before:h-4 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:bg-sidebar-primary before:opacity-0 before:transition-opacity before:content-[''] motion-reduce:before:transition-none",
                active && 'before:opacity-100',
            )}
        >
            <SidebarMenuSubButton
                asChild
                isActive={active}
                className="h-8 rounded-lg text-[13px] text-sidebar-foreground transition-colors data-[active=true]:bg-sidebar-primary data-[active=true]:font-semibold data-[active=true]:text-sidebar-primary-foreground data-[active=true]:shadow-sm data-[active=true]:shadow-primary/30 data-[active=true]:hover:bg-sidebar-primary data-[active=true]:hover:text-sidebar-primary-foreground"
            >
                <Link href={item.href} prefetch>
                    <span>{item.title}</span>
                </Link>
            </SidebarMenuSubButton>
        </SidebarMenuSubItem>
    );
}

/** Sidebar diringkas: klik ikon header membuka daftar sub menunya di samping. */
function NavFlyout({ item, active }: { item: NavItem; active: boolean }) {
    const { currentUrl } = useCurrentUrl();
    const Icon = item.icon;
    const children = item.items ?? [];

    const link = (child: NavItem) => (
        <DropdownMenuItem key={child.title} asChild>
            <Link
                href={child.href}
                prefetch
                className={cn(
                    'cursor-pointer',
                    matchesPath(currentUrl, child.href) &&
                        'bg-accent font-medium text-accent-foreground',
                )}
            >
                {child.title}
            </Link>
        </DropdownMenuItem>
    );

    return (
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <SidebarMenuButton
                        tooltip={item.title}
                        isActive={active}
                        aria-label={item.title}
                    >
                        {Icon && <Icon />}
                        <span>{item.title}</span>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    side="right"
                    align="start"
                    sideOffset={8}
                    className="max-h-[80dvh] min-w-56 overflow-y-auto"
                >
                    <DropdownMenuLabel>{item.title}</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    {children.map((child) =>
                        child.items?.length ? (
                            <DropdownMenuGroup key={child.title}>
                                <DropdownMenuLabel className="pt-2 text-[11px] font-semibold tracking-wide text-muted-foreground uppercase">
                                    {child.title}
                                </DropdownMenuLabel>
                                {child.items.map(link)}
                            </DropdownMenuGroup>
                        ) : (
                            link(child)
                        ),
                    )}
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    );
}
