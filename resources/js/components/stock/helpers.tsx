import { Badge } from '@/components/ui/badge';
import type { EmployeeName, StockDocumentStatus } from '@/lib/model';
import { capitalize } from '@/lib/utils';

const statusMap: Record<
    StockDocumentStatus,
    {
        label: string;
        variant: 'default' | 'secondary' | 'destructive' | 'outline';
    }
> = {
    requested: { label: 'Diajukan', variant: 'secondary' },
    approved: { label: 'Disetujui', variant: 'default' },
    rejected: { label: 'Ditolak', variant: 'destructive' },
    cancelled: { label: 'Dibatalkan', variant: 'outline' },
};

export function StockStatusBadge({ status }: { status: StockDocumentStatus }) {
    const item = statusMap[status] ?? {
        label: status,
        variant: 'outline' as const,
    };

    return <Badge variant={item.variant}>{item.label}</Badge>;
}

export const stockStatusTabs: { value: string; label: string }[] = [
    { value: 'all', label: 'Semua' },
    { value: 'requested', label: 'Diajukan' },
    { value: 'approved', label: 'Disetujui' },
    { value: 'rejected', label: 'Ditolak' },
];

export const employeeName = (employee?: EmployeeName | null) =>
    employee
        ? capitalize(
              [employee.first_name, employee.last_name]
                  .filter(Boolean)
                  .join(' ')
                  .replaceAll('_', ' '),
          )
        : '-';

/** Waktu lokal dokumen dari DB ("yyyy-MM-dd HH:mm:ss"). */
export const formatDocumentDate = (value?: string | null) =>
    value
        ? new Date(value.replace(' ', 'T')).toLocaleString('id-ID', {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '-';

/** Tampilkan selisih stok dengan tanda + dan warna. */
export function DifferenceValue({ value }: { value: number }) {
    if (value === 0) {
        return <span className="text-muted-foreground">0</span>;
    }

    return (
        <span
            className={
                value > 0
                    ? 'font-medium text-emerald-600 dark:text-emerald-400'
                    : 'font-medium text-destructive'
            }
        >
            {value > 0 ? `+${value}` : value}
        </span>
    );
}
