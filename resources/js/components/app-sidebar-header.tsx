import { Link, usePage } from '@inertiajs/react';
import { LogOut, SquareArrowOutUpRight } from 'lucide-react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { logout } from '@/routes';
import sso from '@/routes/sso';
import type { BreadcrumbItem as BreadcrumbItemType, SharedData } from '@/types';
import { Button } from './ui/button';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { sso: ssoLinks } = usePage<SharedData>().props;

    return (
        <header className="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-4">
                <SidebarTrigger />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
            <div className="flex items-center gap-2">
                {ssoLinks?.newzakica && (
                    <Button variant="outline" asChild>
                        {/* SSO: masuk ke NewZakicaPOS tanpa login ulang (tab baru). */}
                        <a
                            href={sso.newzakica().url}
                            target="_blank"
                            rel="noopener"
                            title="Kasir dan laporan per CV, tanpa login ulang"
                        >
                            <SquareArrowOutUpRight />
                            <span className="hidden lg:inline">
                                POS &amp; Backoffice CV
                            </span>
                        </a>
                    </Button>
                )}
                <Button asChild>
                    <Link href={logout()} data-test="logout-button">
                        <LogOut />
                        <span className="hidden lg:inline"> Keluar</span>
                    </Link>
                </Button>
            </div>
        </header>
    );
}
