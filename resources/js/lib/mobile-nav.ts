import type { LucideIcon } from 'lucide-react';
import {
    ArrowLeftRight,
    BadgePercent,
    BarChart3,
    CircleUserRound,
    Building2,
    ClipboardCheck,
    ClipboardList,
    Contact,
    CreditCard,
    Crown,
    FileBarChart,
    FileText,
    Gem,
    History,
    LayoutGrid,
    MapPin,
    Package,
    PackageSearch,
    Percent,
    ReceiptText,
    Ruler,
    Scale,
    Settings,
    ShieldCheck,
    ShoppingBag,
    Store,
    Tags,
    TrendingUp,
    UserRoundCheck,
    Users,
    UsersRound,
    Utensils,
    Wallet,
    Warehouse,
} from 'lucide-react';
import { mainNavItems, settingNavItems } from '@/lib/nav-items';
import { settings } from '@/routes';
import customerCategories from '@/routes/customer-categories';
import customers from '@/routes/customers';
import dailySales from '@/routes/daily-sales';
import employees from '@/routes/employees';
import entityRoutes from '@/routes/entity';
import legalEntities from '@/routes/legal-entities';
import legalEntityLocations from '@/routes/legal-entity-locations';
import locations from '@/routes/locations';
import loyalties from '@/routes/loyalties';
import orderTypes from '@/routes/order-types';
import paymentmethods from '@/routes/payment-methods';
import paymentRecaps from '@/routes/payment-recaps';
import productAdjustmentStocks from '@/routes/product-adjustment-stocks';
import productCategories from '@/routes/product-categories';
import productOpnameServices from '@/routes/product-opname-services';
import productTransferServices from '@/routes/product-transfer-services';
import productUnits from '@/routes/product-units';
import products from '@/routes/products';
import promos from '@/routes/promos';
import reportByLocations from '@/routes/report-by-locations';
import reportByProducts from '@/routes/report-by-products';
import reportEmployeeDetail from '@/routes/report-employee-detail';
import reportEmployeeSummary from '@/routes/report-employee-summary';
import reportSales from '@/routes/report-sales';
import reportStockCard from '@/routes/report-stock-card';
import reportStockMovement from '@/routes/report-stock-movement';
import reportTopCustomers from '@/routes/report-top-customers';
import assetsByCategory from '@/routes/reports/assets-by-category';
import roles from '@/routes/roles';
import saleTransactions from '@/routes/sale-transactions';
import stockRemaining from '@/routes/stock-remaining';
import taxes from '@/routes/taxes';
import type { NavItem } from '@/types';

/** Warna tile ikon ala aplikasi dompet digital. */
export type MenuTone =
    | 'brand'
    | 'emerald'
    | 'sky'
    | 'amber'
    | 'violet'
    | 'rose'
    | 'slate';

export const toneClasses: Record<MenuTone, string> = {
    brand: 'bg-primary/12 text-primary dark:bg-primary/20',
    emerald:
        'bg-emerald-500/12 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400',
    sky: 'bg-sky-500/12 text-sky-600 dark:bg-sky-400/15 dark:text-sky-400',
    amber: 'bg-amber-500/14 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400',
    violet: 'bg-violet-500/12 text-violet-600 dark:bg-violet-400/15 dark:text-violet-400',
    rose: 'bg-rose-500/12 text-rose-600 dark:bg-rose-400/15 dark:text-rose-400',
    slate: 'bg-slate-500/12 text-slate-600 dark:bg-slate-400/15 dark:text-slate-300',
};

export type MobileMenuLink = {
    title: string;
    href: string;
    icon: LucideIcon;
    tone: MenuTone;
};

export type MobileMenuSection = {
    title: string;
    items: MobileMenuLink[];
};

/** Ikon per halaman; halaman yang tidak terdaftar memakai ikon grupnya. */
const iconByHref: Record<string, LucideIcon> = {
    [reportSales.index().url]: ReceiptText,
    [reportByProducts.index().url]: Package,
    [reportByLocations.index().url]: Store,
    [reportStockCard.index().url]: ClipboardList,
    [reportStockMovement.index().url]: ArrowLeftRight,
    [stockRemaining.chooseLocation().url]: PackageSearch,
    [assetsByCategory.index().url]: Gem,
    [reportEmployeeSummary.index().url]: TrendingUp,
    [reportEmployeeDetail.index().url]: FileBarChart,
    [reportTopCustomers.index().url]: Crown,
    [products.index().url]: ShoppingBag,
    [productCategories.index().url]: Tags,
    [productUnits.index().url]: Ruler,
    [productOpnameServices.index().url]: ClipboardCheck,
    [productTransferServices.index().url]: ArrowLeftRight,
    [productAdjustmentStocks.index().url]: Scale,
    [customers.index().url]: Contact,
    [customerCategories.index().url]: Users,
    [loyalties.index().url]: Gem,
    [dailySales.index().url]: Wallet,
    [paymentRecaps.index().url]: FileText,
    [saleTransactions.index().url]: History,
    [promos.index().url]: BadgePercent,
    [entityRoutes.edit().url]: Building2,
    [locations.index().url]: MapPin,
    [legalEntities.index().url]: Building2,
    [legalEntityLocations.index().url]: Store,
    [employees.index().url]: UserRoundCheck,
    [roles.index().url]: ShieldCheck,
    [paymentmethods.index().url]: CreditCard,
    [taxes.index().url]: Percent,
    [orderTypes.index().url]: Utensils,
    [settings().url]: Settings,
};

const sectionTones: MenuTone[] = [
    'brand',
    'emerald',
    'sky',
    'amber',
    'violet',
    'rose',
];

