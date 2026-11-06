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
        <header className="sticky top-0 z-20 hidden h-14 shrink-0 items-center justify-between gap-3 border-b border-border bg-background/85 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/70 md:flex">
            <div className="flex min-w-0 items-center gap-2">
                <Tooltip>
                    <TooltipTrigger asChild>
                        <SidebarTrigger className="-ml-1 size-9" />
                    </TooltipTrigger>
                    <TooltipContent side="bottom">
                        Buka/tutup sidebar (Ctrl B)
                    </TooltipContent>
                </Tooltip>
                <Separator orientation="vertical" className="mr-1 h-5" />
                <div className="min-w-0">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
            </div>

            <div className="flex shrink-0 items-center gap-1.5">
                <Button
                    variant="outline"
                    onClick={openNavSearch}
                    className="h-9 w-9 justify-start gap-2 px-2.5 text-muted-foreground lg:w-56"
                    aria-label="Cari menu"
                >
                    <Search />
                    <span className="hidden flex-1 text-left font-normal lg:inline">
                        Cari menu…
                    </span>
                    <ShortcutHint className="hidden lg:inline-flex" />
                </Button>

                {ssoLinks?.newzakica && (
                    <Button variant="outline" className="h-9" asChild>
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
                            className="size-9"
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

                <Button className="h-9" asChild>
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
