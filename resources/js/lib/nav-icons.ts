import type { LucideIcon } from 'lucide-react';
import {
    BadgePercent,
    BarChart3,
    Boxes,
    Building2,
    ChartNoAxesCombined,
    CircleDollarSign,
    ClipboardCheck,
    ClipboardList,
    Cog,
    CreditCard,
    FileBarChart,
    FileText,
    Gem,
    History,
    KeyRound,
    LayoutGrid,
    ListTree,
    MapPin,
    MonitorCog,
    Package,
    Percent,
    ReceiptText,
    ScanBarcode,
    Settings,
    ShieldCheck,
    ShoppingBag,
    Store,
    Tags,
    TrendingUp,
    Truck,
    UserCog,
    Users,
    UsersRound,
    Utensils,
    Wallet,
    Warehouse,
} from 'lucide-react';
import { createElement, type ReactElement } from 'react';

/**
 * Ikon yang bisa dipilih untuk menu (kolom icon di tabel menu menyimpan namanya).
 * Sengaja daftar terbatas: mengimpor semua ikon lucide membuat bundle sangat besar.
 */
export const navIcons: Record<string, LucideIcon> = {
    BadgePercent,
    BarChart3,
    Boxes,
    Building2,
    ChartNoAxesCombined,
    CircleDollarSign,
    ClipboardCheck,
    ClipboardList,
    Cog,
    CreditCard,
    FileBarChart,
    FileText,
    Gem,
    History,
    KeyRound,
    LayoutGrid,
    ListTree,
    MapPin,
    MonitorCog,
    Package,
    Percent,
    ReceiptText,
    ScanBarcode,
    Settings,
    ShieldCheck,
    ShoppingBag,
    Store,
    Tags,
    TrendingUp,
    Truck,
    UserCog,
    Users,
    UsersRound,
    Utensils,
    Wallet,
    Warehouse,
};

export const navIconNames = Object.keys(navIcons).sort();

export const navIcon = (name?: string | null): LucideIcon | null =>
    (name && navIcons[name]) || null;

/** Render ikon menu dari namanya (tanpa membuat komponen baru saat render). */
export const renderNavIcon = (
    name: string | null | undefined,
    className = 'size-4',
): ReactElement | null => {
    const icon = navIcon(name);

    return icon ? createElement(icon, { className }) : null;
};
