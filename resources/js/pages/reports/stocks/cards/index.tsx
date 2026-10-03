import { Form, Head } from '@inertiajs/react';
import type { VisibilityState } from '@tanstack/react-table';
import {
    createColumnHelper,
    getCoreRowModel,
    useReactTable,
    type ColumnDef,
} from '@tanstack/react-table';
import { Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import ColumnVisibilityDropdown from '@/components/column-visibility-dropdown';
import DataTable from '@/components/data-table';
import DateRangePicker from '@/components/date-range-picker';
import LocationDropdown from '@/components/location-dropdown';
import PrintPdfButton from '@/components/print-pdf-button';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { Pagination } from '@/lib/model';
import { toRupiah } from '@/lib/utils';
import reportStockCard from '@/routes/report-stock-card';
import type { BreadcrumbItem } from '@/types';

type StockCardData = {
    product_name: string;
    product_sku: string;
    product_unit_name: string;
    sell_price: number;
    stock_in: number;
    stock_out: number;
    difference: number;
};

const columnHelper = createColumnHelper<StockCardData>();

export const columns = [
    columnHelper.accessor('product_name', {
        header: 'Produk',
    }),

    columnHelper.accessor('product_sku', {
        header: 'SKU Produk',
    }),

    columnHelper.accessor('sell_price', {
        header: 'Harga Jual',
        cell: (info) => toRupiah(info.getValue()),
    }),

    columnHelper.accessor('product_unit_name', {
        header: 'Satuan',
    }),

    columnHelper.accessor('stock_in', {
        header: 'Stok Masuk',
    }),

    columnHelper.accessor('stock_out', {
        header: 'Stok Keluar',
    }),

    columnHelper.accessor('difference', {
        header: 'Selisih',
    }),
] as ColumnDef<StockCardData>[];

const title = 'Laporan Kartu Stok';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: reportStockCard.index().url,
    },
];

const defaultColumn = {};

const cachedColumnKey = 'stockCardColumnVisibility';
// referensi tetap agar react-table tidak re-render terus saat lokasi belum dipilih
const emptyData: StockCardData[] = [];

const cachedColumn = JSON.parse(
    localStorage.getItem(cachedColumnKey) || JSON.stringify(defaultColumn),
);

type Option = {
    value: string | number;
    label: string;
};

type Props = {
    pagination: Pagination<StockCardData> | null;
    locationOptions: Option[];
};

export default ({ pagination, locationOptions }: Props) => {
    const data = pagination?.data ?? emptyData;
    const query = useQuery();

    const [columnVisibility, setColumnVisibility] =
        useState<VisibilityState>(cachedColumn);
    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        onColumnVisibilityChange: setColumnVisibility,
        state: {
            columnVisibility,
        },
    });

    useEffect(() => {
        localStorage.setItem(cachedColumnKey, JSON.stringify(columnVisibility));
    }, [columnVisibility]);

    const initialLoc = Number(query.loc ?? 0);
    const [loc, setLoc] = useState<number>(initialLoc);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <Card>
                <CardContent>
                    <Form className="mb-4 grid gap-2 lg:flex lg:justify-between">
                        <ColumnVisibilityDropdown table={table} />

                        <div className="grid gap-2 lg:flex">
                            {/* SEARCH PRODUCT */}
                            <Input
                                name="search"
                                placeholder="Cari produk / SKU..."
                                defaultValue={query.search || ''}
                                className="lg:w-[220px]"
                            />

                            {/* DATE */}
                            <DateRangePicker />

                            {/* LOCATION */}
                            <LocationDropdown
                                options={locationOptions.map((l) => ({
                                    id: Number(l.value),
                                    name: l.label,
                                }))}
                                defaultId={initialLoc}
                                handleIdChange={setLoc}
                            />
                            {loc > 0 && (
                                <input type="hidden" name="loc" value={loc} />
                            )}

                            <Button type="submit">
                                <Search /> Cari
                            </Button>
                            <PrintPdfButton
                                url={reportStockCard.pdf().url}
                                disabled={!pagination}
                            />
                        </div>
                    </Form>
                    <DataTable
                        columns={columns}
                        table={table}
                        emptyMessage={
                            pagination
                                ? undefined
                                : 'Silakan pilih lokasi yang diinginkan terlebih dahulu'
                        }
                    />
                    {pagination && <TablePagination pagination={pagination} />}
                </CardContent>
            </Card>
        </AppLayout>
    );
};
