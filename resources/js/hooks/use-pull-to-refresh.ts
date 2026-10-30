import { useEffect, useRef, useState } from 'react';

const THRESHOLD = 72;
const MAX_PULL = 120;

/**
 * Tarik layar ke bawah dari posisi paling atas untuk memuat ulang data.
 * Native pull-to-refresh browser dimatikan selama hook aktif agar tidak
 * memuat ulang seluruh halaman.
 */
export function usePullToRefresh(
    onRefresh: () => Promise<unknown>,
    enabled = true,
) {
    const [pull, setPull] = useState(0);
    const [refreshing, setRefreshing] = useState(false);
    const startY = useRef<number | null>(null);
    const pullRef = useRef(0);
    const refreshRef = useRef(onRefresh);

    useEffect(() => {
        refreshRef.current = onRefresh;
    }, [onRefresh]);

    useEffect(() => {
        if (!enabled) {
            return;
        }

        const root = document.documentElement;
        const previous = root.style.overscrollBehaviorY;
        root.style.overscrollBehaviorY = 'contain';

        const onStart = (event: TouchEvent) => {
            const insideDialog = (event.target as Element | null)?.closest?.(
                '[role="dialog"]',
            );

            startY.current =
                window.scrollY <= 0 &&
                event.touches.length === 1 &&
                !insideDialog
                    ? event.touches[0].clientY
                    : null;
        };

        const onMove = (event: TouchEvent) => {
            if (startY.current === null) {
                return;
            }

            const delta = event.touches[0].clientY - startY.current;

            if (delta <= 0 || window.scrollY > 0) {
                pullRef.current = 0;
                setPull(0);

                return;
            }

            // Efek "karet": makin jauh ditarik makin berat.
            const eased = Math.min(MAX_PULL, delta * 0.5);
            pullRef.current = eased;
            setPull(eased);
        };

        const onEnd = async () => {
            if (startY.current === null) {
                return;
            }

            startY.current = null;
            const reached = pullRef.current >= THRESHOLD;
            pullRef.current = 0;
            setPull(0);

            if (!reached) {
                return;
            }

            setRefreshing(true);
            navigator.vibrate?.(10);

            try {
                await refreshRef.current();
            } finally {
                setRefreshing(false);
            }
        };

        window.addEventListener('touchstart', onStart, { passive: true });
        window.addEventListener('touchmove', onMove, { passive: true });
        window.addEventListener('touchend', onEnd);
        window.addEventListener('touchcancel', onEnd);

        return () => {
            root.style.overscrollBehaviorY = previous;
            window.removeEventListener('touchstart', onStart);
            window.removeEventListener('touchmove', onMove);
            window.removeEventListener('touchend', onEnd);
            window.removeEventListener('touchcancel', onEnd);
        };
    }, [enabled]);

    return { pull, refreshing, progress: Math.min(1, pull / THRESHOLD) };
}
