import { Link } from '@inertiajs/react';
import {
    LayoutGrid,
    ScanBarcode,
    ChartNoAxesCombined,
    Warehouse,
    UsersRound,
    CircleDollarSign,
    Cog,
    MonitorCog,
    UserCog
} from 'lucide-react';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, employee, summary } from '@/routes';
import { roles } from '@/routes';
import type { NavItem } from '@/types';
import AppLogo from './app-logo';
import { NavSetting } from './nav-setting';
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
    },
    {
        title: "Laporan",
        href: "#",
        icon: ChartNoAxesCombined,
        isActive: false,
        items: [
            {
                title: "Penjualan",
                href: "#",
                items: [
                    {
                        title: "Ringkasan",
                        href: summary().url
                    },
                    {
                        title: "Per Product",
                        href: "#"
                    },
                    {
                        title: "Per Toko",
                        href: "#"
                    }
                ]
            },
            {
                title: "Stok",
                href: "/playground/starred",
                items: [
                    {
                        title: "Kartu Stok",
                        href: "#"
                    },
                    {
                        title: "Pergerakan",
                        href: "#"
                    }
                ]
            },
            {
                title: "Sales",
                href: "#",
                items: [
                    {
                        title: "Perform",
                        href: "/playground/settings/general",
                    },
                    {
                        title: "Perform Detail",
                        href: "/playground/settings/team",
                    },
                ],
            },
        ],
    },
    {
        title: "Produk",
        href: "#",
        icon: ScanBarcode,
        items: [
            {
                title: "Kelola Produk",
                href: "#",
            },
            {
                title: "Produk Kategori",
                href: "#",
            },
            {
                title: "Produk Unit",
                href: "#",
            },
        ],
    },
    {
        title: "Stok",
        href: "#",
        icon: Warehouse,
        items: [
            {
                title: "Stok Opname",
                href: "#",
            },
            {
                title: "Pindah Stok",
                href: "#",
            },
            {
                title: "Penyesuaian Stok",
                href: "#",
            },
        ],
    },
    {
        title: "People",
        href: "#",
        icon: UsersRound,
        isActive: false,
        items: [
            {
                title: "Pelanggan",
                href: "/playground/history",
                items: [
                    {
                        title: "Loyalty",
                        href: "#"
                    },
                    {
                        title: "Kategori",
                        href: "#"
                    },
                ]
            },
        ],
    },
    {
        title: "Revenue Center",
        href: "#",
        icon: CircleDollarSign,
        isActive: false,
        items: [
            {
                title: "Penjualan",
                href: "/playground/history",
                items: [
                    {
                        title: "Rekapan",
                        href: "#"
                    },
                    {
                        title: "Data Penjualan",
                        href: "#"
                    },
                ]
            },
            {
                title: "Promosi",
                href: "#"
            }
        ],
    },
    {
        title: "Administrasi",
        href: "#",
        icon: Cog,
        items: [
            {
                title: "Entity",
                href: "#",
            },
            {
                title: "Lokasi",
                href: "#",
            },
            {
                title: "Karyawan",
                href: employee().url,
            },
            {
                title: "Role",
                href: roles().url,
            },
        ],
    },
];
const settingNavItems: NavItem[] = [
    {
        title: "Sistem",
        href: "#",
        icon: MonitorCog,
        isActive: false,
        items: [
            {
                title: "Pembayaran",
                href: "/playground/history",
            },
            {
                title: "Pajak",
                href: "#"
            }
        ],
    },
    {
        title: "Konfigurasi",
        href: "#",
        icon: UserCog,
        isActive: false,
        items: [
            {
                title: "Jenis Pesanan",
                href: "#",
            },
        ],
    },
];
export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
                <NavSetting items={settingNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
