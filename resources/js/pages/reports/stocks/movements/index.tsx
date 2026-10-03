import { Form, Head } from '@inertiajs/react';
import type { VisibilityState } from '@tanstack/react-table';
import {
    createColumnHelper,
    getCoreRowModel,
    useReactTable,
    type ColumnDef,
} from '@tanstack/react-table';
import { format } from 'date-fns';
import { Search } from 'lucide-react';
import QueryString from 'qs';
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
import AppLayout from '@/layouts/app-layout';
import type { Pagination } from '@/lib/model';
import { toRupiah } from '@/lib/utils';
import reportStockMovement from '@/routes/report-stock-movement';
import type { BreadcrumbItem } from '@/types';

type StockMovementData = {
    product_name: string;
    product_sku: string;
    product_unit_name: string;
    location_name: string;
    date: string;
    sell_price: number;
    stock_in: number;
    stock_out: number;
};

const columnHelper = createColumnHelper<StockMovementData>();

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

    columnHelper.accessor('location_name', {
        header: 'Lokasi',
    }),

    columnHelper.accessor('product_unit_name', {
        header: 'Satuan',
    }),

    columnHelper.accessor('date', {
        header: 'Tanggal',
        cell: (info) => format(new Date(info.getValue()), 'dd MMM yyyy'),
    }),

    columnHelper.accessor('stock_in', {
        header: 'Stok Masuk',
    }),

    columnHelper.accessor('stock_out', {
        header: 'Stok Keluar',
    }),
] as ColumnDef<StockMovementData>[];

const title = 'Laporan Pergerakan Stok';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: reportStockMovement.index().url,
    },
];

const defaultColumn = {};

const cachedColumnKey = 'stockMovementColumnVisibility';
const cachedColumn = JSON.parse(
    localStorage.getItem(cachedColumnKey) || JSON.stringify(defaultColumn),
);

type Option = {
    value: string | number;
    label: string;
};

type Props = {
    pagination: Pagination<StockMovementData>;
    locationOptions: Option[];
};

export default ({ pagination, locationOptions }: Props) => {
    const { data } = pagination;

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

    const params = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    });

    const parseToNumberArray = (val: unknown): number[] => {
        if (!val) return [];

        if (Array.isArray(val)) {
            return val.map(Number);
        }

        return String(val).split(',').map(Number);
    };

    const initialSelectAll = params.select_all_location !== '0';
    const initialLocs = parseToNumberArray(params.locs);
    const initialExcludeLocs = parseToNumberArray(params.exclude_locs);

    const [selectAllLocation, setSelectAllLocation] =
        useState<boolean>(initialSelectAll);
    const [locs, setLocs] = useState<number[]>(initialLocs);
    const [excludeLocs, setExcludeLocs] =
        useState<number[]>(initialExcludeLocs);

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
                                defaultValue={String(params.search ?? '')}
                                className="lg:w-[220px]"
                            />

                            {/* DATE */}
                            <DateRangePicker />

                            {/* LOCATION */}
                            <LocationDropdown
                                multiSelect
                                options={locationOptions.map((l) => ({
                                    id: Number(l.value),
                                    name: l.label,
                                }))}
                                defaultSelectAll={initialSelectAll}
                                defaultIds={initialLocs}
                                defaultExcludeIds={initialExcludeLocs}
                                handleSelectAllChange={setSelectAllLocation}
                                handleIdsChange={setLocs}
                                handleExcludeIdsChange={setExcludeLocs}
                            />
                            <input
                                type="hidden"
                                name="select_all_location"
                                value={selectAllLocation ? '1' : '0'}
                            />
                            {locs.map((id, i) => (
                                <input
                                    key={i}
                                    type="hidden"
                                    name="locs[]"
                                    value={id}
                                />
                            ))}

                            {excludeLocs.map((id, i) => (
                                <input
                                    key={i}
                                    type="hidden"
                                    name="exclude_locs[]"
                                    value={id}
                                />
                            ))}
                            <Button type="submit">
                                <Search /> Cari
                            </Button>
                            <PrintPdfButton
                                url={reportStockMovement.pdf().url}
                            />
                        </div>
                    </Form>
                    <DataTable columns={columns} table={table} />
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
};
