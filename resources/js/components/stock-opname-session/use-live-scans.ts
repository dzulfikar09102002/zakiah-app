import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import productOpnameSessions from '@/routes/product-opname-sessions';
import type {
    LiveEmployee,
    LiveItem,
    LiveResponse,
    SessionStatus,
} from './types';

const POLL_MS = 2500;

/**
 * Daftar hasil scan yang selalu mengikuti server. Hanya produk yang berubah sejak
 * cursor terakhir yang diambil, dan polling berhenti saat aplikasi di latar belakang.
 */
export function useLiveScans(sessionId: number, status: SessionStatus) {
    const [items, setItems] = useState<LiveItem[]>([]);
    const [employees, setEmployees] = useState<LiveEmployee[]>([]);
    const [loaded, setLoaded] = useState(false);
    const cursor = useRef(0);
    const busy = useRef(false);
    const again = useRef(false);

    const refresh = useCallback(async () => {
        // Scan baru saat pengambilan masih jalan: ambil sekali lagi supaya langsung tampil.
        if (busy.current) {
            again.current = true;

            return;
        }

        busy.current = true;

        try {
            do {
                again.current = false;

                const response = await fetch(
                    productOpnameSessions.live(sessionId, {
                        query: { since: cursor.current },
                    }).url,
                    {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    },
                );

                // PJ membatalkan sesi: petugas lain dikembalikan ke daftar.
                if (response.status === 404) {
                    toast.error('Sesi SO ini sudah dibatalkan oleh PJ.');
                    router.visit(productOpnameSessions.index().url);

                    return;
                }

                if (!response.ok) {
                    return;
                }

                const body: LiveResponse = await response.json();

                cursor.current = body.cursor;
                setEmployees(body.employees);
                setLoaded(true);

                if (body.items.length) {
                    setItems((current) => {
                        const merged = new Map(
                            current.map((item) => [item.product_id, item]),
                        );

                        body.items.forEach((item) =>
                            merged.set(item.product_id, item),
                        );

                        return Array.from(merged.values())
                            .filter((item) => item.counted !== 0)
                            .sort((a, b) => b.last_id - a.last_id);
                    });
                }

                // PJ menutup scan atau mengakhiri sesi: pindah ke tampilan yang sesuai.
                if (body.status !== status) {
                    router.reload();
                }
            } while (again.current);
        } catch {
            // Sinyal putus: dicoba lagi pada putaran berikutnya.
        } finally {
            busy.current = false;
        }
    }, [sessionId, status]);

    useEffect(() => {
        const timer = setInterval(() => {
            if (!document.hidden) {
                void refresh();
            }
        }, POLL_MS);

        const onVisible = () => {
            if (!document.hidden) {
                void refresh();
            }
        };

        document.addEventListener('visibilitychange', onVisible);
        void refresh();

        return () => {
            clearInterval(timer);
            document.removeEventListener('visibilitychange', onVisible);
        };
    }, [refresh]);

    return { items, employees, loaded, refresh };
}
