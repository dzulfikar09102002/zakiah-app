import { Form, Head, Link } from '@inertiajs/react';
import {
    createColumnHelper,
    getCoreRowModel,
    useReactTable,
    type ColumnDef,
} from '@tanstack/react-table';
import { Search, SquareArrowOutUpRight } from 'lucide-react';
import QueryString from 'qs';
import { useState } from 'react';

import DataTable from '@/components/data-table';
import DateRangePicker from '@/components/date-range-picker';
import LocationDropdown from '@/components/location-dropdown';
import PrintStrukButton from '@/components/print-struk-button';
import TablePagination from '@/components/table-pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { Pagination } from '@/lib/model';
import { capitalize, formatDate, toRupiah } from '@/lib/utils';
import dailySales from '@/routes/daily-sales';
import type { BreadcrumbItem } from '@/types';

type DailySaleData = {
    id: number;
    taking_id: number | null;
    location: string | null;
    local_sales_at: string;
    sales_amount: number;
    refund_amount: number;
    employee_id: number | null;
    employee_first_name: string | null;
    employee_last_name: string | null;
};

type LocationOption = {
    id: number;
    name: string;
};

type Props = {
    pagination: Pagination<DailySaleData>;
    locationOptions: LocationOption[];
};

const title = 'Rekapan Penjualan';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: dailySales.index().url,
    },
];

const columnHelper = createColumnHelper<DailySaleData>();

const columns = [
    columnHelper.accessor('local_sales_at', {
        header: 'Tanggal',
        cell: (info) => formatDate(info.getValue(), true),
    }),
    columnHelper.accessor('location', {
        header: 'Lokasi',
        cell: (info) => capitalize(info.getValue() ?? '-'),
    }),
    columnHelper.display({
        id: 'employee',
        header: 'Ditutup Oleh',
        cell: ({ row }) => {
            const name = [
                row.original.employee_first_name,
                row.original.employee_last_name,
            ]
                .filter(Boolean)
                .join(' ')
                .replace(/_/g, ' ');

            return name ? capitalize(name) : '-';
        },
    }),
    columnHelper.accessor('sales_amount', {
        header: 'Penjualan',
        cell: (info) => toRupiah(info.getValue()),
    }),
    columnHelper.accessor('refund_amount', {
        header: 'Pengembalian',
        cell: (info) => toRupiah(info.getValue()),
    }),
    columnHelper.display({
        id: 'total',
        header: 'Total',
        cell: ({ row }) => (
            <span className="font-medium">
                {toRupiah(
                    row.original.sales_amount - row.original.refund_amount,
                )}
            </span>
        ),
    }),
    columnHelper.accessor('taking_id', {
        header: 'Status',
        cell: (info) =>
            info.getValue() ? (
                <Badge variant="secondary">Tutup Shift</Badge>
            ) : (
                <Badge variant="outline">Belum Tutup Shift</Badge>
            ),
    }),
    columnHelper.display({
        id: 'action',
        header: '',
        cell: ({ row }) => (
            <div className="flex justify-end gap-2">
                <PrintStrukButton
                    url={dailySales.struk(row.original.id).url}
                    disabled={!row.original.taking_id}
                    iconOnly
                />
                <Button variant="outline" size="icon" asChild>
                    <Link href={dailySales.show(row.original.id).url}>
                        <SquareArrowOutUpRight />
                    </Link>
                </Button>
            </div>
        ),
    }),
] as ColumnDef<DailySaleData>[];

const toNumberArray = (value: unknown): number[] => {
    if (!value) return [];

    return (Array.isArray(value) ? value : String(value).split(','))
        .map(Number)
        .filter((id) => !isNaN(id));
};

export default function DailySaleIndex({ pagination, locationOptions }: Props) {
    const params = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    });
    const initialSelectAll = params.select_all_location !== '0';
    const initialLocs = toNumberArray(params.locs);
    const initialExcludeLocs = toNumberArray(params.exclude_locs);

    const [selectAllLocation, setSelectAllLocation] =
        useState<boolean>(initialSelectAll);
    const [locs, setLocs] = useState<number[]>(initialLocs);
    const [excludeLocs, setExcludeLocs] =
        useState<number[]>(initialExcludeLocs);

    const table = useReactTable({
        data: pagination.data,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <Card>
                <CardContent>
                    <Form className="mb-4 grid gap-2 lg:flex lg:justify-end">
                        <DateRangePicker />

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
                                key={id}
                                type="hidden"
                                name="locs[]"
                                value={id}
                            />
                        ))}
                        {excludeLocs.map((id) => (
                            <input
                                key={id}
                                type="hidden"
                                name="exclude_locs[]"
                                value={id}
                            />
                        ))}

                        <Button type="submit">
                            <Search /> Cari
                        </Button>
                    </Form>
                    <DataTable columns={columns} table={table} />
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}
