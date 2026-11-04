import productAdjustmentStocks from '@/routes/product-adjustment-stocks';
import productOpnameServices from '@/routes/product-opname-services';
import productOpnameSessions from '@/routes/product-opname-sessions';
import type { CountDocumentConfig } from './count-document';

export const opnameConfig: CountDocumentConfig = {
    title: 'Stok Opname',
    noun: 'stok opname',
    description:
        'Dokumen hasil opname. Pembuatan SO sekarang lewat sesi SO; halaman ini untuk detail dan persetujuan dokumen lama.',
    detailsKey: 'product_opname_service_details',
    editable: false,
    autoApproveDefault: false,
    routes: {
        index: () => productOpnameSessions.index(),
        create: () => productOpnameSessions.create(),
        store: () => productOpnameSessions.store(),
        show: (id) => productOpnameServices.show(id),
        approve: (id) => productOpnameServices.approve(id),
        reject: (id) => productOpnameServices.reject(id),
    },
};

export const adjustmentConfig: CountDocumentConfig = {
    title: 'Penyesuaian Stok',
    noun: 'penyesuaian stok',
    description:
        'Sesuaikan stok produk di lokasi berdasarkan stok fisik yang sebenarnya.',
    detailsKey: 'product_adjustment_stock_details',
    editable: false,
    autoApproveDefault: true,
    routes: {
        index: () => productAdjustmentStocks.index(),
        create: () => productAdjustmentStocks.create(),
        store: () => productAdjustmentStocks.store(),
        show: (id) => productAdjustmentStocks.show(id),
        approve: (id) => productAdjustmentStocks.approve(id),
        reject: (id) => productAdjustmentStocks.reject(id),
    },
};
