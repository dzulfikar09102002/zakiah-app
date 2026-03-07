import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import sellings from '@/routes/sellings';
import { Card, CardContent } from '@/components/ui/card';
import { capitalize, toRupiah } from '@/lib/utils';
import { getCoreRowModel, useReactTable, VisibilityState, type ColumnDef } from '@tanstack/react-table';
import { useEffect, useState } from 'react';
import ColumnVisibilityDropdown from '@/components/column-visibility-dropdown';
import DataTable from '@/components/data-table';
import TablePagination from '@/components/table-pagination';
import { Pagination } from '@/lib/model';

type SalesData = {
    nomorTransaksi: string;
    lokasi: string;
    tanggal: string;
    kasir: string;
    sales: string;
    member: string;
    subtotal: number;
    totalDiskon: number;
    totalPenyesuaian: number;
    total: number;
    laba: number;
};


const salesData = [
    {
        nomorTransaksi: "20260223/tul/00001",
        lokasi: "STORE TULANGAN",
        tanggal: "2026-02-23 08:34:46.000000",
        kasir: "Putri Kasir",
        sales: "Nilta Sales",
        member: "",
        subtotal: 573000,
        totalDiskon: 0,
        totalPenyesuaian: 0,
        total: 573000,
        laba: 202500
    },
    {
        nomorTransaksi: "20260223/por/00001",
        lokasi: "STORE PORONG",
        tanggal: "2026-02-23 08:51:25.000000",
        kasir: "Erinindya Porong Kasir",
        sales: "Selvia Porong Sales",
        member: "",
        subtotal: 156000,
        totalDiskon: 0,
        totalPenyesuaian: 0,
        total: 156000,
        laba: 44739
    },
    {
        nomorTransaksi: "20260223/por/00002",
        lokasi: "STORE PORONG",
        tanggal: "2026-02-23 08:53:05.000000",
        kasir: "Erinindya Porong Kasir",
        sales: "Selvia Porong Sales",
        member: "",
        subtotal: 132000,
        totalDiskon: 0,
        totalPenyesuaian: 0,
        total: 132000,
        laba: 37000
    },
    {
        nomorTransaksi: "20260223/tul/00002",
        lokasi: "STORE TULANGAN",
        tanggal: "2026-02-23 08:58:38.000000",
        kasir: "Putri Kasir",
        sales: "Nilta Sales",
        member: "",
        subtotal: 99000,
        totalDiskon: 0,
        totalPenyesuaian: 0,
        total: 99000,
        laba: 35500
    },
    {
        nomorTransaksi: "20260223/tul/00003",
        lokasi: "STORE TULANGAN",
        tanggal: "2026-02-23 09:09:28.000000",
        kasir: "Putri Kasir",
        sales: "Nilta Sales",
        member: "",
        subtotal: 54000,
        totalDiskon: 0,
        totalPenyesuaian: 0,
        total: 54000,
        laba: 23000
    },
    {
        nomorTransaksi: "20260223/moj/00001",
        lokasi: "STORE MOJOSARI",
        tanggal: "2026-02-23 09:10:14.000000",
        kasir: "dea.kasir Kasir",
        sales: "rachman.sl Sales",
        member: "",
        subtotal: 374000,
        totalDiskon: 22000,
        totalPenyesuaian: 0,
        total: 352000,
        laba: 90946
    },
    {
        nomorTransaksi: "20260223/tul/00004",
        lokasi: "STORE TULANGAN",
        tanggal: "2026-02-23 09:23:51.000000",
        kasir: "Putri Kasir",
        sales: "Nilta Sales",
        member: "",
        subtotal: 285000,
        totalDiskon: 0,
        totalPenyesuaian: 0,
        total: 285000,
        laba: 100000
    },
    {
        nomorTransaksi: "20260223/tul/00005",
        lokasi: "STORE TULANGAN",
        tanggal: "2026-02-23 09:26:48.000000",
        kasir: "Putri Kasir",
        sales: "Nilta Sales",
        member: "",
        subtotal: 38000,
        totalDiskon: 0,
        totalPenyesuaian: 0,
        total: 38000,
        laba: 14000
    }
];

const columns: ColumnDef<SalesData>[] = [
    {
        accessorKey: "nomorTransaksi",
        header: "Nomor Transaksi",
    },
    {
        accessorKey: "lokasi",
        header: "Lokasi",
        cell: ({ getValue }) => capitalize(getValue() as string),
    },
    {
        accessorKey: "tanggal",
        header: "Tanggal",
        cell: ({ getValue }) => new Date(getValue() as string).toLocaleDateString('id-ID')
    },
    {
        accessorKey: "kasir",
        header: "Kasir",
    },
    {
        accessorKey: "sales",
        header: "Sales",
    },
    {
        accessorKey: "member",
        header: "Member",
        cell: ({ getValue }) => getValue() || '-'
    },
    {
        accessorKey: "subtotal",
        header: "Subtotal",
        cell: ({ getValue }) => toRupiah(getValue() as number),
    },
    {
        accessorKey: "totalDiskon",
        header: "Diskon",
        cell: ({ getValue }) => toRupiah(getValue() as number),
    },
    {
        accessorKey: "totalPenyesuaian",
        header: "Penyesuaian",
        cell: ({ getValue }) => toRupiah(getValue() as number),
    },
    {
        accessorKey: "total",
        header: "Total",
        cell: ({ getValue }) => toRupiah(getValue() as number),
    },
    {
        accessorKey: "laba",
        header: "Laba",
        cell: ({ getValue }) => toRupiah(getValue() as number),
    }
];

const title = 'Ringkasan Penjualan'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: sellings.summary().url,
    },
];

const defaultColumn = {
    kasir: false,
    sales: false
}
const cachedColumnKey = 'salesSummaryColumnVisibility'
const cachedColumn = JSON.parse(localStorage.getItem(cachedColumnKey) || JSON.stringify(defaultColumn))

type Props = {
    pagination: Pagination<unknown>
}

export default (props: Props) => {
    console.log(props.pagination);

    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>(cachedColumn)
    const table = useReactTable({
        data: salesData,
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
            <div className="mb-4">
                <ColumnVisibilityDropdown table={table} />
            </div>
            <Card>
                <CardContent>
                    <DataTable columns={columns} table={table} />
                    {/* <TablePagination pagination={}/> */}
                </CardContent>
            </Card>
        </AppLayout>
    );
}