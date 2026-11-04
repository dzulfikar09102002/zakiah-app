import { useCallback, useEffect, useRef, useState } from 'react';
import productOpnameSessions from '@/routes/product-opname-sessions';

export type QueuedScan = {
    uuid: string;
    barcode?: string | null;
    product_id?: number | null;
};

export type ScanOutcome =
    | {
          kind: 'ok';
          uuid: string;
          name: string;
          counted: number;
      }
    | { kind: 'failed'; uuid: string; message: string }
    /** Belum terkirim (sinyal putus), tersimpan di perangkat dan dikirim ulang otomatis. */
    | { kind: 'queued'; uuid: string };

const RETRY_MS = 5000;
const TIMEOUT_MS = 10000;

export const readCookie = (name: string) => {
    const match = document.cookie.match(
        new RegExp('(?:^|; )' + name + '=([^;]*)'),
    );

    return match ? decodeURIComponent(match[1]) : '';
};

export const newScanUuid = (): string => {
    if (typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    // crypto.randomUUID hanya ada di HTTPS; scanner USB tetap bisa dipakai lewat HTTP.
    const bytes = crypto.getRandomValues(new Uint8Array(16));
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    const hex = Array.from(bytes, (byte) =>
        byte.toString(16).padStart(2, '0'),
    ).join('');

    return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
};

const load = (key: string): QueuedScan[] => {
    try {
        const parsed = JSON.parse(localStorage.getItem(key) ?? '[]');

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
};

const persist = (key: string, queue: QueuedScan[]) => {
    try {
        localStorage.setItem(key, JSON.stringify(queue));
    } catch {
        // Penyimpanan tidak tersedia: antrean tetap jalan di memori.
    }
};

/**
 * Antrean scan yang tersimpan di perangkat. Dikirim berurutan; kalau sinyal putus atau
 * login kedaluwarsa, scan tidak hilang dan dikirim ulang. Server menolak uuid ganda
 * sehingga kirim ulang tidak menambah hitungan.
 */
export function useScanQueue(
    sessionId: number,
    onBackgroundResult: (outcome: ScanOutcome) => void,
) {
    const key = `so-scan-queue-${sessionId}`;
    const queue = useRef<QueuedScan[]>([]);
    const waiters = useRef(new Map<string, (outcome: ScanOutcome) => void>());
    const flushing = useRef(false);
    const retryTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
    const backgroundResult = useRef(onBackgroundResult);
    const [pending, setPending] = useState(0);

    useEffect(() => {
        backgroundResult.current = onBackgroundResult;
    });

    const sync = useCallback(() => {
        persist(key, queue.current);
        setPending(queue.current.length);
    }, [key]);

    const settle = useCallback((outcome: ScanOutcome) => {
        const waiter = waiters.current.get(outcome.uuid);

        if (waiter) {
            waiters.current.delete(outcome.uuid);
            waiter(outcome);
        } else {
            backgroundResult.current(outcome);
        }
    }, []);

    const send = useCallback(
        async (scan: QueuedScan): Promise<ScanOutcome | null> => {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), TIMEOUT_MS);

            try {
                const response = await fetch(
                    productOpnameSessions.storeScan(sessionId).url,
                    {
                        method: 'POST',
                        credentials: 'same-origin',
                        signal: controller.signal,
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-XSRF-TOKEN': readCookie('XSRF-TOKEN'),
                        },
                        body: JSON.stringify(scan),
                    },
                );

                if (response.ok) {
                    const body = await response.json();

                    return {
                        kind: 'ok',
                        uuid: scan.uuid,
                        name: body.name,
                        counted: body.counted,
                    };
                }

                if (response.status === 404) {
                    return {
                        kind: 'failed',
                        uuid: scan.uuid,
                        message: 'Sesi SO ini sudah dibatalkan.',
                    };
                }

                if (response.status === 422 || response.status === 403) {
                    const body = await response.json().catch(() => null);

                    return {
                        kind: 'failed',
                        uuid: scan.uuid,
                        message: body?.message ?? 'Scan ditolak server.',
                    };
                }

                // 401/419 (login kedaluwarsa), 429, 5xx: simpan dan coba lagi.
                return null;
            } catch {
                return null;
            } finally {
                clearTimeout(timeout);
            }
        },
        [sessionId],
    );

    const flush = useCallback(async () => {
        if (flushing.current) {
            return;
        }

        flushing.current = true;

        while (queue.current.length) {
            const scan = queue.current[0];
            const outcome = await send(scan);

            if (!outcome) {
                // Yang masih menunggu diberi tahu "tersimpan", lalu dicoba lagi nanti.
                queue.current.forEach((item) => {
                    const waiter = waiters.current.get(item.uuid);

                    if (waiter) {
                        waiters.current.delete(item.uuid);
                        waiter({ kind: 'queued', uuid: item.uuid });
                    }
                });

                if (retryTimer.current) {
                    clearTimeout(retryTimer.current);
                }

                retryTimer.current = setTimeout(() => {
                    void flush();
                }, RETRY_MS);

                break;
            }

            queue.current = queue.current.slice(1);
            sync();
            settle(outcome);
        }

        flushing.current = false;
    }, [send, settle, sync]);

    useEffect(() => {
        queue.current = load(key);
        setPending(queue.current.length);

        const retry = () => void flush();

        window.addEventListener('online', retry);
        void flush();

        return () => {
            window.removeEventListener('online', retry);

            if (retryTimer.current) {
                clearTimeout(retryTimer.current);
            }
        };
    }, [key, flush]);

    const enqueue = useCallback(
        (scan: Omit<QueuedScan, 'uuid'>): Promise<ScanOutcome> => {
            const item: QueuedScan = { ...scan, uuid: newScanUuid() };

            queue.current = [...queue.current, item];
            sync();

            return new Promise((resolve) => {
                waiters.current.set(item.uuid, resolve);
                void flush();
            });
        },
        [flush, sync],
    );

    return { pending, enqueue };
}
