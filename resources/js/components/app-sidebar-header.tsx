import { Link, router, usePage } from '@inertiajs/react';
import { LogOut, Moon, Search, SquareArrowOutUpRight, Sun } from 'lucide-react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { openNavSearch, ShortcutHint } from '@/components/nav-search';
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useAppearance } from '@/hooks/use-appearance';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import sso from '@/routes/sso';
import type { BreadcrumbItem as BreadcrumbItemType, SharedData } from '@/types';
import { Button } from './ui/button';

/**
 * Header desktop: buka-tutup sidebar dan breadcrumb di kiri; cari menu, POS, tema, dan
 * keluar di kanan.
 */
export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { sso: ssoLinks } = usePage<SharedData>().props;
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const isDark = resolvedAppearance === 'dark';
    const cleanup = useMobileNavigation();

    return (
        <header className="sticky top-0 z-20 hidden h-16 shrink-0 items-center justify-between gap-3 bg-gradient-to-r from-primary to-[color-mix(in_oklab,var(--primary)_82%,black)] px-4 text-primary-foreground shadow-md shadow-primary/20 md:flex">
            <div className="flex min-w-0 items-center gap-2">
                <Tooltip>
                    <TooltipTrigger asChild>
                        <SidebarTrigger
                            variant="ghost"
                            className="-ml-1 size-9 border-0 bg-transparent text-primary-foreground shadow-none hover:bg-white/15 hover:text-primary-foreground focus-visible:ring-white/50 dark:bg-transparent dark:hover:bg-black/15"
                        />
                    </TooltipTrigger>
                    <TooltipContent side="bottom">
                        Buka/tutup sidebar (Ctrl B)
                    </TooltipContent>
                </Tooltip>
                <Separator
                    orientation="vertical"
                    className="mr-1 h-5! bg-primary-foreground/30"
                />
                <div className="min-w-0 [&_[data-slot=breadcrumb-link]]:text-primary-foreground/75 [&_[data-slot=breadcrumb-link]:hover]:text-primary-foreground [&_[data-slot=breadcrumb-list]]:text-primary-foreground/75 [&_[data-slot=breadcrumb-page]]:font-semibold [&_[data-slot=breadcrumb-page]]:text-primary-foreground">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
            </div>

            <div className="flex shrink-0 items-center gap-1.5">
                <Button
                    variant="outline"
                    onClick={openNavSearch}
                    className="h-9 w-9 justify-start gap-2 border-white/25 bg-white/10 px-2.5 text-primary-foreground shadow-none hover:border-white/40 hover:bg-white/20 hover:text-primary-foreground focus-visible:ring-white/50 lg:w-56 dark:border-black/15 dark:bg-black/10 dark:hover:bg-black/20"
                    aria-label="Cari menu"
                >
                    <Search />
                    <span className="hidden flex-1 text-left font-normal text-primary-foreground/80 lg:inline">
                        Cari menu…
                    </span>
                    <ShortcutHint className="hidden border-white/25 bg-white/15 text-primary-foreground lg:inline-flex" />
                </Button>

                {ssoLinks?.newzakica && (
                    <Button
                        variant="outline"
                        className="h-9 border-white/25 bg-white/10 text-primary-foreground shadow-none hover:border-white/40 hover:bg-white/20 hover:text-primary-foreground focus-visible:ring-white/50 dark:border-black/15 dark:bg-black/10 dark:hover:bg-black/20"
                        asChild
                    >
                        <a
                            href={sso.newzakica().url}
                            target="_blank"
                            rel="noopener"
                            title="Kasir dan laporan per CV, tanpa login ulang"
                        >
                            <SquareArrowOutUpRight />
                            <span className="hidden xl:inline">
                                POS &amp; Backoffice CV
                            </span>
                        </a>
                    </Button>
                )}

                <Tooltip>
                    <TooltipTrigger asChild>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-9 text-primary-foreground hover:bg-white/15 hover:text-primary-foreground focus-visible:ring-white/50 dark:hover:bg-black/15"
                            aria-label={
                                isDark
                                    ? 'Ganti ke tampilan terang'
                                    : 'Ganti ke tampilan gelap'
                            }
                            onClick={() =>
                                updateAppearance(isDark ? 'light' : 'dark')
                            }
                        >
                            {isDark ? <Sun /> : <Moon />}
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent side="bottom">
                        {isDark ? 'Tampilan terang' : 'Tampilan gelap'}
                    </TooltipContent>
                </Tooltip>

                <Button
                    className="h-9 bg-white font-semibold text-primary shadow-sm shadow-black/10 hover:bg-white/90 dark:bg-background dark:text-foreground dark:hover:bg-background/90"
                    asChild
                >
                    <Link
                        href={logout()}
                        as="button"
                        onClick={() => {
                            cleanup();
                            router.flushAll();
                        }}
                        data-test="header-logout-button"
                    >
                        <LogOut />
                        <span className="hidden lg:inline">Keluar</span>
                    </Link>
                </Button>
            </div>
        </header>
    );
}
