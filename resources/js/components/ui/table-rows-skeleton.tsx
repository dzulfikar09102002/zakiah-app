import { Skeleton } from '@/components/ui/skeleton';
import { TableCell, TableRow } from '@/components/ui/table';

/** Jumlah baris skeleton: per_page dari URL (maks. 10 agar tidak terlalu panjang). */
export function skeletonRowCount(): number {
    const perPage = Number(
        new URLSearchParams(window.location.search).get('per_page') ?? 10,
    );

    return Math.min(Number.isFinite(perPage) && perPage > 0 ? perPage : 10, 10);
}

/**
 * Baris skeleton di dalam <TableBody> selama data tabel (prop deferred) dimuat.
 * Header tabel dan paginasi tetap tampil asli; hanya isi baris yang berkedip.
 */
export function TableRowsSkeleton({
    columns,
    rows = skeletonRowCount(),
}: {
    columns: number;
    rows?: number;
}) {
    return (
        <>
            {Array.from({ length: rows }).map((_, row) => (
                <TableRow
                    key={row}
                    aria-hidden="true"
                    className="hover:bg-transparent"
                >
                    {Array.from({ length: columns }).map((_, col) => (
                        <TableCell key={col}>
                            <Skeleton
                                className="h-3.5"
                                style={{
                                    width: `${55 + ((row * 7 + col * 13) % 40)}%`,
                                }}
                            />
                        </TableCell>
                    ))}
                </TableRow>
            ))}
            <TableRow className="sr-only">
                <TableCell colSpan={columns}>
                    <span role="status">Memuat data…</span>
                </TableCell>
            </TableRow>
        </>
    );
}
