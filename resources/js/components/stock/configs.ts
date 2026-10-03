import productAdjustmentStocks from '@/routes/product-adjustment-stocks';
import productOpnameServices from '@/routes/product-opname-services';
import type { CountDocumentConfig } from './count-document';

export const opnameConfig: CountDocumentConfig = {
    title: 'Stok Opname',
    noun: 'stok opname',
    description:
        'Catat hasil hitung fisik stok. Stok baru disesuaikan setelah opname disetujui.',
    detailsKey: 'product_opname_service_details',
    editable: true,
    autoApproveDefault: false,
    routes: {
        index: () => productOpnameServices.index(),
        create: () => productOpnameServices.create(),
        store: () => productOpnameServices.store(),
        show: (id) => productOpnameServices.show(id),
        edit: (id) => productOpnameServices.edit(id),
        update: (id) => productOpnameServices.update(id),
        destroy: (id) => productOpnameServices.destroy(id),
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
