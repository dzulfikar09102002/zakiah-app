import { Form, Head, Link } from '@inertiajs/react';
import {
    createColumnHelper,
    getCoreRowModel,
    useReactTable,
    type ColumnDef,
} from '@tanstack/react-table';
import { Eye, Search } from 'lucide-react';
import { useState } from 'react';

import DataTable from '@/components/data-table';
import DateRangePicker from '@/components/date-range-picker';
import LocationDropdown from '@/components/location-dropdown';
import TablePagination from '@/components/table-pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { Pagination } from '@/lib/model';
import { capitalize, toRupiah } from '@/lib/utils';
import dailySales from '@/routes/daily-sales';
import type { BreadcrumbItem } from '@/types';

type DailySaleData = {
    id: number;
    taking_id: number | null;
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
    locationId: number;
};

const title = 'Rekapan Penjualan';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: dailySales.index().url,
    },
];

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });

const columnHelper = createColumnHelper<DailySaleData>();

const columns = [
    columnHelper.accessor('local_sales_at', {
        header: 'Tanggal',
        cell: (info) => formatDate(info.getValue()),
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
            <Button variant="outline" size="sm" asChild>
                <Link href={dailySales.show(row.original.id).url}>
                    <Eye /> Detail
                </Link>
            </Button>
        ),
    }),
] as ColumnDef<DailySaleData>[];

export default function DailySaleIndex({
    pagination,
    locationOptions,
    locationId,
}: Props) {
    const [loc, setLoc] = useState<number>(locationId);

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
                            options={locationOptions}
                            defaultId={locationId}
                            handleIdChange={setLoc}
                        />
                        <input type="hidden" name="loc" value={loc} />

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
