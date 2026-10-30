/// <reference types="vite/client" />

interface Window {
    /** Menutup splash screen; didefinisikan di resources/views/partials/splash.blade.php. */
    __hideSplash?: () => void;
}
