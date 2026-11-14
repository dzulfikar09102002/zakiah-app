import {
    Camera,
    CloudOff,
    ListChecks,
    Lock,
    ScanBarcode,
    Search,
} from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { toast } from 'sonner';
import ProductPicker from '@/components/product-picker';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useIsMobile } from '@/hooks/use-mobile';
import { scanFeedback, unlockScanAudio } from '@/lib/scan-feedback';
import productOpnameSessions from '@/routes/product-opname-sessions';
import { postSessionAction } from './helpers';
import ScanDetailDialog from './scan-detail-dialog';
import ScannerOverlay from './scanner-overlay';
import type { OpnameSession, Viewer } from './types';
import { useLiveScans } from './use-live-scans';
import type { ScanOutcome } from './use-scan-queue';
import { useScanQueue } from './use-scan-queue';

const PAGE_SIZE = 50;

/** Barcode sama dari alat scan dalam jeda ini dianggap satu kali scan. */
const SCANNER_DOUBLE_TRIGGER_MS = 500;

type Props = {
    session: OpnameSession;
    viewer: Viewer;
};

export default function ScanPanel({ session, viewer }: Props) {
    const isMobile = useIsMobile();
    const { items, employees, loaded, refresh } = useLiveScans(
        session.id,
        session.status,
    );
    const { pending, enqueue } = useScanQueue(session.id, (outcome) => {
        if (outcome.kind === 'failed') {
            toast.error(outcome.message);
        }

        void refresh();
    });

    const inputRef = useRef<HTMLInputElement>(null);
    const lastSubmit = useRef({ code: '', at: 0 });
    const [detailProduct, setDetailProduct] = useState<{
        id: number;
        name: string;
    } | null>(null);
    // Di HP kamera langsung terbuka; di komputer kolom scan yang siap dipakai.
    const [cameraOpen, setCameraOpen] = useState(isMobile);
    const [code, setCode] = useState('');
    const [filter, setFilter] = useState('');
    const [visible, setVisible] = useState(PAGE_SIZE);
    const [confirmClose, setConfirmClose] = useState(false);

    const focusInput = () => {
        if (!isMobile) {
            inputRef.current?.focus();
        }
    };

    useEffect(() => {
        focusInput();
        // Hanya saat halaman pertama dibuka.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const handleOutcome = (outcome: ScanOutcome) => {
        if (outcome.kind === 'ok') {
            scanFeedback(true);
            toast.success(`${outcome.name}: +1, total ${outcome.counted}`);
        } else if (outcome.kind === 'failed') {
            scanFeedback(false);
            toast.error(outcome.message);
        } else {
            scanFeedback(true);
            toast.info('Tersimpan di perangkat, dikirim saat sinyal kembali');
        }

        void refresh();

        return outcome;
    };

    const scanCode = (barcode: string) =>
        enqueue({ barcode }).then((outcome) => {
            void refresh();

            return outcome;
        });

    const submitCode = async (event: React.FormEvent) => {
        event.preventDefault();
        unlockScanAudio();

        const value = code.trim();

        if (!value) {
            return;
        }

        // Dikosongkan lebih dulu supaya alat scan bisa langsung membaca barang berikutnya.
        setCode('');
        focusInput();

        // Alat scan yang terpicu dua kali: tidak ada orang men-scan secepat ini.
        const now = Date.now();
        const isTrigger =
            lastSubmit.current.code === value &&
            now - lastSubmit.current.at < SCANNER_DOUBLE_TRIGGER_MS;

        lastSubmit.current = { code: value, at: now };

        if (isTrigger) {
            return;
        }

        handleOutcome(await enqueue({ barcode: value }));
    };

    const shown = useMemo(() => {
        const keyword = filter.trim().toLowerCase();

        return keyword
            ? items.filter((item) =>
                  [item.name, item.barcode, item.sku].some((value) =>
                      value?.toLowerCase().includes(keyword),
                  ),
              )
            : items;
    }, [items, filter]);

    const totalQty = items.reduce((sum, item) => sum + item.counted, 0);

    return (
        <div className="space-y-4">
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <ScanBarcode className="size-5 text-primary" /> Scan
                        Barang
                    </CardTitle>
                    <CardDescription>
                        Gunakan kamera, alat scan, atau ketik barcode. Setiap
                        scan langsung tersimpan.
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                    <Button
                        type="button"
                        className="h-12 w-full md:hidden"
                        onClick={() => {
                            unlockScanAudio();
                            setCameraOpen(true);
                        }}
                    >
                        <Camera /> Buka Kamera
                    </Button>

                    <form onSubmit={submitCode} className="flex gap-2">
                        <Input
                            ref={inputRef}
                            value={code}
                            onChange={(event) => setCode(event.target.value)}
                            placeholder="Scan atau ketik barcode, lalu Enter"
                            autoComplete="off"
                            autoCorrect="off"
                            autoCapitalize="off"
                            spellCheck={false}
                            enterKeyHint="send"
                            aria-label="Barcode"
                            className="h-11 text-base md:h-9 md:text-sm"
                        />
                        <Button
                            type="button"
                            variant="secondary"
                            className="hidden md:inline-flex"
                            onClick={() => {
                                unlockScanAudio();
                                setCameraOpen(true);
                            }}
                        >
                            <Camera /> Kamera
                        </Button>
                    </form>

                    {viewer.is_pic && (
                        <ProductPicker
                            placeholder="Barcode rusak? Cari produk manual (PJ)"
                            className="h-11 md:h-9"
                            onSelect={async (product) => {
                                unlockScanAudio();
                                handleOutcome(
                                    await enqueue({ product_id: product.id }),
                                );
                            }}
                        />
                    )}

                    {pending > 0 && (
                        <p className="flex items-center gap-2 rounded-lg bg-amber-500/12 p-2 text-sm text-amber-700 dark:text-amber-400">
                            <CloudOff className="size-4 shrink-0" />
                            {pending} scan tersimpan di perangkat dan menunggu
                            dikirim. Jangan hapus data browser.
                        </p>
                    )}
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Hasil Scan</CardTitle>
                    <CardDescription>
                        {items.length} produk, {totalQty} barang
                        {employees.length > 0 &&
                            ` • ${employees
                                .map(
                                    (employee) =>
                                        `${employee.name}: ${employee.qty}`,
                                )
                                .join(', ')}`}
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                    <div className="relative">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={filter}
                            onChange={(event) => {
                                setFilter(event.target.value);
                                setVisible(PAGE_SIZE);
                            }}
                            placeholder="Cari di hasil scan"
                            aria-label="Cari di hasil scan"
                            className="h-11 pl-9 text-base md:h-9 md:text-sm"
                        />
                    </div>

                    <ul className="divide-y rounded-lg border">
                        {shown.slice(0, visible).map((item) => (
                            <li
                                key={item.product_id}
                                className="flex items-center gap-3 p-3"
                            >
                                <div className="min-w-0 flex-1">
                                    <p className="font-medium break-words">
                                        {item.name}
                                    </p>
                                    <p className="truncate text-xs text-muted-foreground">
                                        {item.barcode ?? item.sku ?? '-'}
                                    </p>
                                </div>
                                <p className="shrink-0 text-right">
                                    <span className="text-lg font-semibold">
                                        {item.counted}
                                    </span>{' '}
                                    <span className="text-xs text-muted-foreground">
                                        {item.unit_name}
                                    </span>
                                </p>
                                {viewer.can_correct && (
                                    <Button
                                        type="button"
                                        size="icon"
                                        variant="outline"
                                        aria-label={`Rincian scan ${item.name}`}
                                        title="Rincian scan"
                                        className="size-11 shrink-0 md:size-9"
                                        onClick={() =>
                                            setDetailProduct({
                                                id: item.product_id,
                                                name: item.name,
                                            })
                                        }
                                    >
                                        <ListChecks />
                                    </Button>
                                )}
                            </li>
                        ))}

                        {!shown.length && (
                            <li className="p-6 text-center text-sm text-muted-foreground">
                                {loaded
                                    ? 'Belum ada barang di-scan'
                                    : 'Memuat hasil scan...'}
                            </li>
                        )}
                    </ul>

                    {shown.length > visible && (
                        <Button
                            type="button"
                            variant="outline"
                            className="h-11 w-full md:h-9"
                            onClick={() =>
                                setVisible((current) => current + PAGE_SIZE)
                            }
                        >
                            Tampilkan lebih banyak
                        </Button>
                    )}
                </CardContent>
            </Card>

            {/* Langkah berikutnya setelah semua barang dihitung: di kanan bawah, setelah hasil scan. */}
            {viewer.is_pic && (
                <div className="flex justify-end">
                    <Button
                        type="button"
                        className="h-11 w-full md:h-9 md:w-auto"
                        onClick={() => setConfirmClose(true)}
                    >
                        <Lock /> Tutup Scan dan Review
                    </Button>
                </div>
            )}

            <ScanDetailDialog
                sessionId={session.id}
                product={detailProduct}
                onClose={() => setDetailProduct(null)}
                onChanged={() => void refresh()}
            />

            {cameraOpen && (
                <ScannerOverlay
                    pending={pending}
                    onScan={scanCode}
                    onClose={() => {
                        setCameraOpen(false);
                        focusInput();
                    }}
                />
            )}

            <AlertDialog open={confirmClose} onOpenChange={setConfirmClose}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Tutup scan?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Semua petugas tidak bisa scan lagi selama Anda
                            mereview. Pastikan semua barang sudah dihitung. Scan
                            bisa dibuka kembali untuk hitung ulang.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Batal</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={() =>
                                postSessionAction(
                                    productOpnameSessions.closeScan(session.id)
                                        .url,
                                    {},
                                    { success: 'Scan ditutup, silakan review' },
                                )
                            }
                        >
                            Tutup Scan
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    );
}
