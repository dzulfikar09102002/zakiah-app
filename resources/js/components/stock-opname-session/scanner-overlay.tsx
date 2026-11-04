import {
    CheckCircle2,
    CloudOff,
    Flashlight,
    FlashlightOff,
    X,
    XCircle,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import type { ScannerHandle } from '@/lib/barcode-scanner';
import { cameraErrorMessage, startBarcodeScanner } from '@/lib/barcode-scanner';
import { scanFeedback, unlockScanAudio } from '@/lib/scan-feedback';
import { cn } from '@/lib/utils';
import type { ScanOutcome } from './use-scan-queue';

/** Barcode yang sama harus hilang dari kamera selama ini sebelum dihitung lagi. */
const SAME_CODE_GAP_MS = 1500;

type Result = {
    key: string;
    tone: 'ok' | 'fail' | 'queued';
    title: string;
    detail: string;
};

type Props = {
    pending: number;
    onClose: () => void;
    onScan: (barcode: string) => Promise<ScanOutcome>;
};

/**
 * Kamera layar penuh untuk HP dan komputer. Menutupi bottom nav dan mengikuti
 * safe area supaya tombolnya tidak tertutup notch atau garis home iPhone.
 */
export default function ScannerOverlay({ pending, onClose, onScan }: Props) {
    const video = useRef<HTMLVideoElement>(null);
    const handle = useRef<ScannerHandle | null>(null);
    const lastCode = useRef('');
    const lastSeen = useRef(0);
    const busy = useRef(false);
    const flashTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    const [starting, setStarting] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [torchAvailable, setTorchAvailable] = useState(false);
    const [torchOn, setTorchOn] = useState(false);
    const [flash, setFlash] = useState<'ok' | 'fail' | null>(null);
    const [results, setResults] = useState<Result[]>([]);
    const [count, setCount] = useState(0);

    const onScanRef = useRef(onScan);

    useEffect(() => {
        onScanRef.current = onScan;
    });

    useEffect(() => {
        unlockScanAudio();
        document.body.style.overflow = 'hidden';

        let cancelled = false;
        let wakeLock: { release: () => Promise<void> } | null = null;

        const keepAwake = async () => {
            try {
                const lock = await (
                    navigator as unknown as {
                        wakeLock?: {
                            request: (type: 'screen') => Promise<{
                                release: () => Promise<void>;
                            }>;
                        };
                    }
                ).wakeLock?.request('screen');

                wakeLock = lock ?? null;
            } catch {
                // Layar tetap menyala hanya pelengkap.
            }
        };

        const handleCode = async (code: string) => {
            const now = Date.now();

            if (
                busy.current ||
                !code ||
                (code === lastCode.current &&
                    now - lastSeen.current < SAME_CODE_GAP_MS)
            ) {
                if (code === lastCode.current) {
                    lastSeen.current = now;
                }

                return;
            }

            busy.current = true;
            lastCode.current = code;
            lastSeen.current = now;

            const outcome = await onScanRef.current(code);

            if (cancelled) {
                return;
            }

            const ok = outcome.kind !== 'failed';

            scanFeedback(ok);
            setFlash(ok ? 'ok' : 'fail');

            if (flashTimer.current) {
                clearTimeout(flashTimer.current);
            }

            flashTimer.current = setTimeout(() => setFlash(null), 600);

            const result: Result =
                outcome.kind === 'ok'
                    ? {
                          key: outcome.uuid,
                          tone: 'ok',
                          title: outcome.name,
                          detail: `+1 • total ${outcome.counted}`,
                      }
                    : outcome.kind === 'queued'
                      ? {
                            key: outcome.uuid,
                            tone: 'queued',
                            title: code,
                            detail: 'Tersimpan di perangkat, dikirim saat sinyal kembali',
                        }
                      : {
                            key: outcome.uuid,
                            tone: 'fail',
                            title: 'Scan gagal',
                            detail: outcome.message,
                        };

            setResults((current) => [result, ...current].slice(0, 4));

            if (ok) {
                setCount((current) => current + 1);
            }

            lastSeen.current = Date.now();
            busy.current = false;
        };

        const start = async () => {
            if (!video.current || handle.current) {
                return;
            }

            setStarting(true);

            try {
                const scanner = await startBarcodeScanner(
                    video.current,
                    (code) => void handleCode(code),
                );

                if (cancelled) {
                    scanner.stop();

                    return;
                }

                handle.current = scanner;
                setTorchAvailable(scanner.setTorch !== null);
                setError(null);
            } catch (exception) {
                if (!cancelled) {
                    setError(cameraErrorMessage(exception));
                }
            } finally {
                if (!cancelled) {
                    setStarting(false);
                }
            }
        };

        const stop = () => {
            handle.current?.stop();
            handle.current = null;
        };

        // Saat aplikasi PWA kembali dari latar belakang, kamera perlu dibuka lagi.
        const onVisibility = () => {
            if (document.hidden) {
                stop();
                setTorchOn(false);
            } else {
                void keepAwake();
                void start();
            }
        };

        document.addEventListener('visibilitychange', onVisibility);
        void keepAwake();
        void start();

        return () => {
            cancelled = true;
            document.removeEventListener('visibilitychange', onVisibility);
            document.body.style.overflow = '';
            stop();
            void wakeLock?.release().catch(() => {});

            if (flashTimer.current) {
                clearTimeout(flashTimer.current);
            }
        };
    }, []);

    const toggleTorch = async () => {
        const setTorch = handle.current?.setTorch;

        if (setTorch) {
            setTorchOn(await setTorch(!torchOn));
        }
    };

    return createPortal(
        <div
            role="dialog"
            aria-modal="true"
            aria-label="Scan barcode"
            className="fixed inset-0 z-50 flex flex-col bg-black text-white md:inset-6 md:mx-auto md:max-w-2xl md:overflow-hidden md:rounded-xl"
        >
            <div className="flex shrink-0 items-center gap-2 px-3 pt-[calc(env(safe-area-inset-top)+10px)] pb-2">
                <p className="min-w-0 flex-1 truncate font-semibold">
                    Scan Barcode
                </p>
                {pending > 0 && (
                    <span className="flex items-center gap-1 rounded-full bg-amber-500/20 px-2 py-1 text-xs text-amber-300">
                        <CloudOff className="size-3.5" /> {pending} menunggu
                    </span>
                )}
                {torchAvailable && (
                    <Button
                        type="button"
                        size="icon"
                        variant="ghost"
                        aria-label={
                            torchOn ? 'Matikan senter' : 'Nyalakan senter'
                        }
                        className="size-11 text-white hover:bg-white/10 hover:text-white"
                        onClick={toggleTorch}
                    >
                        {torchOn ? <FlashlightOff /> : <Flashlight />}
                    </Button>
                )}
                <Button
                    type="button"
                    size="icon"
                    variant="ghost"
                    aria-label="Tutup kamera"
                    className="size-11 text-white hover:bg-white/10 hover:text-white"
                    onClick={onClose}
                >
                    <X />
                </Button>
            </div>

            <div className="relative min-h-0 flex-1 overflow-hidden">
                <video
                    ref={video}
                    playsInline
                    muted
                    autoPlay
                    className="absolute inset-0 size-full object-cover"
                />

                <div className="pointer-events-none absolute inset-0 flex items-center justify-center p-8">
                    <div
                        className={cn(
                            'aspect-[16/9] w-full max-w-md rounded-xl border-4 transition-colors',
                            flash === 'ok' && 'border-emerald-400',
                            flash === 'fail' && 'border-red-500',
                            flash === null && 'border-white/70',
                        )}
                    />
                </div>

                {starting && !error && (
                    <div className="absolute inset-0 flex items-center justify-center gap-2 text-sm">
                        <Spinner /> Membuka kamera...
                    </div>
                )}

                {error && (
                    <div className="absolute inset-0 flex items-center justify-center bg-black/80 p-6 text-center text-sm">
                        {error}
                    </div>
                )}
            </div>

            <div className="shrink-0 space-y-2 bg-background px-3 pt-3 pb-[calc(env(safe-area-inset-bottom)+12px)] text-foreground">
                <div className="space-y-1" aria-live="polite">
                    {results.length === 0 && (
                        <p className="py-1 text-center text-xs text-muted-foreground">
                            Scan barang satu per satu. Untuk barang dengan
                            barcode sama, jauhkan kamera lalu arahkan lagi.
                        </p>
                    )}
                    {results.map((result) => (
                        <div
                            key={result.key}
                            className="flex items-center gap-2 rounded-lg border px-2 py-1.5"
                        >
                            {result.tone === 'ok' && (
                                <CheckCircle2 className="size-5 shrink-0 text-emerald-600" />
                            )}
                            {result.tone === 'queued' && (
                                <CloudOff className="size-5 shrink-0 text-amber-600" />
                            )}
                            {result.tone === 'fail' && (
                                <XCircle className="size-5 shrink-0 text-destructive" />
                            )}
                            <div className="min-w-0">
                                <p className="text-sm font-semibold break-words">
                                    {result.title}
                                </p>
                                <p className="truncate text-xs text-muted-foreground">
                                    {result.detail}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>

                <Button type="button" className="h-11 w-full" onClick={onClose}>
                    Selesai{count > 0 ? ` (${count})` : ''}
                </Button>
            </div>
        </div>,
        document.body,
    );
}
