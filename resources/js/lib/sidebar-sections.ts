/**
 * Status buka-tutup header dan group di sidebar, disimpan di browser.
 * Direset saat masuk halaman login, supaya setelah login semua menu mulai tertutup.
 */
const STORAGE_KEY = 'sidebar-open-sections';

export function readOpenSections(): Record<string, boolean> {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '{}');
    } catch {
        return {};
    }
}

export function writeOpenSection(key: string, open: boolean): void {
    try {
        localStorage.setItem(
            STORAGE_KEY,
            JSON.stringify({ ...readOpenSections(), [key]: open }),
        );
    } catch {
        // Penyimpanan diblokir (mode privat): cukup tidak diingat.
    }
}

export function resetOpenSections(): void {
    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch {
        // Penyimpanan diblokir: tidak ada yang perlu dihapus.
    }
}
