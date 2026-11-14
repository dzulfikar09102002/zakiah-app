import { router } from '@inertiajs/react';
import {
    CheckCircle2,
    ClipboardCheck,
    ListChecks,
    Lock,
    LockOpen,
    Search,
    Undo2,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import { DifferenceValue } from '@/components/stock/helpers';
import TablePagination from '@/components/table-pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { cn } from '@/lib/utils';
import productOpnameSessions from '@/routes/product-opname-sessions';
import FinishDialog from './finish-dialog';
import { postSessionAction, sessionEmployeeName } from './helpers';
import ScanDetailDialog from './scan-detail-dialog';
import type {
    OpnameSession,
    ReviewData,
    ReviewFilter,
    ReviewRow,
} from './types';
import { DECISION_ACCEPTED, DECISION_REJECTED } from './types';

type Props = {
    session: OpnameSession;
    review: ReviewData;
    /** Tampilan PJ saat review; selain itu hanya baca. */
    canDecide: boolean;
    /** Rincian dan koreksi scan tersedia untuk akun ini. */
    canCorrect: boolean;
};

/** Pilihan yang belum dikunci: hanya di layar ini, disimpan di perangkat supaya tidak hilang saat dimuat ulang. */
type Draft = { decision: number | null; note: string };

const MIN_NOTE = 3;

const draftKey = (sessionId: number) => `so-review-draft-${sessionId}`;

function readDrafts(sessionId: number): Record<number, Draft> {
    try {
        return JSON.parse(localStorage.getItem(draftKey(sessionId)) ?? '{}');
    } catch {
        return {};
    }
}

function writeDrafts(sessionId: number, drafts: Record<number, Draft>) {
    try {
        localStorage.setItem(draftKey(sessionId), JSON.stringify(drafts));
    } catch {
        // Penyimpanan perangkat tidak tersedia: pilihan tetap jalan, hanya tidak diingat.
    }
}

/** Tanda untuk PJ, bukan bukti salah: barang kembar yang di-scan cepat juga bisa kena. */
function ScanFlags({ row }: { row: ReviewRow }) {
    const scanners = row.scanners ?? [];
    const rapid = row.rapid_repeats ?? 0;
    const cancelled = row.cancelled_scans ?? 0;
    const overStock = row.scanned === 1 && row.difference_stock > 0;

    if (!scanners.length && !cancelled) {
        return null;
    }

    return (
        <div className="mt-1 space-y-1">
            {(rapid > 0 || overStock || cancelled > 0) && (
                <div className="flex flex-wrap gap-1">
                    {rapid > 0 && (
                        <Badge
                            variant="outline"
                            className="border-transparent bg-amber-500/14 text-amber-700 dark:text-amber-400"
                        >
                            Mungkin dobel: {rapid} scan berdekatan
                        </Badge>
                    )}
                    {overStock && (
                        <Badge
                            variant="outline"
                            className="border-transparent bg-amber-500/14 text-amber-700 dark:text-amber-400"
                        >
                            Melebihi stok sistem
                        </Badge>
                    )}
                    {cancelled > 0 && (
                        <Badge
                            variant="outline"
                            className="border-transparent bg-slate-500/12 text-slate-700 dark:text-slate-300"
                        >
                            {cancelled} scan dibatalkan
                        </Badge>
                    )}
                </div>
            )}
            {scanners.length > 0 && (
                <p className="text-xs font-normal break-words whitespace-normal text-muted-foreground">
                    {scanners
                        .map((scanner) => `${scanner.name}: ${scanner.qty}`)
                        .join(', ')}
                </p>
            )}
        </div>
    );
}

function DecisionBadge({ decision }: { decision: number }) {
    return decision === DECISION_ACCEPTED ? (
        <div className="space-y-0.5">
            <Badge
                variant="outline"
                className="border-transparent bg-emerald-500/12 text-emerald-700 dark:text-emerald-400"
            >
                Pakai Stok SO
            </Badge>
            <p className="text-[11px] text-muted-foreground">
                Stok sistem disesuaikan saat sesi ditutup
            </p>
        </div>
    ) : (
        <div className="space-y-0.5">
            <Badge
                variant="outline"
                className="border-transparent bg-sky-500/12 text-sky-700 dark:text-sky-400"
            >
                Pakai Stok Sistem
            </Badge>
            <p className="text-[11px] text-muted-foreground">
                Dicatat cocok saat sesi ditutup
            </p>
        </div>
    );
}

export default function ReviewPanel({
    session,
    review,
    canDecide,
    canCorrect,
}: Props) {
    const { pagination, summary, filter } = review;
    const [search, setSearch] = useState(review.search);
    const [drafts, setDrafts] = useState<Record<number, Draft>>(() =>
        readDrafts(session.id),
    );
    // Baris cocok yang sedang dibuka kuncinya untuk dihitung ulang.
    const [openMatched, setOpenMatched] = useState<number[]>([]);
    const [busy, setBusy] = useState<number | null>(null);
    const [finishOpen, setFinishOpen] = useState(false);
    const [detailProduct, setDetailProduct] = useState<{
        id: number;
        name: string;
    } | null>(null);
    const firstRender = useRef(true);

    const locked = summary.difference - summary.pending;
    const ready = summary.pending === 0 && openMatched.length === 0;
    const progress = summary.difference
        ? Math.round((locked / summary.difference) * 100)
        : 100;

    const visit = (params: Record<string, string | number>) =>
        router.get(
            productOpnameSessions.show(session.id).url,
            { filter, search, per_page: pagination.per_page, ...params },
            {
                only: ['review'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );

    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;

            return;
        }

        const timer = setTimeout(() => visit({ search, page: 1 }), 350);

        return () => clearTimeout(timer);
        // Hanya mengikuti ketikan pencarian.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    useEffect(() => writeDrafts(session.id, drafts), [session.id, drafts]);

    // Baris cocok yang setelah dihitung ulang jadi selisih tidak lagi dianggap "dibuka".
    useEffect(() => {
        const nowDifferent = pagination.data
            .filter((row) => row.difference_stock !== 0)
            .map((row) => row.product_id);

        setOpenMatched((current) =>
            current.filter((id) => !nowDifferent.includes(id)),
        );
    }, [pagination.data]);

    const draftOf = (row: ReviewRow): Draft =>
        drafts[row.product_id] ?? { decision: null, note: '' };

    const updateDraft = (row: ReviewRow, patch: Partial<Draft>) =>
        setDrafts((current) => ({
            ...current,
            [row.product_id]: { ...draftOf(row), ...patch },
        }));

    const dropDraft = (productId: number) =>
        setDrafts((current) => {
            const next = { ...current };
            delete next[productId];

            return next;
        });

    const post = (
        url: string,
        data: Record<string, string | number>,
        productId: number,
        success: string,
        onSuccess: () => void,
    ) => {
        setBusy(productId);
        router.post(url, data, {
            only: ['review'],
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.success(success);
                onSuccess();
            },
            onError: (errors) =>
                toast.error(
                    Object.values(errors)[0] ??
                        'Aksi gagal, silakan coba lagi.',
                ),
            onFinish: () => setBusy(null),
        });
    };

    const lockRow = (row: ReviewRow) => {
        const draft = draftOf(row);

        if (draft.decision === null || draft.note.trim().length < MIN_NOTE) {
            return;
        }

        post(
            productOpnameSessions.lockReview(session.id).url,
            {
                product_id: row.product_id,
                decision: draft.decision,
                note: draft.note.trim(),
            },
            row.product_id,
            draft.decision === DECISION_ACCEPTED
                ? `${row.name} dikunci: pakai stok SO`
                : `${row.name} dikunci: pakai stok sistem`,
            () => dropDraft(row.product_id),
        );
    };

    const unlockRow = (row: ReviewRow) =>
        post(
            productOpnameSessions.unlockReview(session.id).url,
            { product_id: row.product_id },
            row.product_id,
            `Kunci ${row.name} dibuka`,
            // Pilihan dan alasan sebelumnya dikembalikan supaya tinggal diubah sedikit.
            () =>
                updateDraft(row, {
                    decision: row.decision,
                    note: row.note ?? '',
                }),
        );

    const isDifferent = (row: ReviewRow) => row.difference_stock !== 0;
    const isOpen = (row: ReviewRow) =>
        isDifferent(row)
            ? row.decision === null
            : openMatched.includes(row.product_id);

    const detailButton = (row: ReviewRow) =>
        canDecide && canCorrect && isOpen(row) ? (
            <Button
                type="button"
                variant="outline"
                size="icon"
                className="size-11 shrink-0 md:size-9"
                aria-label={`Hitung ulang ${row.name}`}
                title="Hitung ulang (tambah / batalkan scan)"
                onClick={() =>
                    setDetailProduct({ id: row.product_id, name: row.name })
                }
            >
                <ListChecks />
            </Button>
        ) : null;

    /** Pilihan stok: dua tombol berdampingan, atau label bila sudah dikunci. */
    const decisionCell = (row: ReviewRow) => {
        if (!isDifferent(row)) {
            return (
                <span className="inline-flex items-center gap-1.5 text-sm text-muted-foreground">
                    <CheckCircle2 className="size-4 text-emerald-600" /> Cocok
                </span>
            );
        }

        if (row.decision !== null || !canDecide) {
            return row.decision !== null ? (
                <DecisionBadge decision={row.decision} />
            ) : (
                <span className="text-sm text-muted-foreground">
                    Belum dikunci
                </span>
            );
        }

        const draft = draftOf(row);
        const options = [
            {
                value: DECISION_ACCEPTED,
                label: 'Stok SO',
                stock: row.counted_stock,
                active: 'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-600/90 hover:text-white',
            },
            {
                value: DECISION_REJECTED,
                label: 'Stok Sistem',
                stock: row.recorded_stock,
                active: 'border-sky-600 bg-sky-600 text-white hover:bg-sky-600/90 hover:text-white',
            },
        ];

        return (
            <div
                role="radiogroup"
                aria-label={`Pilih stok ${row.name}`}
                className="grid grid-cols-2 gap-1.5"
            >
                {options.map((option) => {
                    const selected = draft.decision === option.value;

                    return (
                        <Button
                            key={option.value}
                            type="button"
                            role="radio"
                            aria-checked={selected}
                            variant="outline"
                            disabled={busy === row.product_id}
                            className={cn(
                                'h-auto min-h-11 flex-col gap-0 px-2 py-1 leading-tight md:min-h-9',
                                selected && option.active,
                            )}
                            onClick={() =>
                                updateDraft(row, { decision: option.value })
                            }
                        >
                            <span className="text-xs font-semibold">
                                {option.label}
                            </span>
                            <span
                                className={cn(
                                    'text-[11px] tabular-nums',
                                    selected
                                        ? 'text-white/85'
                                        : 'text-muted-foreground',
                                )}
                            >
                                {option.stock} {row.unit_name ?? ''}
                            </span>
                        </Button>
                    );
                })}
            </div>
        );
    };

    const noteCell = (row: ReviewRow) => {
        if (!isDifferent(row)) {
            return <span className="text-sm text-muted-foreground">-</span>;
        }

        if (row.decision !== null || !canDecide) {
            return (
                <p className="text-sm break-words whitespace-normal">
                    {row.note || '-'}
                </p>
            );
        }

        const draft = draftOf(row);

        return (
            <Input
                value={draft.note}
                maxLength={1000}
                disabled={busy === row.product_id}
                placeholder="Alasan (wajib)"
                aria-label={`Alasan ${row.name}`}
                className="h-11 text-base md:h-9 md:text-sm"
                onChange={(event) =>
                    updateDraft(row, { note: event.target.value })
                }
                onKeyDown={(event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        lockRow(row);
                    }
                }}
            />
        );
    };

    /** Gembok: kunci / buka kunci, ditambah tombol hitung ulang saat terbuka. */
    const lockCell = (row: ReviewRow) => {
        if (!canDecide) {
            return null;
        }

        const processing = busy === row.product_id;

        if (!isDifferent(row)) {
            const open = openMatched.includes(row.product_id);

            return (
                <div className="flex items-center justify-end gap-1.5">
                    {detailButton(row)}
                    <Button
                        type="button"
                        variant={open ? 'default' : 'ghost'}
                        size="icon"
                        className={cn(
                            'size-11 shrink-0 md:size-9',
                            !open && 'text-emerald-700 dark:text-emerald-400',
                        )}
                        aria-label={
                            open
                                ? `Kunci lagi ${row.name}`
                                : `Buka kunci ${row.name}`
                        }
                        title={
                            open
                                ? 'Kunci lagi'
                                : 'Buka kunci untuk hitung ulang'
                        }
                        onClick={() =>
                            setOpenMatched((current) =>
                                open
                                    ? current.filter(
                                          (id) => id !== row.product_id,
                                      )
                                    : [...current, row.product_id],
                            )
                        }
                    >
                        {/* Ikon mengikuti keadaan: gembok terbuka = sedang bisa dihitung ulang. */}
                        {open ? <LockOpen /> : <Lock />}
                    </Button>
                </div>
            );
        }

        if (row.decision !== null) {
            return (
                <div className="flex items-center justify-end">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="size-11 shrink-0 text-emerald-700 md:size-9 dark:text-emerald-400"
                        disabled={processing}
                        aria-label={`Buka kunci ${row.name}`}
                        title="Terkunci. Klik untuk membuka dan mengubah."
                        onClick={() => unlockRow(row)}
                    >
                        {processing ? <Spinner /> : <Lock />}
                    </Button>
                </div>
            );
        }

        const draft = draftOf(row);
        const valid =
            draft.decision !== null && draft.note.trim().length >= MIN_NOTE;

        return (
            <div className="flex items-center justify-end gap-1.5">
                {detailButton(row)}
                <Button
                    type="button"
                    size="icon"
                    className="size-11 shrink-0 md:size-9"
                    disabled={!valid || processing}
                    aria-label={`Kunci ${row.name}`}
                    title={
                        valid
                            ? 'Kunci keputusan ini'
                            : 'Pilih stok dan isi alasan dulu'
                    }
                    onClick={() => lockRow(row)}
                >
                    {processing ? <Spinner /> : <LockOpen />}
                </Button>
            </div>
        );
    };

    const tabs: { value: ReviewFilter; label: string; count: number }[] = [
        { value: 'all', label: 'Semua', count: summary.all },
        { value: 'difference', label: 'Selisih', count: summary.difference },
        { value: 'matched', label: 'Cocok', count: summary.matched },
    ];

    return (
        <div className="space-y-4">
            {canDecide && (
                <Card>
                    <CardHeader className="gap-3">
                        <div className="flex flex-wrap items-start justify-between gap-2">
                            <div className="space-y-1.5">
                                <CardTitle>Review Hasil SO</CardTitle>
                                <CardDescription>
                                    Untuk tiap barang selisih, pilih pakai stok
                                    SO (stok sistem ikut hasil hitung) atau stok
                                    sistem (fisiknya ada, hasil SO yang
                                    disamakan), isi alasan, lalu kunci. Yang
                                    sudah dikunci tersimpan sebagai draf. Semua
                                    baru diterapkan saat sesi ditutup.
                                    {review.suspect_count > 0 &&
                                        ` ${review.suspect_count} produk ditandai mungkin dobel, cek rincian scannya dulu.`}
                                </CardDescription>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                className="h-11 w-full md:h-9 md:w-auto"
                                onClick={() =>
                                    postSessionAction(
                                        productOpnameSessions.reopenScan(
                                            session.id,
                                        ).url,
                                        {},
                                        { success: 'Scan dibuka kembali' },
                                    )
                                }
                            >
                                <Undo2 /> Buka Scan Lagi
                            </Button>
                        </div>

                        <div className="space-y-1.5">
                            <div className="flex items-center justify-between text-sm">
                                <span className="font-medium">
                                    {locked} dari {summary.difference} selisih
                                    terkunci
                                </span>
                                <span className="text-muted-foreground tabular-nums">
                                    {progress}%
                                </span>
                            </div>
                            <div
                                role="progressbar"
                                aria-valuenow={progress}
                                aria-valuemin={0}
                                aria-valuemax={100}
                                aria-label="Progres review"
                                className="h-2 overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    className="h-full rounded-full bg-emerald-600 transition-[width] duration-500"
                                    style={{ width: `${progress}%` }}
                                />
                            </div>
                        </div>
                    </CardHeader>
                </Card>
            )}

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="space-y-3 p-0 lg:px-6">
                    <Tabs
                        value={filter}
                        onValueChange={(value) =>
                            visit({ filter: value, page: 1 })
                        }
                    >
                        <TabsList className="grid w-full grid-cols-3 md:inline-flex md:w-auto">
                            {tabs.map((tab) => (
                                <TabsTrigger key={tab.value} value={tab.value}>
                                    {tab.label} ({tab.count})
                                </TabsTrigger>
                            ))}
                        </TabsList>
                    </Tabs>

                    <div className="relative">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Cari nama barang atau barcode"
                            aria-label="Cari nama barang atau barcode"
                            className="h-11 pl-9 text-base md:h-9 md:text-sm"
                        />
                    </div>
                </CardHeader>

                <CardContent className="p-0 pt-3 lg:px-6">
                    {/* HP: kartu supaya tidak perlu geser tabel. */}
                    <ul className="space-y-2 md:hidden">
                        {pagination.data.map((row) => (
                            <li
                                key={row.product_id}
                                className={cn(
                                    'space-y-3 rounded-xl border bg-card p-3',
                                    isDifferent(row) &&
                                        row.decision === null &&
                                        canDecide &&
                                        'border-amber-500/40',
                                )}
                            >
                                <div className="flex items-start gap-2">
                                    <div className="min-w-0 flex-1">
                                        <p className="font-medium break-words">
                                            {row.name}
                                        </p>
                                        <p className="truncate text-xs text-muted-foreground">
                                            {row.barcode ?? row.sku ?? '-'}
                                            {!row.scanned &&
                                                ' • Tidak ter-scan'}
                                        </p>
                                        <ScanFlags row={row} />
                                    </div>
                                    {lockCell(row)}
                                </div>
                                <dl className="grid grid-cols-3 gap-2 text-center">
                                    <div className="rounded-lg bg-muted/60 p-2">
                                        <dt className="text-xs text-muted-foreground">
                                            Sistem
                                        </dt>
                                        <dd className="font-semibold">
                                            {row.recorded_stock}
                                        </dd>
                                    </div>
                                    <div className="rounded-lg bg-muted/60 p-2">
                                        <dt className="text-xs text-muted-foreground">
                                            SO
                                        </dt>
                                        <dd className="font-semibold">
                                            {row.counted_stock}
                                        </dd>
                                    </div>
                                    <div className="rounded-lg bg-muted/60 p-2">
                                        <dt className="text-xs text-muted-foreground">
                                            Selisih
                                        </dt>
                                        <dd>
                                            <DifferenceValue
                                                value={row.difference_stock}
                                            />
                                        </dd>
                                    </div>
                                </dl>
                                {isDifferent(row) && (
                                    <div className="space-y-2">
                                        {decisionCell(row)}
                                        {noteCell(row)}
                                    </div>
                                )}
                            </li>
                        ))}
                        {!pagination.data.length && (
                            <li className="p-6 text-center text-sm text-muted-foreground">
                                Tidak ada data
                            </li>
                        )}
                    </ul>

                    <div className="hidden md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama Barang</TableHead>
                                    <TableHead className="text-right">
                                        Sistem
                                    </TableHead>
                                    <TableHead className="text-right">
                                        SO
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Selisih
                                    </TableHead>
                                    <TableHead className="w-52">
                                        Pakai Stok
                                    </TableHead>
                                    <TableHead className="min-w-48">
                                        Alasan
                                    </TableHead>
                                    {canDecide && (
                                        <TableHead className="w-24 text-right">
                                            Kunci
                                        </TableHead>
                                    )}
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pagination.data.map((row) => (
                                    <TableRow
                                        key={row.product_id}
                                        className={cn(
                                            isDifferent(row) &&
                                                row.decision === null &&
                                                canDecide &&
                                                'bg-amber-500/5',
                                        )}
                                    >
                                        <TableCell className="max-w-72 font-medium whitespace-normal">
                                            {row.name}
                                            <span className="block text-xs font-normal text-muted-foreground">
                                                {row.barcode ?? row.sku ?? '-'}
                                                {!row.scanned &&
                                                    ' • Tidak ter-scan'}
                                            </span>
                                            <ScanFlags row={row} />
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {row.recorded_stock}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {row.counted_stock}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <DifferenceValue
                                                value={row.difference_stock}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            {decisionCell(row)}
                                        </TableCell>
                                        <TableCell>{noteCell(row)}</TableCell>
                                        {canDecide && (
                                            <TableCell>
                                                {lockCell(row)}
                                            </TableCell>
                                        )}
                                    </TableRow>
                                ))}
                                {!pagination.data.length && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={canDecide ? 7 : 6}
                                            className="py-6 text-center text-muted-foreground"
                                        >
                                            Tidak ada data
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>

                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>

            {canDecide && (
                <Card
                    className={cn(
                        'transition-colors',
                        ready && 'border-emerald-500/40 bg-emerald-500/5',
                    )}
                >
                    <CardContent className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <p className="flex items-start gap-2 text-sm">
                            {ready ? (
                                <>
                                    <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-emerald-600" />
                                    Semua selisih sudah dikunci. Simpan untuk
                                    menyesuaikan stok dan menutup sesi.
                                </>
                            ) : (
                                <>
                                    <Lock className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                    <span className="text-muted-foreground">
                                        {summary.pending > 0
                                            ? `${summary.pending} barang selisih belum dikunci. Progres tersimpan sebagai draf.`
                                            : 'Kunci lagi barang cocok yang sedang dibuka.'}
                                    </span>
                                </>
                            )}
                        </p>
                        <Button
                            type="button"
                            className="h-11 w-full md:h-9 md:w-auto"
                            disabled={!ready}
                            onClick={() => setFinishOpen(true)}
                        >
                            <ClipboardCheck /> Simpan Hasil Review &amp; Tutup
                            Sesi SO
                        </Button>
                    </CardContent>
                </Card>
            )}

            <ScanDetailDialog
                sessionId={session.id}
                product={detailProduct}
                onClose={() => setDetailProduct(null)}
                onChanged={() =>
                    router.reload({
                        only: ['review'],
                    })
                }
            />

            <FinishDialog
                open={finishOpen}
                onOpenChange={setFinishOpen}
                sessionId={session.id}
                sessionCode={session.code}
                picName={sessionEmployeeName(session.pic)}
                statement={`Saya, ${sessionEmployeeName(session.pic)}, selaku PJ SO menyatakan telah memeriksa seluruh hasil SO ini dan bertanggung jawab penuh atas setiap keputusan pakai stok SO atau stok sistem beserta alasannya.`}
                onFinished={() => writeDrafts(session.id, {})}
            >
                <dl className="grid grid-cols-3 gap-2 text-center text-sm">
                    <div className="rounded-lg bg-muted/60 p-2">
                        <dt className="text-xs text-muted-foreground">Cocok</dt>
                        <dd className="font-semibold">{summary.matched}</dd>
                    </div>
                    <div className="rounded-lg bg-emerald-500/10 p-2">
                        <dt className="text-xs text-muted-foreground">
                            Pakai Stok SO
                        </dt>
                        <dd className="font-semibold">{summary.accepted}</dd>
                    </div>
                    <div className="rounded-lg bg-sky-500/10 p-2">
                        <dt className="text-xs text-muted-foreground">
                            Pakai Stok Sistem
                        </dt>
                        <dd className="font-semibold">{summary.rejected}</dd>
                    </div>
                </dl>
                <p className="text-sm text-muted-foreground">
                    Stok sistem disesuaikan sekarang untuk {summary.accepted}{' '}
                    barang yang memakai stok SO. {summary.rejected} barang yang
                    memakai stok sistem dicatat cocok atas nama PJ beserta
                    alasannya, stoknya tidak berubah.
                </p>
            </FinishDialog>
        </div>
    );
}
