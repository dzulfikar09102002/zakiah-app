import { Link } from '@inertiajs/react';
import { NavSearchButton, NavSearchDialog } from '@/components/nav-search';
import { NavSection } from '@/components/nav-tree';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarRail,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { useNavigation } from '@/lib/navigation';
import dashboardRoutes from '@/routes/dashboard';
import AppLogo from './app-logo';

/**
 * Sidebar backoffice. Bisa diringkas jadi ikon (tombol di header, Ctrl+B, atau klik tepi
 * sidebar); isi header menu lalu muncul sebagai panel di samping.
 */
export function AppSidebar() {
    const navigation = useNavigation();

    return (
        <Sidebar collapsible="icon" variant="sidebar">
            <SidebarHeader className="gap-3 pb-0">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            tooltip="Beranda"
                            className="hover:bg-sidebar-accent/60"
                        >
                            <Link href={dashboardRoutes.index()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <NavSearchButton />
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="gap-0 py-1 [scrollbar-color:var(--color-sidebar-border)_transparent] [scrollbar-width:thin]">
                <NavSection label="Menu" items={navigation.main} />
                <NavSection label="Pengaturan" items={navigation.setting} />
            </SidebarContent>

            <SidebarFooter className="gap-2">
                <SidebarSeparator className="mx-0" />
                <NavUser />
            </SidebarFooter>

            <SidebarRail />
            <NavSearchDialog />
        </Sidebar>
    );
}
