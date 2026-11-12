import { Link } from '@inertiajs/react';
import { NavSearchButton, NavSearchDialog } from '@/components/nav-search';
import { NavSection } from '@/components/nav-tree';
import { NavUser } from '@/components/nav-user';
import { SidebarArt } from '@/components/sidebar-art';
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
            {/* Motif butik di belakang menu; disembunyikan saat sidebar hanya ikon. */}
            <span className="group-data-[collapsible=icon]:hidden">
                <SidebarArt />
            </span>
            <SidebarHeader className="relative gap-3 border-b border-sidebar-border/70 px-3 pt-4 pb-3">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            tooltip="Beranda"
                            className="h-12 group-data-[collapsible=icon]:p-0! hover:bg-transparent active:bg-transparent"
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

            <SidebarContent className="relative gap-0 py-2 [scrollbar-color:var(--color-sidebar-border)_transparent] [scrollbar-width:thin]">
                <NavSection label="Menu" items={navigation.main} />
                <NavSection label="Pengaturan" items={navigation.setting} />
            </SidebarContent>

            <SidebarFooter className="relative gap-2">
                <SidebarSeparator className="mx-0" />
                <NavUser />
            </SidebarFooter>

            <SidebarRail />
            <NavSearchDialog />
        </Sidebar>
    );
}
