import type { StockCountDetail, StockCountDocument } from '@/lib/model';

type RouteResult = { url: string };
type IdRoute = (id: number) => RouteResult;

/**
 * Konfigurasi halaman dokumen hitung stok, supaya Stok Opname & Penyesuaian Stok
 * memakai komponen yang sama.
 */
export type CountDocumentConfig = {
    /** Judul menu, mis. "Stok Opname". */
    title: string;
    /** Kata benda huruf kecil untuk pesan, mis. "stok opname". */
    noun: string;
    description: string;
    detailsKey:
        | 'product_opname_service_details'
        | 'product_adjustment_stock_details';
    /** Dokumen berstatus "diajukan" masih bisa diubah / dihapus. */
    editable: boolean;
    /** Nilai awal opsi "langsung setujui" di form. */
    autoApproveDefault: boolean;
    routes: {
        index: () => RouteResult;
        create: () => RouteResult;
        store: () => RouteResult;
        show: IdRoute;
        approve: IdRoute;
        reject: IdRoute;
        edit?: IdRoute;
        update?: IdRoute;
        destroy?: IdRoute;
    };
};

export const documentDetails = (
    document: StockCountDocument,
    config: CountDocumentConfig,
): StockCountDetail[] => document[config.detailsKey] ?? [];
