import { Skeleton } from '@/components/ui/skeleton';

/** Kerangka tabel saat data dimuat (header, baris, dan bar paginasi). */
export default function TableSkeleton({
    rows = 8,
    columns = 6,
}: {
    rows?: number;
    columns?: number;
}) {
    return (
        <div role="status" aria-busy="true" aria-label="Memuat data" className="space-y-3">
            <div
                className="grid gap-4 rounded-lg bg-muted/70 px-3 py-3"
                style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}
            >
                {Array.from({ length: columns }).map((_, i) => (
                    <Skeleton key={i} className="h-3 w-3/4 bg-muted-foreground/15" />
                ))}
            </div>

            <div className="divide-y divide-border/70">
                {Array.from({ length: rows }).map((_, row) => (
                    <div
                        key={row}
                        className="grid items-center gap-4 px-3 py-3.5"
                        style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}
                    >
                        {Array.from({ length: columns }).map((_, col) => (
                            <Skeleton
                                key={col}
                                className="h-3.5"
                                style={{ width: `${55 + ((row * 7 + col * 13) % 40)}%` }}
                            />
                        ))}
                    </div>
                ))}
            </div>

            <div className="flex items-center justify-between pt-1">
                <Skeleton className="h-8 w-40" />
                <Skeleton className="h-8 w-56" />
            </div>
            <span className="sr-only">Memuat data…</span>
        </div>
    );
}
