import { Deferred } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Skeleton } from '@/components/ui/skeleton';
import TableSkeleton from '@/components/ui/table-skeleton';

/** Kerangka deretan kartu ringkasan (angka total) saat data dimuat. */
export function StatCardsSkeleton({ count = 4 }: { count?: number }) {
    return (
        <div
            role="status"
            aria-label="Memuat ringkasan"
            className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5"
        >
            {Array.from({ length: count }).map((_, i) => (
                <div key={i} className="space-y-3 rounded-xl border bg-card p-5">
                    <Skeleton className="h-3 w-1/2" />
                    <Skeleton className="h-6 w-3/4" />
                    <Skeleton className="h-3 w-2/3" />
                </div>
            ))}
        </div>
    );
}

/**
 * Tampilkan isi yang bergantung pada prop berat yang dikirim belakangan (Inertia::defer di
 * controller). Halaman tampil dulu tanpa data; selama prop belum datang yang terlihat skeleton,
 * lalu children dipanggil dengan nilai yang sudah pasti ada (bukan undefined).
 *
 * Contoh: <DeferredData value={pagination}>{(pagination) => <Table pagination={pagination} />}</DeferredData>
 */
export function DeferredData<T>({
    data = 'pagination',
    value,
    fallback,
    children,
}: {
    /** Nama prop deferred di controller (default "pagination"). */
    data?: string | string[];
    value: T | null | undefined;
    fallback?: ReactNode;
    children: (value: T) => ReactNode;
}) {
    const skeleton = fallback ?? <TableSkeleton />;

    return (
        <Deferred data={data} fallback={skeleton}>
            {value == null ? skeleton : children(value)}
        </Deferred>
    );
}
