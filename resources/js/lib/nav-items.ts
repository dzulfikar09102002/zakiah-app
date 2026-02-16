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
import { dashboard } from '@/routes';
import categories from '@/routes/categories';
import employees from '@/routes/employees';
import locations from '@/routes/locations';
import products from '@/routes/products';
import roles from '@/routes/roles';
import sellings from '@/routes/sellings';
import units from '@/routes/units';
import type { NavItem } from '@/types';

export const mainNavItems: NavItem[] = [
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
                        href: sellings.summary().url
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
                href: products.index().url,
            },
            {
                title: "Produk Kategori",
                href: categories.index().url,
            },
            {
                title: "Produk Unit",
                href: units.index().url,
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
                href: locations.index().url,
            },
            {
                title: "Karyawan",
                href: employees.index().url,
            },
            {
                title: "Role",
                href: roles.index().url
            },
        ],
    },
];

export const settingNavItems: NavItem[] = [
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