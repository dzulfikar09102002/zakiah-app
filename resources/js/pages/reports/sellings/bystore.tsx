import ColumnVisibilityDropdown from "@/components/column-visibility-dropdown";
import DataTable from "@/components/data-table";
import { Card, CardContent } from "@/components/ui/card";
import AppLayout from "@/layouts/app-layout";
import { Pagination } from "@/lib/model";
import sellings from "@/routes/sellings";
import { BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/react";
import { ColumnDef, getCoreRowModel, useReactTable, VisibilityState } from "@tanstack/react-table";
import { useEffect, useState } from "react";
import { createColumnHelper } from "@tanstack/react-table";

export type LocationSummary = {
    lokasi: string;
    item: number;
    jumlahRefund: number;
    penjualanKotor: number;
    hargaBeli: number;
    labaKotor: number;
    refundKotor: number;
    totalDiskon: number;
    totalPembayaran: number;
    laba: number;
};

const columnHelper = createColumnHelper<LocationSummary>();

export const columns = [
    columnHelper.accessor("lokasi", {
        header: "Lokasi",
        cell: (info) => <span className="font-medium">{info.getValue()}</span>,
    }),
    columnHelper.accessor("item", {
        header: "Item",
        cell: (info) => info.getValue().toLocaleString("id-ID"),
    }),
    columnHelper.accessor("jumlahRefund", {
        header: "Jumlah Refund",
    }),
    columnHelper.accessor("penjualanKotor", {
        header: "Penjualan Kotor",
        cell: (info) => `Rp ${info.getValue().toLocaleString("id-ID")}`,
    }),
    columnHelper.accessor("hargaBeli", {
        header: "Harga Beli",
        cell: (info) => `Rp ${info.getValue().toLocaleString("id-ID")}`,
    }),
    columnHelper.accessor("labaKotor", {
        header: "Laba Kotor",
        cell: (info) => `Rp ${info.getValue().toLocaleString("id-ID")}`,
    }),
    columnHelper.accessor("totalDiskon", {
        header: "Total Diskon",
        cell: (info) => `Rp ${info.getValue().toLocaleString("id-ID")}`,
    }),
    columnHelper.accessor("totalPembayaran", {
        header: "Total Pembayaran",
        cell: (info) => `Rp ${info.getValue().toLocaleString("id-ID")}`,
    }),
    columnHelper.accessor("laba", {
        header: "Laba",
        cell: (info) => (
            <span className="font-bold text-green-600">
                Rp {info.getValue().toLocaleString("id-ID")}
            </span>
        ),
    }),
] as ColumnDef<LocationSummary>[];

export const data: LocationSummary[] = [
    {
        lokasi: "STORE TULANGAN",
        item: 2438,
        jumlahRefund: 0,
        penjualanKotor: 244767400,
        hargaBeli: 169356877,
        labaKotor: 75410523,
        refundKotor: 0,
        totalDiskon: 46900,
        totalPembayaran: 244720500,
        laba: 75363623,
    },
    {
        lokasi: "STORE PORONG",
        item: 1307,
        jumlahRefund: 0,
        penjualanKotor: 121089900,
        hargaBeli: 87391744,
        labaKotor: 33698156,
        refundKotor: 0,
        totalDiskon: 27100,
        totalPembayaran: 121062800,
        laba: 33671056,
    },
    {
        lokasi: "STORE MOJOSARI",
        item: 3283,
        jumlahRefund: 0,
        penjualanKotor: 353407800,
        hargaBeli: 253232647,
        labaKotor: 100175153,
        refundKotor: 0,
        totalDiskon: 18500,
        totalPembayaran: 353389300,
        laba: 100156653,
    },
    {
        lokasi: "Mojokerto",
        item: 118,
        jumlahRefund: 0,
        penjualanKotor: 14457500,
        hargaBeli: 10352600,
        labaKotor: 4104900,
        refundKotor: 0,
        totalDiskon: 0,
        totalPembayaran: 14457500,
        laba: 4104900,
    },
    {
        lokasi: "Jombang",
        item: 5,
        jumlahRefund: 0,
        penjualanKotor: 705000,
        hargaBeli: 500000,
        labaKotor: 205000,
        refundKotor: 0,
        totalDiskon: 0,
        totalPembayaran: 705000,
        laba: 205000,
    }
];

const title = 'Per Lokasi'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ringkasan Penjualan',
        href: sellings.summary().url
    },
    {
        title,
        href: '#'
    },
];

const defaultColumn = {
}
const cachedColumnKey = 'salesByLocationColumnVisibility'
const cachedColumn = JSON.parse(localStorage.getItem(cachedColumnKey) || JSON.stringify(defaultColumn))

type Props = {
    pagination: Pagination<unknown>
}

export default (props: Props) => {
    console.log(props.pagination);

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