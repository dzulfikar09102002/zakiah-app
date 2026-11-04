import { router } from '@inertiajs/react';
import { Check, ListChecks, Search, Undo2, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
import { Textarea } from '@/components/ui/textarea';
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

type PendingDecision = { row: ReviewRow; decision: number };

function RowStatus({ row }: { row: ReviewRow }) {
    if (row.decision === DECISION_ACCEPTED) {
        return (
            <Badge
                variant="outline"
                className="border-transparent bg-emerald-500/12 text-emerald-700 dark:text-emerald-400"
            >
                Diterima
            </Badge>
        );
    }

    if (row.difference_stock === 0) {
        return <span className="text-sm text-muted-foreground">Cocok</span>;
    }

    return (
        <span className="text-sm text-muted-foreground">Belum diputuskan</span>
    );
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

export default function ReviewPanel({
    session,
    review,
    canDecide,
    canCorrect,
}: Props) {
    const { pagination, summary, filter } = review;
    const [search, setSearch] = useState(review.search);
    const [pending, setPending] = useState<PendingDecision | null>(null);
    const [note, setNote] = useState('');
    const [processing, setProcessing] = useState(false);
    const [finishOpen, setFinishOpen] = useState(false);
    const [detailProduct, setDetailProduct] = useState<{
        id: number;
        name: string;
    } | null>(null);
    const firstRender = useRef(true);

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

    const closeDecision = () => {
        if (!processing) {
            setPending(null);
            setNote('');
        }
    };

    const submitDecision = () => {
        if (!pending) {
            return;
        }

        const accepted = pending.decision === DECISION_ACCEPTED;

        setProcessing(true);
        postSessionAction(
            productOpnameSessions.decide(session.id).url,
            {
                product_id: pending.row.product_id,
                decision: pending.decision,
                note: note.trim() || null,
            },
            {
                success: accepted
                    ? 'Revisi diterima, stok telah diperbarui'
                    : 'Revisi ditolak, stok tidak berubah',
                onSuccess: () => {
                    setPending(null);
                    setNote('');
                },
                onFinish: () => setProcessing(false),
            },
        );
    };

    const detailButton = (row: ReviewRow) =>
        canDecide &&
        canCorrect &&
        row.decision === null &&
        (row.scanners?.length ?? 0) > 0 ? (
            <Button
                type="button"
                variant="ghost"
                className="h-11 w-full text-muted-foreground md:h-8 md:w-auto"
                onClick={() =>
                    setDetailProduct({ id: row.product_id, name: row.name })
                }
            >
                <ListChecks /> Rincian Scan
            </Button>
        ) : null;

    const actions = (row: ReviewRow) =>
        canDecide && row.decision === null && row.difference_stock !== 0 ? (
            <div className="flex gap-2">
                <Button
                    type="button"
                    className="h-11 flex-1 bg-emerald-600 text-white hover:bg-emerald-700 md:size-9 md:flex-none"
                    aria-label={`Terima revisi ${row.name}`}
                    title="Terima sebagai revisi stok"
                    onClick={() =>
                        setPending({ row, decision: DECISION_ACCEPTED })
                    }
                >
                    <Check />
                    <span className="md:hidden">Terima</span>
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    className="h-11 flex-1 text-destructive md:size-9 md:flex-none"
                    aria-label={`Tolak revisi ${row.name}`}
                    title="Tolak"
                    onClick={() =>
                        setPending({ row, decision: DECISION_REJECTED })
                    }
                >
                    <X />
                    <span className="md:hidden">Tolak</span>
                </Button>
            </div>
        ) : (
            <RowStatus row={row} />
        );

    const tabs: { value: ReviewFilter; label: string; count: number }[] = [
        { value: 'all', label: 'Semua', count: summary.all },
        { value: 'difference', label: 'Selisih', count: summary.difference },
        { value: 'matched', label: 'Cocok', count: summary.matched },
    ];

    return (
        <div className="space-y-4">
            {canDecide && (
                <Card>
                    <CardHeader>
                        <CardTitle>Review Hasil SO</CardTitle>
                        <CardDescription>
                            {summary.pending > 0
                                ? `${summary.pending} produk selisih belum diputuskan, termasuk barang yang tidak ter-scan.`
                                : 'Semua produk selisih sudah diputuskan. Sesi bisa diakhiri.'}
                            {review.suspect_count > 0 &&
                                ` ${review.suspect_count} produk ditandai mungkin dobel, cek rincian scannya sebelum memutuskan.`}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-2 md:flex-row">
                        <Button
                            type="button"
                            className="h-11 md:h-9"
                            disabled={summary.pending > 0}
                            onClick={() => setFinishOpen(true)}
                        >
                            <Check /> Simpan dan Akhiri Sesi
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            className="h-11 md:h-9"
                            onClick={() =>
                                postSessionAction(
                                    productOpnameSessions.reopenScan(session.id)
                                        .url,
                                    {},
                                    { success: 'Scan dibuka kembali' },
                                )
                            }
                        >
                            <Undo2 /> Buka Scan Lagi
                        </Button>
                    </CardContent>
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
                                className="space-y-3 rounded-xl border bg-card p-3"
                            >
                                <div>
                                    <p className="font-medium break-words">
                                        {row.name}
                                    </p>
                                    <p className="truncate text-xs text-muted-foreground">
                                        {row.barcode ?? row.sku ?? '-'}
                                        {!row.scanned && ' • Tidak ter-scan'}
                                    </p>
                                    <ScanFlags row={row} />
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
                                {actions(row)}
                                {detailButton(row)}
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
                                    <TableHead>Barcode</TableHead>
                                    <TableHead className="text-right">
                                        Stok Sistem
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Stok SO
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Selisih
                                    </TableHead>
                                    <TableHead className="w-40">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pagination.data.map((row) => (
                                    <TableRow key={row.product_id}>
                                        <TableCell className="max-w-80 font-medium whitespace-normal">
                                            {row.name}
                                            {!row.scanned && (
                                                <span className="block text-xs font-normal text-muted-foreground">
                                                    Tidak ter-scan
                                                </span>
                                            )}
                                            <ScanFlags row={row} />
                                        </TableCell>
                                        <TableCell>
                                            {row.barcode ?? row.sku ?? '-'}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {row.recorded_stock}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {row.counted_stock}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <DifferenceValue
                                                value={row.difference_stock}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            {actions(row)}
                                            {detailButton(row)}
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {!pagination.data.length && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
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

            <Dialog
                open={pending !== null}
                onOpenChange={(open) => !open && closeDecision()}
            >
                <DialogContent className="max-h-[90dvh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>
                            {pending?.decision === DECISION_ACCEPTED
                                ? 'Terima sebagai revisi stok?'
                                : 'Tolak revisi?'}
                        </DialogTitle>
                        <DialogDescription>
                            {pending?.decision === DECISION_ACCEPTED
                                ? 'Stok sistem langsung berubah dan keputusan ini tidak bisa dibatalkan. Koreksi setelahnya hanya lewat Revisi SO.'
                                : 'Stok sistem tidak berubah dan baris ini hilang dari tabel. Keputusan ini tidak bisa dibatalkan dan jumlahnya tercatat di laporan.'}
                        </DialogDescription>
                    </DialogHeader>

                    {pending && (
                        <div className="space-y-3">
                            <p className="font-medium break-words">
                                {pending.row.name}
                            </p>
                            <div className="grid grid-cols-3 gap-2 text-center text-sm">
                                <div className="rounded-lg bg-muted/60 p-2">
                                    <p className="text-xs text-muted-foreground">
                                        Sistem
                                    </p>
                                    {pending.row.recorded_stock}
                                </div>
                                <div className="rounded-lg bg-muted/60 p-2">
                                    <p className="text-xs text-muted-foreground">
                                        SO
                                    </p>
                                    {pending.row.counted_stock}
                                </div>
                                <div className="rounded-lg bg-muted/60 p-2">
                                    <p className="text-xs text-muted-foreground">
                                        Selisih
                                    </p>
                                    <DifferenceValue
                                        value={pending.row.difference_stock}
                                    />
                                </div>
                            </div>
                            <Textarea
                                value={note}
                                onChange={(event) =>
                                    setNote(event.target.value)
                                }
                                placeholder="Catatan (opsional)"
                                aria-label="Catatan"
                                disabled={processing}
                            />
                        </div>
                    )}

                    <DialogFooter className="gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            className="h-11 md:h-9"
                            disabled={processing}
                            onClick={closeDecision}
                        >
                            Batal
                        </Button>
                        <Button
                            type="button"
                            className="h-11 md:h-9"
                            variant={
                                pending?.decision === DECISION_ACCEPTED
                                    ? 'default'
                                    : 'destructive'
                            }
                            disabled={processing}
                            onClick={submitDecision}
                        >
                            {processing && <Spinner />}
                            {pending?.decision === DECISION_ACCEPTED
                                ? 'Ya, Terima'
                                : 'Ya, Tolak'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

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
                statement={`Saya, ${sessionEmployeeName(session.pic)}, selaku PJ SO menyatakan telah memeriksa seluruh hasil SO ini dan bertanggung jawab penuh atas setiap keputusan terima dan tolak yang telah saya buat.`}
            >
                <dl className="grid grid-cols-3 gap-2 text-center text-sm">
                    <div className="rounded-lg bg-muted/60 p-2">
                        <dt className="text-xs text-muted-foreground">Cocok</dt>
                        <dd className="font-semibold">{summary.matched}</dd>
                    </div>
                    <div className="rounded-lg bg-muted/60 p-2">
                        <dt className="text-xs text-muted-foreground">
                            Diterima
                        </dt>
                        <dd className="font-semibold">{summary.accepted}</dd>
                    </div>
                    <div className="rounded-lg bg-muted/60 p-2">
                        <dt className="text-xs text-muted-foreground">
                            Ditolak
                        </dt>
                        <dd className="font-semibold">{summary.rejected}</dd>
                    </div>
                </dl>
            </FinishDialog>
        </div>
    );
}
