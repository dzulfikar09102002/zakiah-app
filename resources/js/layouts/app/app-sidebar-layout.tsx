import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { MobileBottomNav } from '@/components/mobile/mobile-bottom-nav';
import { MobileMenuSheet } from '@/components/mobile/mobile-menu-sheet';
import { MobileNavProvider } from '@/components/mobile/mobile-nav-context';
import {
    OfflineBanner,
    useThemeColor,
} from '@/components/mobile/mobile-status';
import { MobileTopBar } from '@/components/mobile/mobile-top-bar';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
    mobileHome = false,
}: AppLayoutProps) {
    useThemeColor(mobileHome ? 'brand' : 'surface');

    return (
        <MobileNavProvider>
            <AppShell variant="sidebar">
                <AppSidebar />
                <AppContent variant="sidebar" className="overflow-x-hidden">
                    <AppSidebarHeader breadcrumbs={breadcrumbs} />
                    {!mobileHome && <MobileTopBar breadcrumbs={breadcrumbs} />}
                    {children}
                </AppContent>
            </AppShell>

            <OfflineBanner />
            <MobileBottomNav />
            <MobileMenuSheet />
        </MobileNavProvider>
    );
}
