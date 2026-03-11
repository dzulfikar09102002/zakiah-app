import { Form, Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import sellings from '@/routes/sellings';
import { Card, CardContent } from '@/components/ui/card';
import { capitalize, toRupiah } from '@/lib/utils';
import { createColumnHelper, getCoreRowModel, useReactTable, VisibilityState, type ColumnDef } from '@tanstack/react-table';
import { useEffect, useState } from 'react';
import ColumnVisibilityDropdown from '@/components/column-visibility-dropdown';
import DataTable from '@/components/data-table';
import TablePagination from '@/components/table-pagination';
import { Pagination } from '@/lib/model';
import Autocomplete from '@/components/autocomplete';
import DateRangePicker from '@/components/date-range-picker';
import { Button } from '@/components/ui/button';
import { Search } from 'lucide-react';
import Select from '@/components/select';

type SalesData = {
    transaction_no: string
    location: string
    date: string
    cashier: string
    sales: string
    member?: string
    subtotal: number
    discount: number
    adjustment: number
    total: number
    profit: number
};

const columnHelper = createColumnHelper<SalesData>()

export const columns = [
    columnHelper.accessor("transaction_no", {
        header: "Nomor Transaksi",
        cell: info => info.getValue(),
    }),

    columnHelper.accessor("location", {
        header: "Lokasi",
        cell: info => capitalize(info.getValue()),
    }),

    columnHelper.accessor("date", {
        header: "Tanggal",
        cell: info => {
            const date = new Date(info.getValue())
            return date.toLocaleDateString("id-ID")
        },
    }),

    columnHelper.accessor("cashier", {
        header: "Kasir",
        cell: info => capitalize(info.getValue()),
    }),

    columnHelper.accessor("sales", {
        header: "Sales",
        cell: info => capitalize(info.getValue()),
    }),

    columnHelper.accessor("member", {
        header: "Member",
        cell: info => capitalize(info.getValue() ?? "-"),
    }),

    columnHelper.accessor("subtotal", {
        header: "Subtotal",
        cell: info => toRupiah(info.getValue())
    }),

    columnHelper.accessor("discount", {
        header: "Diskon",
        cell: info => toRupiah(info.getValue())
    }),

    columnHelper.accessor("adjustment", {
        header: "Penyesuaian",
        cell: info => toRupiah(info.getValue())
    }),

    columnHelper.accessor("total", {
        header: "Total",
        cell: info => toRupiah(info.getValue())
    }),

    columnHelper.accessor("profit", {
        header: "Laba",
        cell: info => toRupiah(info.getValue())
    }),
] as ColumnDef<SalesData>[]

const title = 'Ringkasan Penjualan'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: sellings.summary().url,
    },
];

const defaultColumn = {
    cashier: false,
    sales: false
}

const cachedColumnKey = 'salesSummaryColumnVisibility'
const cachedColumn = JSON.parse(localStorage.getItem(cachedColumnKey) || JSON.stringify(defaultColumn))

const discountOption = [
    {
        label: 'Semua Diskon',
        value: 'all',
    },
    {
        label: 'Diskon',
        value: 'available'
    },
    {
        label: 'Tanpa Diskon',
        value: 'none'
    },
]

type Props = {
    pagination: Pagination<SalesData>
}

export default ({ pagination }: Props) => {

    const { data } = pagination

    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>(cachedColumn)
    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        onColumnVisibilityChange: setColumnVisibility,
        state: {
            columnVisibility
        }
    })

    useEffect(() => {
        localStorage.setItem(cachedColumnKey, JSON.stringify(columnVisibility))
    }, [columnVisibility]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <Card>
                <CardContent>
                    <Form className="mb-4 grid gap-2 lg:flex lg:justify-between">
                        <ColumnVisibilityDropdown table={table} />
                        <div className="grid lg:flex gap-2">
                            <DateRangePicker onValueChange={() => { }} />
                            <Select onValueChange={() => { }} options={discountOption} placeholder='Pilih diskon'></Select>
                            <Autocomplete placeholder='Pilih lokasi' options={[]} />
                            <Button type='submit'><Search /> Cari</Button>
                        </div>
                    </Form>
                    <DataTable columns={columns} table={table} />
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}