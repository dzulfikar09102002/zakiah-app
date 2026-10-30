import { useSyncExternalStore } from 'react';

/**
 * Event `beforeinstallprompt` (Chrome/Edge Android & desktop) belum ada di
 * lib.dom TypeScript, jadi didefinisikan minimal di sini.
 */
type BeforeInstallPromptEvent = Event & {
    prompt: () => Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
};

let deferredPrompt: BeforeInstallPromptEvent | null = null;
const listeners = new Set<() => void>();

const notify = () => listeners.forEach((listener) => listener());

export function isStandalone(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        (navigator as Navigator & { standalone?: boolean }).standalone === true
    );
}

export function isIos(): boolean {
    if (typeof navigator === 'undefined') {
        return false;
    }

    return (
        /iphone|ipad|ipod/i.test(navigator.userAgent) ||
        (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
    );
}

/**
 * Daftarkan service worker & tangkap prompt install. Hanya di production,
 * supaya cache tidak mengganggu HMR Vite saat development.
 */
export function initializePwa(): void {
    if (typeof window === 'undefined') {
        return;
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event as BeforeInstallPromptEvent;
        notify();
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        notify();
    });

    if (import.meta.env.PROD && 'serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch((error) => {
                console.error('Gagal mendaftarkan service worker', error);
            });
        });
    }
}

export type InstallState = 'installed' | 'available' | 'ios' | 'unsupported';

function getInstallState(): InstallState {
    if (isStandalone()) {
        return 'installed';
    }

    if (deferredPrompt) {
        return 'available';
    }

    return isIos() ? 'ios' : 'unsupported';
}

function subscribe(listener: () => void) {
    listeners.add(listener);

    return () => {
        listeners.delete(listener);
    };
}

export function usePwaInstall() {
    const state = useSyncExternalStore(
        subscribe,
        getInstallState,
        () => 'unsupported' as InstallState,
    );

    const install = async (): Promise<boolean> => {
        if (!deferredPrompt) {
            return false;
        }

        await deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        deferredPrompt = null;
        notify();

        return outcome === 'accepted';
    };

    return { state, install };
}
