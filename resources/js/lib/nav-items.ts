import {
    LayoutGrid,
    ScanBarcode,
    ChartNoAxesCombined,
    Warehouse,
    UsersRound,
    CircleDollarSign,
    Cog,
    MonitorCog,
    UserCog,
} from 'lucide-react';
import { settings } from '@/routes';

import customerCategories from '@/routes/customer-categories';
import customers from '@/routes/customers';
import dailySales from '@/routes/daily-sales';
import employees from '@/routes/employees';

import type { NavItem } from '@/types';
import stockRemaining from '@/routes/stock-remaining';
import productCategories from '@/routes/product-categories';
import productUnits from '@/routes/product-units';
import orderTypes from '@/routes/order-types';
import loyalties from '@/routes/loyalties';
import taxes from '@/routes/taxes';
import reportSales from '@/routes/report-sales';
import entityRoutes from '@/routes/entity';
import legalEntities from '@/routes/legal-entities';
import legalEntityLocations from '@/routes/legal-entity-locations';
import locations from '@/routes/locations';
import paymentmethods from '@/routes/payment-methods';
import paymentRecaps from '@/routes/payment-recaps';
import productAdjustmentStocks from '@/routes/product-adjustment-stocks';
import productOpnameServices from '@/routes/product-opname-services';
import productTransferServices from '@/routes/product-transfer-services';
import products from '@/routes/products';
import promos from '@/routes/promos';
import reportByLocations from '@/routes/report-by-locations';
import reportByProducts from '@/routes/report-by-products';
import reportEmployeeDetail from '@/routes/report-employee-detail';
import reportEmployeeSummary from '@/routes/report-employee-summary';
import reportStockCard from '@/routes/report-stock-card';
import reportStockMovement from '@/routes/report-stock-movement';
import assetsByCategory from '@/routes/reports/assets-by-category';
import roles from '@/routes/roles';
import saleTransactions from '@/routes/sale-transactions';
import sellings from '@/routes/sellings';

export const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Laporan',
        href: '#',
        icon: ChartNoAxesCombined,
        isActive: false,
        items: [
            {
                title: 'Penjualan',
                href: '#',
                items: [
                    {
                        title: 'Per Transaksi',
                        href: reportSales.index().url,
                    },
                    {
                        title: 'Per Produk',
                        href: reportByProducts.index().url,
                    },
                    {
                        title: 'Per Toko',
                        href: reportByLocations.index().url,
                    },
                ],
            },
            {
                title: 'Stok',
                href: '/playground/starred',
                items: [
                    {
                        title: 'Kartu Stok',
                        href: reportStockCard.index().url,
                    },
                    {
                        title: 'Pergerakan',
                        href: reportStockMovement.index().url,
                    },
                    {
                        title: 'Sisa Stok',
                        href: stockRemaining.chooseLocation().url,
                    },
                    {
                        title: 'Nilai Aset',
                        href: assetsByCategory.index().url,
                    },
                ],
            },
            {
                title: 'Perfoma Sales',
                href: '#',
                items: [
                    {
                        title: 'Ringkasan',
                        href: reportEmployeeSummary.index().url,
                    },
                    {
                        title: 'Detail',
                        href: reportEmployeeDetail.index().url,
                    },
                ],
            },
        ],
    },
    {
        title: 'Produk',
        href: '#',
        icon: ScanBarcode,
        items: [
            {
                title: 'Kelola Produk',
                href: products.index().url,
            },
            {
                title: 'Produk Kategori',
                href: productCategories.index().url,
            },
            {
                title: 'Produk Unit',
                href: productUnits.index().url,
            },
        ],
    },
    {
        title: 'Stok',
        href: '#',
        icon: Warehouse,
        items: [
            {
                title: 'Stok Opname',
                href: productOpnameServices.index().url,
            },
            {
                title: 'Pindah Stok',
                href: productTransferServices.index().url,
            },
            {
                title: 'Penyesuaian Stok',
                href: productAdjustmentStocks.index().url,
            },
        ],
    },
    {
        title: 'People',
        href: '#',
        icon: UsersRound,
        isActive: false,
        items: [
            {
                title: 'Pelanggan',
                href: '/playground/history',
                items: [
                    {
                        title: 'Daftar Pelanggan',
                        href: customers.index().url,
                    },
                    {
                        title: 'Kategori',
                        href: customerCategories.index().url,
                    },
                    {
                        title: 'Loyalty',
                        href: loyalties.index().url,
                    },
                ],
            },
        ],
    },
    {
        title: 'Revenue Center',
        href: '#',
        icon: CircleDollarSign,
        isActive: false,
        items: [
            {
                title: 'Penjualan',
                href: '/playground/history',
                items: [
                    {
                        title: 'Rekapan',
                        href: dailySales.index().url,
                    },
                    {
                        title: 'Detail Rekapan',
                        href: paymentRecaps.index().url,
                    },
                    {
                        title: 'Data Penjualan',
                        href: saleTransactions.index().url,
                    },
                ],
            },
            {
                title: 'Promosi',
                href: promos.index().url,
            },
        ],
    },
    {
        title: 'Administrasi',
        href: '#',
        icon: Cog,
        items: [
            {
                title: 'Entity',
                href: entityRoutes.edit().url,
            },
            {
                title: 'Lokasi',
                href: locations.index().url,
            },
            {
                title: 'CV / Badan Usaha',
                href: '#',
                items: [
                    {
                        title: 'Daftar CV',
                        href: legalEntities.index().url,
                    },
                    {
                        title: 'CV per Toko',
                        href: legalEntityLocations.index().url,
                    },
                ],
            },
            {
                title: 'Karyawan',
                href: employees.index().url,
            },
            {
                title: 'Role',
                href: roles.index().url,
            },
        ],
    },
];

export const settingNavItems: NavItem[] = [
    {
        title: 'Sistem',
        href: '#',
        icon: MonitorCog,
        isActive: false,
        items: [
            {
                title: 'Pembayaran',
                href: paymentmethods.index().url,
            },
            {
                title: 'Pajak',
                href: taxes.index().url,
            },
        ],
    },
    {
        title: 'Konfigurasi',
        href: '#',
        icon: UserCog,
        isActive: false,
        items: [
            {
                title: 'Jenis Pesanan',
                href: orderTypes.index().url,
            },
            {
                title: 'Pengaturan',
                href: settings().url,
            },
            {
                title: 'Kelola Akses',
                href: settings().url,
            },
        ],
    },
];
