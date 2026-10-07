import type { InertiaLinkProps } from '@inertiajs/react';
import { type ClassValue, clsx } from 'clsx';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export const capitalize = (text: string): string => {
    return text
        .toLowerCase()
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

/**
 * Format tanggal laporan: "07 Oktober 2026" (atau "07 Oktober 2026 14:30" bila withTime).
 */
export const formatDate = (
    value: string | Date | null | undefined,
    withTime = false,
): string => {
    if (!value) return '-';

    const date = value instanceof Date ? value : new Date(value);

    if (isNaN(date.getTime())) return '-';

    return format(date, withTime ? 'dd MMMM yyyy HH:mm' : 'dd MMMM yyyy', {
        locale: id,
    });
};

export const toRupiah = (amount: number | string): string => {
    const numericAmount =
        typeof amount === 'string' ? parseFloat(amount) : amount;

    if (isNaN(numericAmount)) return 'Rp0';

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(numericAmount);
};

export function formatEmployeeName(name: string): string {
    return name
        .replace(/_sales$/i, '')
        .replace(/sales$/i, '')
        .replace(/_/g, ' ')
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .map(
            (word) =>
                word.charAt(0).toUpperCase() + word.slice(1).toLowerCase(),
        )
        .join(' ');
}
