import {
    ArrowLeft,
    ArrowRight,
    ChevronDown,
    Hand,
    ListChecks,
    Minus,
    MinusCircle,
    Plus,
    PlusCircle,
    ScanBarcode,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupButton,
    InputGroupInput,
} from '@/components/ui/input-group';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import productOpnameSessions from '@/routes/product-opname-sessions';
import { formatSessionSeconds } from './helpers';
import type { ProductScan } from './types';
import { readCookie } from './use-scan-queue';

type Props = {
    sessionId: number;
    /** Produk yang sedang dilihat; null berarti dialog tertutup. */
    product: { id: number; name: string } | null;
    onClose: () => void;
    /** Dipanggil setelah scan ditambah atau dibatalkan, supaya jumlah di layar ikut berubah. */
    onChanged: () => void;
};

type Mode = 'menu' | 'add' | 'cancel';

const DEFAULT_REASON = 'Kelebihan Scan';

/** Sama dengan ProductOpnameScanService::MAX_MANUAL_QTY. */
const MAX_PER_SAVE = 500;

const QUICK_STEPS = [5, 10, 25];

const jsonHeaders = () => ({
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-XSRF-TOKEN': readCookie('XSRF-TOKEN'),
});

/**
 * Rincian scan satu produk dengan dua aksi yang bentuknya sama: tambah atau batalkan
 * sejumlah scan (jumlah bisa diketik atau diatur dengan − / +). Selama scan dibuka semua
 * anggota sesi bisa memakainya; setelah scan ditutup hanya PJ. Scan lama tidak pernah
 * diubah: tambah dicatat sebagai baris qty 1, batal sebagai baris qty -1 beserta alasannya,
 * dimulai dari scan terbaru.
 */
