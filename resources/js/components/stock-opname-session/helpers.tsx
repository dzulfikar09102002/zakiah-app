import { router } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { id as localeId } from 'date-fns/locale';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { SessionEmployee, SessionStatus } from './types';

const statusStyles: Record<
    SessionStatus,
    { label: string; className: string }
> = {
    scheduled: {
        label: 'Dijadwalkan',
        className:
            'border-transparent bg-slate-500/12 text-slate-700 dark:text-slate-300',
    },
    started: {
        label: 'Mulai',
        className:
            'border-transparent bg-sky-500/12 text-sky-700 dark:text-sky-400',
    },
    review: {
        label: 'Review',
        className:
            'border-transparent bg-amber-500/14 text-amber-700 dark:text-amber-400',
    },
    finished: {
        label: 'Selesai',
        className:
            'border-transparent bg-emerald-500/12 text-emerald-700 dark:text-emerald-400',
    },
};

export const sessionStatusTabs: { value: string; label: string }[] = [
    { value: 'all', label: 'Semua' },
    { value: 'scheduled', label: 'Dijadwalkan' },
    { value: 'started', label: 'Mulai' },
    { value: 'review', label: 'Review' },
    { value: 'finished', label: 'Selesai' },
];

export function SessionStatusBadge({ status }: { status: SessionStatus }) {
    const style = statusStyles[status];

    return (
        <Badge variant="outline" className={cn(style.className)}>
            {style.label}
        </Badge>
    );
}

export const formatSessionDate = (value?: string | null) =>
    value ? format(parseISO(value), 'd MMM yyyy', { locale: localeId }) : '-';

export const formatSessionTime = (value?: string | null) =>
    value
        ? format(parseISO(value), 'd MMM yyyy HH:mm', { locale: localeId })
        : '-';

/** Dengan detik, supaya jarak antar scan terlihat saat mengecek dobel. */
export const formatSessionSeconds = (value?: string | null) =>
    value
        ? format(parseISO(value), 'd MMM HH:mm:ss', { locale: localeId })
        : '-';

export const sessionEmployeeName = (employee?: SessionEmployee | null) =>
    employee
        ? [employee.first_name, employee.last_name]
              .filter(Boolean)
              .join(' ')
              .replaceAll('_', ' ')
        : '-';

/** Kirim aksi sesi lewat Inertia, hasilnya ditampilkan sebagai toast. */
export function postSessionAction(
    url: string,
    data: Record<string, string | number | boolean | null>,
    options: {
        success: string;
        onSuccess?: () => void;
        onFinish?: () => void;
    },
) {
    router.post(url, data, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(options.success);
            options.onSuccess?.();
        },
        onError: (errors) => {
            toast.error(
                Object.values(errors)[0] ?? 'Aksi gagal, silakan coba lagi.',
            );
        },
        onFinish: () => options.onFinish?.(),
    });
}
