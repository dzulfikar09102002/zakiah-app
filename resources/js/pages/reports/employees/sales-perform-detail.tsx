import { Form, Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import employee from '@/routes/report-employee-detail';
import { Card, CardContent } from '@/components/ui/card';
import { capitalize, formatEmployeeName, toRupiah } from '@/lib/utils';
import {
    createColumnHelper,
    getCoreRowModel,
    useReactTable,
    VisibilityState,
    type ColumnDef,
} from '@tanstack/react-table';
import { useEffect, useState } from 'react';
import ColumnVisibilityDropdown from '@/components/column-visibility-dropdown';
import DataTable from '@/components/data-table';
import TablePagination from '@/components/table-pagination';
import { EmployeeSalesDetailData, Pagination } from '@/lib/model';
import DateRangePicker from '@/components/date-range-picker';
import { Button } from '@/components/ui/button';
import { Search } from 'lucide-react';
import { Input } from '@/components/ui/input';
import LocationDropdown from '@/components/location-dropdown';
import QueryString from 'qs';

const columnHelper = createColumnHelper<EmployeeSalesDetailData>();

export const columns = [
    columnHelper.accessor('local_sales_date', {
        header: 'Tanggal',
        cell: (info) => {
            const date = new Date(info.getValue());

            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
            });
        },
    }),

    columnHelper.accessor('employee_sales_name', {
        header: 'Nama Sales',
        cell: (info) => formatEmployeeName(info.getValue()),
    }),

    columnHelper.accessor('location_name', {
        header: 'Lokasi',
        cell: (info) => capitalize(info.getValue()),
    }),

    columnHelper.accessor('sales_count', {
        header: () => <div className="text-center">Transaksi</div>,
        cell: (info) => <div className="text-center">{info.getValue()}</div>,
        meta: { label: 'Transaksi' },
    }),

    columnHelper.accessor('refund_count', {
        header: () => <div className="text-center">Pengembalian</div>,
        cell: (info) => <div className="text-center">{info.getValue()}</div>,
        meta: { label: 'Pengembalian' },
    }),

    columnHelper.accessor('net_count', {
        header: () => <div className="text-center">Penjualan Bersih</div>,
        cell: (info) => <div className="text-center">{info.getValue()}</div>,
        meta: { label: 'Penjualan Bersih' },
    }),

    columnHelper.accessor('sales_quantity', {
        header: () => <div className="text-center">Item Terjual</div>,
        cell: (info) => <div className="text-center">{info.getValue()}</div>,
        meta: { label: 'Item Terjual' },
    }),

    columnHelper.accessor('refund_quantity', {
        header: () => <div className="text-center">Item Pengembalian</div>,
        cell: (info) => <div className="text-center">{info.getValue()}</div>,
        meta: { label: 'Item Pengembalian' },
    }),

    columnHelper.accessor('net_quantity', {
        header: () => <div className="text-center">Item Penjualan Bersih</div>,
        cell: (info) => <div className="text-center">{info.getValue()}</div>,
        meta: { label: 'Item Penjualan Bersih' },
    }),

    columnHelper.accessor('sales_amount', {
        header: () => <div className="text-right">Total Penjualan</div>,
        cell: (info) => (
            <div className="text-right">{toRupiah(info.getValue())}</div>
        ),
        meta: { label: 'Total Penjualan' },
    }),

    columnHelper.accessor('refund_amount', {
        header: () => <div className="text-right">Total Pengembalian</div>,
        cell: (info) => (
            <div className="text-right">{toRupiah(info.getValue())}</div>
        ),
        meta: { label: 'Total Pengembalian' },
    }),

    columnHelper.accessor('net_sales_amount', {
        header: () => <div className="text-right">Total Penjualan Bersih</div>,
        cell: (info) => (
            <div className="text-right">{toRupiah(info.getValue())}</div>
        ),
        meta: { label: 'Total Penjualan Bersih' },
    }),
] as ColumnDef<EmployeeSalesDetailData>[];

const title = 'Detail Performa Sales';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: employee.index().url,
    },
];

const defaultColumn = {};

const cachedColumnKey = 'employeeSalesDetailColumnVisibility';
const cachedColumn = JSON.parse(
    localStorage.getItem(cachedColumnKey) || JSON.stringify(defaultColumn),
);

type Option = {
    value: string;
    label: string;
};

type Props = {
    employeeSalesDetail: Pagination<EmployeeSalesDetailData>;
    locationOptions: Option[];
};

type Params = {
    search: string;
    locs: string[];
    exclude_locs: string[];
    select_all_location: string;
    start_at: string;
    end_at: string;
};

export default ({ employeeSalesDetail, locationOptions }: Props) => {
    const { data } = employeeSalesDetail;

    const query = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    }) as Partial<Params>;

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

    const parseToNumberArray = (val: any): number[] => {
        if (!val) return [];

        if (Array.isArray(val)) {
            return val.map(Number);
        }

        return String(val).split(',').map(Number);
    };

    const initialSelectAll = query.select_all_location !== '0';
    const initialLocs = parseToNumberArray(query.locs);
    const initialExcludeLocs = parseToNumberArray(query.exclude_locs);

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
                            <DateRangePicker />

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

                            <Input
                                name="search"
                                placeholder="Cari nama sales / lokasi"
                                defaultValue={query.search || ''}
                            />
                            <Button type="submit">
                                <Search /> Cari
                            </Button>
                        </div>
                    </Form>
                    <DataTable columns={columns} table={table} />
                    <TablePagination pagination={employeeSalesDetail} />
                </CardContent>
            </Card>
        </AppLayout>
    );
};
