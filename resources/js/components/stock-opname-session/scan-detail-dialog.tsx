import { useEffect, useState } from 'react';
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
    /** Dipanggil setelah scan dibatalkan, supaya jumlah di layar ikut berubah. */
    onChanged: () => void;
};

/**
 * Rincian setiap scan satu produk dan koreksi scan yang salah. Selama scan dibuka semua
 * anggota sesi bisa memakainya; setelah scan ditutup hanya PJ. Scan lama tetap
 * tersimpan; koreksi dicatat sebagai baris baru beserta alasannya.
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
    const [target, setTarget] = useState<number | null>(null);
    const [reason, setReason] = useState('');
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
        setTarget(null);
        setReason('');
        setScans([]);

        if (productId !== null) {
            void load(productId);
        }
        // Hanya saat produk yang dilihat berganti.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [productId]);

    const cancelScan = async () => {
        if (target === null || productId === null) {
            return;
        }

        setSaving(true);

        try {
            const response = await fetch(
                productOpnameSessions.cancelScan({
                    id: sessionId,
                    scanId: target,
                }).url,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': readCookie('XSRF-TOKEN'),
                    },
                    body: JSON.stringify({ reason: reason.trim() }),
                },
            );

            if (!response.ok) {
                const body = await response.json().catch(() => null);

                toast.error(body?.message ?? 'Scan gagal dibatalkan.');

                return;
            }

            toast.success('Scan dibatalkan');
            setTarget(null);
            setReason('');
            onChanged();
            await load(productId);
        } catch {
            toast.error('Tidak ada koneksi, scan belum dibatalkan.');
        } finally {
            setSaving(false);
        }
    };

    const active = scans.filter((scan) => !scan.cancelled).length;

    return (
        <Dialog
            open={product !== null}
            onOpenChange={(open) => !open && !saving && onClose()}
        >
            <DialogContent className="max-h-[90dvh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Rincian Scan</DialogTitle>
                    <DialogDescription className="break-words">
                        {product?.name} • {active} scan berlaku
                    </DialogDescription>
                </DialogHeader>

                {loading && !scans.length ? (
                    <div className="flex justify-center py-6">
                        <Spinner />
                    </div>
                ) : (
                    <ul className="divide-y rounded-lg border">
                        {scans.map((scan) => (
                            <li key={scan.id} className="space-y-2 p-3">
                                <div className="flex items-center gap-3">
                                    <div
                                        className={cn(
                                            'min-w-0 flex-1',
                                            scan.cancelled &&
                                                'text-muted-foreground line-through',
                                        )}
                                    >
                                        <p className="text-sm font-medium break-words">
                                            {scan.employee}
                                        </p>
                                        <p className="text-xs break-all">
                                            {formatSessionSeconds(
                                                scan.scanned_at,
                                            )}{' '}
                                            • {scan.barcode}
                                        </p>
                                    </div>
                                    {!scan.cancelled && target !== scan.id && (
                                        <Button
                                            type="button"
                                            variant="outline"
                                            className="h-11 shrink-0 text-destructive md:h-9"
                                            disabled={saving}
                                            onClick={() => {
                                                setTarget(scan.id);
                                                setReason('');
                                            }}
                                        >
                                            Batalkan
                                        </Button>
                                    )}
                                </div>

                                {scan.cancelled && (
                                    <p className="text-xs break-words text-muted-foreground">
                                        Dibatalkan {scan.cancelled_by} pada{' '}
                                        {formatSessionSeconds(
                                            scan.cancelled_at,
                                        )}
                                        : {scan.reason}
                                    </p>
                                )}

                                {target === scan.id && (
                                    <div className="space-y-2">
                                        <Textarea
                                            autoFocus
                                            value={reason}
                                            disabled={saving}
                                            maxLength={255}
                                            placeholder="Alasan pembatalan (wajib), mis. barang yang sama ter-scan dua kali"
                                            aria-label="Alasan pembatalan"
                                            className="text-base md:text-sm"
                                            onChange={(event) =>
                                                setReason(event.target.value)
                                            }
                                        />
                                        <div className="flex gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                className="h-11 flex-1 md:h-9"
                                                disabled={saving}
                                                onClick={() => setTarget(null)}
                                            >
                                                Tidak Jadi
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                className="h-11 flex-1 md:h-9"
                                                disabled={
                                                    saving ||
                                                    reason.trim().length < 3
                                                }
                                                onClick={cancelScan}
                                            >
                                                {saving && <Spinner />}
                                                Batalkan Scan
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </li>
                        ))}
                        {!scans.length && !loading && (
                            <li className="p-6 text-center text-sm text-muted-foreground">
                                Belum ada scan untuk produk ini
                            </li>
                        )}
                    </ul>
                )}

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        className="h-11 md:h-9"
                        disabled={saving}
                        onClick={onClose}
                    >
                        Tutup
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