const isLink = (item: NavItem) => item.href !== '#' && !item.items?.length;

const findGroup = (title: string): NavItem =>
    [...mainNavItems, ...settingNavItems].find(
        (item) => item.title === title,
    ) ?? { title, href: '#', items: [] };

/**
 * Ubah satu grup sidebar menjadi section menu mobile. Anak yang punya
 * sub-menu menjadi section tersendiri; anak berupa link langsung digabung
 * ke section bernama grupnya.
 */
function toSections(group: NavItem, prefix?: string): MobileMenuSection[] {
    const fallbackIcon = group.icon ?? LayoutGrid;
    const direct: NavItem[] = [];
    const nested: NavItem[] = [];

    (group.items ?? []).forEach((child) =>
        (isLink(child) ? direct : nested).push(child),
    );

    const sections: { title: string; items: NavItem[] }[] = [
        ...(direct.length ? [{ title: group.title, items: direct }] : []),
        ...nested.map((child) => ({
            title: child.title,
            items: (child.items ?? []).filter(isLink),
        })),
    ];

    return sections
        .filter((section) => section.items.length > 0)
        .map((section, index) => ({
            title: prefix ? `${prefix} · ${section.title}` : section.title,
            items: section.items.map((item) => ({
                title: item.title,
                href: item.href,
                icon: iconByHref[item.href] ?? fallbackIcon,
                tone: sectionTones[index % sectionTones.length],
            })),
        }));
}

export type MobileTabKey = 'home' | 'reports' | 'sales' | 'stock' | 'account';

/** Sheet yang bisa dibuka: tab bottom nav + "semua menu" dari tombol ☰. */
export type MobileSheetKey = Exclude<MobileTabKey, 'home'> | 'menu';

export type MobileTab = {
    key: MobileTabKey;
    title: string;
    icon: LucideIcon;
    href?: string;
    sections: MobileMenuSection[];
};

const reportGroup = findGroup('Laporan');
const revenueGroup = findGroup('Revenue Center');
const stockGroup = findGroup('Stok');
const productGroup = findGroup('Produk');

const stockReportSection = toSections(reportGroup).find(
    (section) => section.title === 'Stok',
);

export const mobileTabs: MobileTab[] = [
    {
        key: 'home',
        title: 'Beranda',
        icon: LayoutGrid,
        href: '/dashboard',
        sections: [],
    },
    {
        key: 'reports',
        title: 'Laporan',
        icon: BarChart3,
        sections: toSections(reportGroup),
    },
    {
        key: 'sales',
        title: 'Penjualan',
        icon: ReceiptText,
        sections: toSections(revenueGroup),
    },
    {
        key: 'stock',
        title: 'Stok',
        icon: Warehouse,
        sections: [
            ...toSections(stockGroup).map((section) => ({
                ...section,
                title: 'Kelola Stok',
            })),
            ...(stockReportSection
                ? [{ ...stockReportSection, title: 'Laporan Stok' }]
                : []),
            ...toSections(productGroup),
        ],
    },
    {
        key: 'account',
        title: 'Akun',
        icon: CircleUserRound,
        sections: [],
    },
];

/** Semua fitur backoffice, dibuka dari tombol ☰ dan tile "Lainnya". */
export const allMenuSections: MobileMenuSection[] = [
    ...mainNavItems,
    ...settingNavItems,
]
    .filter((group) => group.items?.length)
    .flatMap((group) =>
        toSections(
            group,
            group.items?.some((child) => !isLink(child))
                ? group.title
                : undefined,
        ),
    );

/** Pintasan grid di beranda (seperti grid layanan GoPay). */
export const quickActions: MobileMenuLink[] = [
    {
        title: 'Penjualan',
        href: saleTransactions.index().url,
        icon: History,
        tone: 'brand',
    },
    {
        title: 'Rekap Harian',
        href: dailySales.index().url,
        icon: Wallet,
        tone: 'emerald',
    },
    {
        title: 'Lap. Produk',
        href: reportByProducts.index().url,
        icon: BarChart3,
        tone: 'sky',
    },
    {
        title: 'Sisa Stok',
        href: stockRemaining.chooseLocation().url,
        icon: PackageSearch,
        tone: 'amber',
    },
    {
        title: 'Produk',
        href: products.index().url,
        icon: ShoppingBag,
        tone: 'violet',
    },
    {
        title: 'Pelanggan',
        href: customers.index().url,
        icon: UsersRound,
        tone: 'rose',
    },
    {
        title: 'Promo',
        href: promos.index().url,
        icon: BadgePercent,
        tone: 'brand',
    },
];

const pathOf = (href: string) => {
    try {
        return new URL(href, 'http://local').pathname;
    } catch {
        return href;
    }
};

/** Cocokkan URL saat ini dengan link menu (termasuk halaman turunan, mis. /products/12). */
export function matchesPath(currentPath: string, href: string): boolean {
    const path = pathOf(href);

    if (path === '/' || href === '#') {
        return false;
    }

    return currentPath === path || currentPath.startsWith(`${path}/`);
}

export function activeTabFor(currentPath: string): MobileTabKey | null {
    if (matchesPath(currentPath, '/dashboard')) {
        return 'home';
    }

    // Halaman profil, keamanan, dan tampilan milik tab Akun.
    if (matchesPath(currentPath, '/settings')) {
        return 'account';
    }

    const tab = mobileTabs.find((candidate) =>
        candidate.sections.some((section) =>
            section.items.some((item) => matchesPath(currentPath, item.href)),
        ),
    );

    return tab?.key ?? null;
}