export default function ScanDetailDialog({
    sessionId,
    product,
    onClose,
    onChanged,
}: Props) {
    const productId = product?.id ?? null;
    const [scans, setScans] = useState<ProductScan[]>([]);
    const [loading, setLoading] = useState(false);
    const [mode, setMode] = useState<Mode>('menu');
    const [reason, setReason] = useState(DEFAULT_REASON);
    // Disimpan sebagai teks supaya kolom bisa dikosongkan sementara saat diketik ulang.
    const [qtyText, setQtyText] = useState('1');
    const [saving, setSaving] = useState(false);

    const load = async (id: number) => {
        setLoading(true);

        try {
            const response = await fetch(
                productOpnameSessions.productScans({
                    id: sessionId,
                    productId: id,
                }).url,
                {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
            );

            if (!response.ok) {
                throw new Error();
            }

            setScans(await response.json());
        } catch {
            toast.error('Rincian scan gagal dimuat, silakan coba lagi.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        setMode('menu');
        setReason(DEFAULT_REASON);
        setQtyText('1');
        setScans([]);

        if (productId !== null) {
            void load(productId);
        }
        // Hanya saat produk yang dilihat berganti.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [productId]);

    const active = scans.filter((scan) => !scan.cancelled).length;
    const max =
        mode === 'cancel' ? Math.min(active, MAX_PER_SAVE) : MAX_PER_SAVE;
    const qty = Number(qtyText);
    const qtyValid = Number.isInteger(qty) && qty >= 1 && qty <= max;
    const reasonValid = reason.trim().length >= 3;
    const after = mode === 'cancel' ? active - qty : active + qty;

    const goTo = (next: Mode) => {
        setMode(next);
        setReason(DEFAULT_REASON);
        setQtyText('1');
    };

    const submit = async () => {
        if (productId === null || !qtyValid) {
            return;
        }

        const isCancel = mode === 'cancel';

        if (isCancel && !reasonValid) {
            return;
        }

        setSaving(true);

        try {
            const response = await fetch(
                isCancel
                    ? productOpnameSessions.cancelScans({
                          id: sessionId,
                          productId,
                      }).url
                    : productOpnameSessions.addScans({
                          id: sessionId,
                          productId,
                      }).url,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: jsonHeaders(),
                    body: JSON.stringify(
                        isCancel ? { qty, reason: reason.trim() } : { qty },
                    ),
                },
            );

            if (!response.ok) {
                const data = await response.json().catch(() => null);
                const firstError = data?.errors
                    ? (Object.values(data.errors).flat()[0] as string)
                    : null;

                toast.error(firstError ?? data?.message ?? 'Gagal disimpan.');

                return;
            }

            toast.success(
                isCancel
                    ? `${qty} scan dibatalkan, sisa ${after}`
                    : `${qty} scan ditambahkan, total ${after}`,
            );
            onChanged();
            onClose();
        } catch {
            toast.error('Tidak ada koneksi, perubahan belum tersimpan.');
        } finally {
            setSaving(false);
        }
    };

    const tone = mode === 'cancel' ? 'cancel' : 'add';

    return (
        <Dialog
            open={product !== null}
            onOpenChange={(open) => !open && !saving && onClose()}
        >
            <DialogContent className="max-h-[90dvh] gap-5 overflow-y-auto">
                <DialogHeader className="text-left">
                    <div className="flex items-start gap-3">
                        <span
                            className={cn(
                                'flex size-11 shrink-0 items-center justify-center rounded-xl text-white shadow-md',
                                mode === 'add' &&
                                    'bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-emerald-500/30',
                                mode === 'cancel' &&
                                    'bg-gradient-to-br from-red-500 to-rose-600 shadow-red-500/30',
                                mode === 'menu' &&
                                    'bg-gradient-to-br from-primary to-primary/70 text-primary-foreground shadow-primary/25',
                            )}
                        >
                            {mode === 'add' ? (
                                <PlusCircle className="size-5" />
                            ) : mode === 'cancel' ? (
                                <MinusCircle className="size-5" />
                            ) : (
                                <ListChecks className="size-5" />
                            )}
                        </span>
                        <div className="min-w-0 space-y-1">
                            <DialogTitle>
                                {mode === 'add'
                                    ? 'Tambah Scan'
                                    : mode === 'cancel'
                                      ? 'Batalkan Scan'
                                      : 'Rincian Scan'}
                            </DialogTitle>
                            <DialogDescription className="break-words">
                                {product?.name}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                {/* Jumlah berlaku saat ini, dan hasilnya setelah disimpan */}
                <div className="relative overflow-hidden rounded-2xl border border-primary/15 bg-gradient-to-br from-primary/10 via-primary/5 to-transparent p-4">
                    <ScanBarcode
                        aria-hidden="true"
                        className="absolute -right-3 -bottom-4 size-24 text-primary/10"
                    />
                    <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                        Scan berlaku
                    </p>
                    <p className="mt-1 flex items-baseline gap-2">
                        {loading && !scans.length ? (
                            <Spinner />
                        ) : (
                            <span className="text-4xl font-bold tracking-tight text-primary tabular-nums">
                                {active}
                            </span>
                        )}
                        {mode !== 'menu' && qtyValid && (
                            <span
                                className={cn(
                                    'flex items-center gap-1.5 text-lg font-semibold tabular-nums',
                                    mode === 'add'
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-destructive',
                                )}
                            >
                                <ArrowRight className="size-4" />
                                {after}
                            </span>
                        )}
                    </p>
                </div>

                {mode === 'menu' && (
                    <div className="grid grid-cols-2 gap-3">
                        <ActionTile
                            tone="add"
                            icon={<Plus className="size-6" />}
                            title="Tambah Scan"
                            hint="Barang belum ter-scan"
                            disabled={loading}
                            onClick={() => goTo('add')}
                        />
                        <ActionTile
                            tone="cancel"
                            icon={<Minus className="size-6" />}
                            title="Batalkan Scan"
                            hint={
                                active
                                    ? 'Kurangi yang berlebih'
                                    : 'Belum ada scan'
                            }
                            disabled={loading || active === 0}
                            onClick={() => goTo('cancel')}
                        />
                    </div>
                )}

                {mode !== 'menu' && (
                    <div className="space-y-4">
                        <QtyStepper
                            tone={tone}
                            label={
                                mode === 'add'
                                    ? 'Jumlah barang yang ditambahkan'
                                    : 'Jumlah scan yang dibatalkan'
                            }
                            value={qtyText}
                            max={max}
                            valid={qtyValid}
                            disabled={saving}
                            onChange={setQtyText}
                            onSubmit={() => void submit()}
                        />

                        {mode === 'cancel' && (
                            <div className="space-y-2">
                                <label
                                    htmlFor="cancel-scan-reason"
                                    className="text-sm font-medium"
                                >
                                    Alasan pembatalan
                                </label>
                                <Textarea
                                    id="cancel-scan-reason"
                                    value={reason}
                                    disabled={saving}
                                    maxLength={255}
                                    rows={2}
                                    placeholder="Alasan pembatalan (wajib), mis. Kelebihan Scan"
                                    aria-invalid={!reasonValid}
                                    className="text-base md:text-sm"
                                    onChange={(event) =>
                                        setReason(event.target.value)
                                    }
                                />
                                {!reasonValid && (
                                    <p className="text-xs text-destructive">
                                        Alasan wajib diisi, minimal 3 huruf.
                                    </p>
                                )}
                            </div>
                        )}

                        <p className="flex items-start gap-2 rounded-xl bg-muted/60 p-3 text-xs leading-relaxed text-muted-foreground">
                            <Hand className="mt-0.5 size-4 shrink-0" />
                            {mode === 'add'
                                ? 'Dicatat atas nama Anda sebagai scan manual. Kalau salah, kurangi lagi lewat Batalkan Scan.'
                                : 'Yang dibatalkan scan terbaru lebih dulu. Scan lama tetap tersimpan, pembatalan dicatat atas nama Anda beserta alasannya.'}
                        </p>

                        {mode === 'cancel' && <ScanHistory scans={scans} />}
                    </div>
                )}

                <DialogFooter className="flex-row gap-2">
                    {mode === 'menu' ? (
                        <Button
                            type="button"
                            variant="outline"
                            className="h-11 flex-1 md:h-9 md:flex-none"
                            onClick={onClose}
                        >
                            Tutup
                        </Button>
                    ) : (
                        <>
                            <Button
                                type="button"
                                variant="outline"
                                className="h-11 flex-1 md:h-9 md:flex-none"
                                disabled={saving}
                                onClick={() => goTo('menu')}
                            >
                                <ArrowLeft /> Kembali
                            </Button>
                            <Button
                                type="button"
                                variant={
                                    mode === 'cancel'
                                        ? 'destructive'
                                        : 'default'
                                }
                                className={cn(
                                    'h-11 flex-1 md:h-9 md:flex-none',
                                    mode === 'add' &&
                                        'bg-emerald-600 text-white hover:bg-emerald-600/90',
                                )}
                                disabled={
                                    saving ||
                                    !qtyValid ||
                                    (mode === 'cancel' && !reasonValid)
                                }
                                onClick={() => void submit()}
                            >
                                {saving ? (
                                    <Spinner />
                                ) : mode === 'cancel' ? (
                                    <Minus />
                                ) : (
                                    <Plus />
                                )}
                                {mode === 'cancel' ? 'Batalkan' : 'Tambah'}{' '}
                                {qtyValid ? qty : ''} Scan
                            </Button>
                        </>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

/** Kolom jumlah dengan − / + dan tombol cepat, sama seperti di modal tambah produk. */
function QtyStepper({
    tone,
    label,
    value,
    max,
    valid,
    disabled,
    onChange,
    onSubmit,
}: {
    tone: 'add' | 'cancel';
    label: string;
    value: string;
    max: number;
    valid: boolean;
    disabled: boolean;
    onChange: (value: string) => void;
    onSubmit: () => void;
}) {
    const current = Number(value);
    const clamp = (next: number) =>
        String(Math.min(max, Math.max(1, Math.trunc(next) || 1)));
    const step = (delta: number) =>
        onChange(clamp((valid ? current : 0) + delta));

    return (
        <div className="space-y-3">
            <div className="space-y-2">
                <label htmlFor="scan-qty" className="text-sm font-medium">
                    {label}
                </label>
                <InputGroup className="h-16 rounded-2xl">
                    <InputGroupAddon align="inline-start">
                        <InputGroupButton
                            size="icon-sm"
                            variant="secondary"
                            aria-label="Kurangi jumlah"
                            className="size-12 rounded-xl [&>svg]:size-5"
                            disabled={disabled || (valid && current <= 1)}
                            onClick={() => step(-1)}
                        >
                            {/* Di mode batal tanda mengikuti stok: mengurangi jumlah batal = stok bertambah. */}
                            {tone === 'add' ? <Minus /> : <Plus />}
                        </InputGroupButton>
                    </InputGroupAddon>
                    <InputGroupInput
                        id="scan-qty"
                        type="number"
                        inputMode="numeric"
                        min={1}
                        max={max}
                        autoFocus
                        value={value}
                        disabled={disabled}
                        aria-invalid={!valid}
                        onFocus={(event) => event.target.select()}
                        onChange={(event) => onChange(event.target.value)}
                        onBlur={() => onChange(clamp(current))}
                        onKeyDown={(event) => {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                                onSubmit();
                            }
                        }}
                        className="[appearance:textfield] text-center text-3xl font-bold tabular-nums md:text-3xl [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                    />
                    <InputGroupAddon align="inline-end">
                        <InputGroupButton
                            size="icon-sm"
                            aria-label="Tambah jumlah"
                            className={cn(
                                'size-12 rounded-xl text-white [&>svg]:size-5',
                                tone === 'add'
                                    ? 'bg-emerald-600 hover:bg-emerald-600/90 hover:text-white'
                                    : 'bg-destructive hover:bg-destructive/90 hover:text-white',
                            )}
                            disabled={disabled || (valid && current >= max)}
                            onClick={() => step(1)}
                        >
                            {tone === 'add' ? <Plus /> : <Minus />}
                        </InputGroupButton>
                    </InputGroupAddon>
                </InputGroup>
                {!valid && (
                    <p className="text-xs text-destructive">
                        Isi angka 1 sampai {max}.
                    </p>
                )}
            </div>

            <div className="flex gap-2">
                {QUICK_STEPS.map((quick) => (
                    <Button
                        key={quick}
                        type="button"
                        variant="outline"
                        className={cn(
                            'h-11 flex-1 rounded-full font-semibold tabular-nums md:h-9',
                            tone === 'add'
                                ? 'text-emerald-700 dark:text-emerald-400'
                                : 'text-destructive',
                        )}
                        disabled={disabled || (valid && current >= max)}
                        onClick={() => step(quick)}
                    >
                        {/* Batal mengurangi stok terhitung, jadi tandanya minus. */}
                        {tone === 'add' ? '+' : '−'}
                        {quick}
                    </Button>
                ))}
                {tone === 'cancel' && (
                    <Button
                        type="button"
                        variant="outline"
                        className="h-11 flex-1 rounded-full font-semibold md:h-9"
                        disabled={disabled || (valid && current === max)}
                        onClick={() => onChange(String(max))}
                    >
                        Semua
                    </Button>
                )}
            </div>
        </div>
    );
}

/** Riwayat scan, dilipat supaya modal tetap ringkas. */
function ScanHistory({ scans }: { scans: ProductScan[] }) {
    if (!scans.length) {
        return null;
    }

    return (
        <details className="group rounded-xl border">
            <summary className="flex cursor-pointer list-none items-center justify-between gap-2 p-3 text-sm font-medium [&::-webkit-details-marker]:hidden">
                Riwayat scan ({scans.length})
                <ChevronDown className="size-4 text-muted-foreground transition-transform group-open:rotate-180" />
            </summary>
            <ul className="max-h-60 divide-y overflow-y-auto border-t">
                {scans.map((scan) => (
                    <li key={scan.id} className="space-y-0.5 px-3 py-2">
                        <p
                            className={cn(
                                'flex flex-wrap items-center gap-1.5 text-sm font-medium break-words',
                                scan.cancelled &&
                                    'text-muted-foreground line-through',
                            )}
                        >
                            {scan.employee}
                            {scan.manual && (
                                <span className="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-semibold tracking-wide text-primary uppercase">
                                    Manual
                                </span>
                            )}
                        </p>
                        <p className="text-xs break-all text-muted-foreground">
                            {formatSessionSeconds(scan.scanned_at)} •{' '}
                            {scan.barcode}
                        </p>
                        {scan.cancelled && (
                            <p className="text-xs break-words text-muted-foreground">
                                Dibatalkan {scan.cancelled_by} pada{' '}
                                {formatSessionSeconds(scan.cancelled_at)}:{' '}
                                {scan.reason}
                            </p>
                        )}
                    </li>
                ))}
            </ul>
        </details>
    );
}

function ActionTile({
    tone,
    icon,
    title,
    hint,
    disabled,
    onClick,
}: {
    tone: 'add' | 'cancel';
    icon: ReactNode;
    title: string;
    hint: string;
    disabled?: boolean;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            disabled={disabled}
            onClick={onClick}
            className={cn(
                'group flex min-h-32 cursor-pointer flex-col items-start justify-between gap-3 rounded-2xl border p-4 text-left transition-all duration-200 outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 motion-safe:hover:-translate-y-0.5 motion-safe:active:translate-y-0',
                tone === 'add'
                    ? 'border-emerald-500/30 bg-emerald-500/8 hover:border-emerald-500/60 hover:bg-emerald-500/12 hover:shadow-lg hover:shadow-emerald-500/15'
                    : 'border-destructive/30 bg-destructive/6 hover:border-destructive/60 hover:bg-destructive/10 hover:shadow-lg hover:shadow-destructive/15',
            )}
        >
            <span
                className={cn(
                    'flex size-11 items-center justify-center rounded-xl text-white shadow-md transition-transform duration-200 motion-safe:group-hover:scale-110',
                    tone === 'add'
                        ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-emerald-500/30'
                        : 'bg-gradient-to-br from-red-500 to-rose-600 shadow-red-500/30',
                )}
            >
                {icon}
            </span>
            <span className="space-y-0.5">
                <span
                    className={cn(
                        'block text-sm font-semibold',
                        tone === 'add'
                            ? 'text-emerald-700 dark:text-emerald-400'
                            : 'text-destructive',
                    )}
                >
                    {title}
                </span>
                <span className="block text-xs text-muted-foreground">
                    {hint}
                </span>
            </span>
        </button>
    );
}
