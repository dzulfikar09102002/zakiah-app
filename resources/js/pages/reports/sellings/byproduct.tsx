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

type ProductTransaction = {
    produk: string;
    skuProduk: string;
    kategoriProduk: string;
    deskripsi: string | null;
    qty: number;
    hargaJual: number;
    hargaBeli: number;
    penjualanKotor: number;
    totalDiskon: number;
    penjualanBersih: number;
    laba: number;
};

const columnHelper = createColumnHelper<ProductTransaction>();

const columns = [
    columnHelper.accessor("produk", {
        header: "Produk",
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor("skuProduk", {
        header: "SKU Produk",
    }),
    columnHelper.accessor("kategoriProduk", {
        header: "Kategori",
    }),
    columnHelper.accessor("qty", {
        header: "Qty",
        cell: (info) => info.getValue().toLocaleString("id-ID"),
    }),
    columnHelper.accessor("hargaJual", {
        header: "Harga Jual",
        cell: (info) => `Rp ${info.getValue().toLocaleString("id-ID")}`,
    }),
    columnHelper.accessor("hargaBeli", {
        header: "Harga Beli",
        cell: (info) => `Rp ${info.getValue().toLocaleString("id-ID")}`,
    }),
    columnHelper.accessor("penjualanBersih", {
        header: "Penjualan Bersih",
        cell: (info) => `Rp ${info.getValue().toLocaleString("id-ID")}`,
    }),
    columnHelper.accessor("laba", {
        header: "Laba",
        cell: (info) => {
            const val = info.getValue();
            return (
                <span className={val < 0 ? "text-red-500" : "text-green-600"}>
                    {val < 0 ? `-Rp ${Math.abs(val).toLocaleString("id-ID")}` : `Rp ${val.toLocaleString("id-ID")}`}
                </span>
            );
        },
    }),
] as ColumnDef<ProductTransaction>[];

export const data: ProductTransaction[] = [
    {
        produk: "Celana CL621BW",
        skuProduk: "311501260315",
        kategoriProduk: "Celana",
        deskripsi: null,
        qty: 2,
        hargaJual: 152000,
        hargaBeli: 110000,
        penjualanKotor: 304000,
        totalDiskon: 0,
        penjualanBersih: 304000,
        laba: 84000,
    },
    {
        produk: "Tas TCCMPSC",
        skuProduk: "811712240326",
        kategoriProduk: "Tas",
        deskripsi: null,
        qty: 46,
        hargaJual: 52000,
        hargaBeli: 38000,
        penjualanKotor: 2392000,
        totalDiskon: 0,
        penjualanBersih: 2392000,
        laba: 644000,
    },
    {
        produk: "Voal Miracle Mtf RC ( Free gift)",
        skuProduk: "812402260601",
        kategoriProduk: "Hijab",
        deskripsi: null,
        qty: 71,
        hargaJual: 0,
        hargaBeli: 16500,
        penjualanKotor: 0,
        totalDiskon: 0,
        penjualanBersih: 0,
        laba: -1171500,
    },
    // ... tambahkan item lainnya sesuai kebutuhan
];

const title = 'Per Produk'

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
const cachedColumnKey = 'salesByProductColumnVisibility'
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