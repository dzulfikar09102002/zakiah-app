import { Form, Head, Link } from '@inertiajs/react';
import {
    createColumnHelper,
    getCoreRowModel,
    useReactTable,
    type ColumnDef,
    type VisibilityState,
} from '@tanstack/react-table';
import { Eye, Search } from 'lucide-react';
import QueryString from 'qs';
import { useEffect, useState } from 'react';

import ColumnVisibilityDropdown from '@/components/column-visibility-dropdown';
import DataTable from '@/components/data-table';
import DateRangePicker from '@/components/date-range-picker';
import LocationDropdown from '@/components/location-dropdown';
import MultiSelect from '@/components/multi-select';
import Select from '@/components/select';
import TablePagination from '@/components/table-pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import type { Option, Pagination } from '@/lib/model';
import { capitalize, toRupiah } from '@/lib/utils';
import saleTransactions from '@/routes/sale-transactions';
import type { BreadcrumbItem } from '@/types';

type SaleTransactionData = {
    id: number;
    sales_no: string;
    receipt_no: string;
    location: string;
    order_type: string;
    date: string;
    customer: string;
    cashier: string;
    sales: string;
    net_sales_after_tax: number;
    refunded_amount: number;
    status: 'ok' | 'void';
};

type LocationOption = {
    id: number;
    name: string;
};

type Props = {
    pagination: Pagination<SaleTransactionData>;
    locationOptions: LocationOption[];
    orderTypeOptions: Option[];
};

const title = 'Data Penjualan';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: saleTransactions.index().url,
    },
];

const statusOptions = [
    { label: 'Semua Status', value: 'all' },
    { label: 'Berhasil', value: 'ok' },
    { label: 'Dibatalkan', value: 'void' },
];

const columnHelper = createColumnHelper<SaleTransactionData>();

const columns = [
    columnHelper.accessor('sales_no', {
        header: 'No. Penjualan',
        cell: (info) => (
            <Link
                href={saleTransactions.show(info.row.original.id).url}
                className="font-medium hover:underline"
            >
                {info.getValue()}
            </Link>
        ),
    }),
    columnHelper.accessor('date', {
        header: 'Tanggal',
        cell: (info) =>
            new Date(info.getValue()).toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }),
    }),
    columnHelper.accessor('location', {
        header: 'Lokasi',
        cell: (info) => capitalize(info.getValue() ?? '-'),
    }),
    columnHelper.accessor('order_type', {
        header: 'Jenis Pesanan',
        cell: (info) => info.getValue() ?? '-',
    }),
    columnHelper.accessor('customer', {
        header: 'Pelanggan',
        cell: (info) => (info.getValue() ? capitalize(info.getValue()) : '-'),
    }),
    columnHelper.accessor('cashier', {
        header: 'Kasir',
        cell: (info) => capitalize(info.getValue() || '-'),
    }),
    columnHelper.accessor('sales', {
        header: 'Sales',
        cell: (info) => capitalize(info.getValue() || '-'),
    }),
    columnHelper.accessor('net_sales_after_tax', {
        header: 'Total',
        cell: (info) => toRupiah(info.getValue()),
    }),
    columnHelper.accessor('refunded_amount', {
        header: 'Dikembalikan',
        cell: (info) => toRupiah(info.getValue()),
    }),
    columnHelper.accessor('status', {
        header: 'Status',
        cell: (info) =>
            info.getValue() === 'void' ? (
                <Badge variant="destructive">Dibatalkan</Badge>
            ) : (
                <Badge variant="secondary">Berhasil</Badge>
            ),
    }),
    columnHelper.display({
        id: 'action',
        header: '',
        enableHiding: false,
        cell: ({ row }) => (
            <Button variant="outline" size="sm" asChild>
                <Link href={saleTransactions.show(row.original.id).url}>
                    <Eye /> Detail
                </Link>
            </Button>
        ),
    }),
] as ColumnDef<SaleTransactionData>[];

const defaultColumn = {
    cashier: false,
    sales: false,
    refunded_amount: false,
};

const cachedColumnKey = 'saleTransactionColumnVisibility';

const loadColumnVisibility = (): VisibilityState => {
    try {
        return JSON.parse(
            localStorage.getItem(cachedColumnKey) ||
                JSON.stringify(defaultColumn),
        );
    } catch {
        return defaultColumn;
    }
};

const parseToNumberArray = (val: unknown): number[] => {
    if (!val) return [];
    if (Array.isArray(val)) return val.map(Number);
    return String(val).split(',').map(Number);
};

const parseToStringArray = (val: unknown): string[] => {
    if (!val) return [];
    if (Array.isArray(val)) return val.map(String);
    return String(val).split(',');
};

export default function SaleTransactionIndex({
    pagination,
    locationOptions,
    orderTypeOptions,
}: Props) {
    const query = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    });

    const initialSelectAll = query.select_all_location !== '0';
    const initialLocs = parseToNumberArray(query.locs);
    const initialExcludeLocs = parseToNumberArray(query.exclude_locs);

    const [selectAllLocation, setSelectAllLocation] =
        useState<boolean>(initialSelectAll);
    const [locs, setLocs] = useState<number[]>(initialLocs);
    const [excludeLocs, setExcludeLocs] =
        useState<number[]>(initialExcludeLocs);

    const [columnVisibility, setColumnVisibility] =
        useState<VisibilityState>(loadColumnVisibility);

    useEffect(() => {
        localStorage.setItem(cachedColumnKey, JSON.stringify(columnVisibility));
    }, [columnVisibility]);

    const table = useReactTable({
        data: pagination.data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        onColumnVisibilityChange: setColumnVisibility,
        state: {
            columnVisibility,
        },
    });

    const orderTypePlaceholder = (values: string[]) => {
        const selected = values.filter((v) => v !== 'all');
        if (
            selected.length === 0 ||
            selected.length >= orderTypeOptions.length
        ) {
            return 'Semua jenis pesanan';
        }
        return `${selected.length} jenis pesanan`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <Card>
                <CardContent>
                    <Form className="mb-4 grid gap-2 xl:flex xl:justify-between">
                        <ColumnVisibilityDropdown table={table} />

                        <div className="grid gap-2 lg:flex">
                            <Input
                                name="search"
                                placeholder="Cari no. penjualan / pelanggan"
                                defaultValue={String(query.search ?? '')}
                                className="lg:w-[220px]"
                            />

                            <DateRangePicker />

                            <div className="lg:w-[150px]">
                                <Select
                                    name="status"
                                    defaultValue={String(query.status ?? 'all')}
                                    options={statusOptions}
                                    placeholder="Pilih status"
                                />
                            </div>

                            <div className="lg:w-[190px]">
                                <MultiSelect
                                    name="order_types[]"
                                    options={orderTypeOptions}
                                    defaultValues={parseToStringArray(
                                        query.order_types,
                                    )}
                                    placeholder={orderTypePlaceholder}
                                />
                            </div>

                            <LocationDropdown
                                multiSelect
                                options={locationOptions}
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
                            {locs.map((id) => (
                                <input
                                    key={`loc-${id}`}
                                    type="hidden"
                                    name="locs[]"
                                    value={id}
                                />
                            ))}
                            {excludeLocs.map((id) => (
                                <input
                                    key={`exclude-${id}`}
                                    type="hidden"
                                    name="exclude_locs[]"
                                    value={id}
                                />
                            ))}

                            <Button type="submit">
                                <Search /> Cari
                            </Button>
                        </div>
                    </Form>
                    <div className="overflow-x-auto">
                        <DataTable columns={columns} table={table} />
                    </div>
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}
