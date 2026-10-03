import { Link, router } from '@inertiajs/react';
import {
    createColumnHelper,
    flexRender,
    getCoreRowModel,
    getExpandedRowModel,
    useReactTable,
    type ColumnDef,
} from '@tanstack/react-table';
import {
    ChevronDown,
    ChevronRight,
    ChevronsDown,
    ChevronsUp,
} from 'lucide-react';
import QueryString from 'qs';
import { Fragment } from 'react';

import Select from '@/components/select';
import TablePagination from '@/components/table-pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { Option, Pagination } from '@/lib/model';
import { capitalize, cn, toRupiah } from '@/lib/utils';
import saleTransactions from '@/routes/sale-transactions';

export type SaleTransactionItem = {
    id: number;
    product_name: string;
    product_sku: string | null;
    product_unit_name: string | null;
    quantity: number;
    sell_price: number;
    promo_amount: number;
    discount_amount: number;
    notes: string | null;
    status: string;
};

export type DailySaleTransaction = {
    id: number;
    sales_no: string;
    shift: string;
    date: string;
    order_type: string | null;
    customer: string;
    cashier: string;
    subtotal: number;
    discount: number;
    net_sales_after_tax: number;
    status: 'ok' | 'void';
    details: SaleTransactionItem[];
};

type Props = {
    pagination: Pagination<DailySaleTransaction>;
    shiftOptions: Option[];
};

const columnHelper = createColumnHelper<DailySaleTransaction>();

const columns = [
    columnHelper.display({
        id: 'expander',
        header: ({ table }) => (
            <Button
                type="button"
                variant="ghost"
                size="icon"
                className="size-7"
                onClick={table.getToggleAllRowsExpandedHandler()}
                title={
                    table.getIsAllRowsExpanded()
                        ? 'Tutup semua detail'
                        : 'Buka semua detail'
                }
            >
                {table.getIsAllRowsExpanded() ? (
                    <ChevronsUp />
                ) : (
                    <ChevronsDown />
                )}
            </Button>
        ),
        cell: ({ row }) => (
            <Button
                type="button"
                variant="ghost"
                size="icon"
                className="size-7"
                onClick={row.getToggleExpandedHandler()}
                title={row.getIsExpanded() ? 'Tutup detail' : 'Buka detail'}
            >
                {row.getIsExpanded() ? <ChevronDown /> : <ChevronRight />}
            </Button>
        ),
    }),
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
    columnHelper.accessor('shift', {
        header: 'Shift',
    }),
    columnHelper.accessor('date', {
        header: 'Waktu',
        cell: (info) =>
            new Date(info.getValue()).toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }),
    }),
    columnHelper.accessor('order_type', {
        header: 'Jenis Pesanan',
        cell: (info) => info.getValue() || '-',
    }),
    columnHelper.accessor('customer', {
        header: 'Pelanggan',
        cell: (info) => (info.getValue() ? capitalize(info.getValue()) : '-'),
    }),
    columnHelper.accessor('cashier', {
        header: 'Kasir',
        cell: (info) => (info.getValue() ? capitalize(info.getValue()) : '-'),
    }),
    columnHelper.accessor('subtotal', {
        header: () => <div className="text-right">Subtotal</div>,
        cell: (info) => (
            <div className="text-right tabular-nums">
                {toRupiah(info.getValue())}
            </div>
        ),
    }),
    columnHelper.accessor('discount', {
        header: () => <div className="text-right">Diskon</div>,
        cell: (info) => (
            <div className="text-right tabular-nums">
                {info.getValue() > 0 ? toRupiah(info.getValue() * -1) : '-'}
            </div>
        ),
    }),
    columnHelper.accessor('net_sales_after_tax', {
        header: () => <div className="text-right">Total</div>,
        cell: (info) => (
            <div className="text-right font-medium tabular-nums">
                {toRupiah(info.getValue())}
            </div>
        ),
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
] as ColumnDef<DailySaleTransaction>[];

function ItemTable({ items }: { items: SaleTransactionItem[] }) {
    return (
        <div className="rounded-md border bg-background">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Produk</TableHead>
                        <TableHead>Unit</TableHead>
                        <TableHead className="text-right">Qty</TableHead>
                        <TableHead className="text-right">Harga</TableHead>
                        <TableHead className="text-right">Diskon</TableHead>
                        <TableHead className="text-right">Subtotal</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {items.length ? (
                        items.map((item) => {
                            const discount =
                                item.promo_amount + item.discount_amount;

                            return (
                                <TableRow
                                    key={item.id}
                                    className={cn(
                                        item.status === 'void' &&
                                            'text-muted-foreground line-through',
                                    )}
                                >
                                    <TableCell>
                                        <p className="font-medium">
                                            {item.product_name}
                                        </p>
                                        {(item.product_sku || item.notes) && (
                                            <p className="text-xs text-muted-foreground">
                                                {[item.product_sku, item.notes]
                                                    .filter(Boolean)
                                                    .join(' · ')}
                                            </p>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {item.product_unit_name || '-'}
                                    </TableCell>
                                    <TableCell className="text-right tabular-nums">
                                        {item.quantity}
                                    </TableCell>
                                    <TableCell className="text-right tabular-nums">
                                        {toRupiah(item.sell_price)}
                                    </TableCell>
                                    <TableCell className="text-right tabular-nums">
                                        {discount > 0
                                            ? toRupiah(discount * -1)
                                            : '-'}
                                    </TableCell>
                                    <TableCell className="text-right tabular-nums">
                                        {toRupiah(
                                            item.sell_price * item.quantity -
                                                discount,
                                        )}
                                    </TableCell>
                                </TableRow>
                            );
                        })
                    ) : (
                        <TableRow>
                            <TableCell colSpan={6} className="text-center">
                                Tidak ada produk
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
        </div>
    );
}

export default function SaleTransactionTable({
    pagination,
    shiftOptions,
}: Props) {
    const query = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    });

    const table = useReactTable({
        data: pagination.data,
        columns,
        getRowId: (row) => String(row.id),
        getRowCanExpand: () => true,
        getCoreRowModel: getCoreRowModel(),
        getExpandedRowModel: getExpandedRowModel(),
    });

    const handleShiftChange = (value: string) => {
        router.get(
            window.location.pathname,
            {
                ...query,
                taking_id: value === 'all' ? undefined : value,
                page: undefined,
            },
            { preserveScroll: true, preserveState: true },
        );
    };

    return (
        <div className="flex flex-col gap-4">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <h3 className="font-semibold">Daftar Transaksi</h3>
                <div className="w-full sm:w-[180px]">
                    <Select
                        defaultValue={String(query.taking_id ?? 'all')}
                        options={[
                            { label: 'Semua Shift', value: 'all' },
                            ...shiftOptions,
                        ]}
                        onValueChange={handleShiftChange}
                        placeholder="Pilih shift"
                    />
                </div>
            </div>

            <div className="overflow-x-auto">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => (
                                    <TableHead
                                        key={header.id}
                                        className={cn(
                                            header.column.id === 'expander' &&
                                                'w-10',
                                        )}
                                    >
                                        {header.isPlaceholder
                                            ? null
                                            : flexRender(
                                                  header.column.columnDef
                                                      .header,
                                                  header.getContext(),
                                              )}
                                    </TableHead>
                                ))}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows.length ? (
                            table.getRowModel().rows.map((row) => (
                                <Fragment key={row.id}>
                                    <TableRow
                                        data-state={
                                            row.getIsExpanded()
                                                ? 'selected'
                                                : undefined
                                        }
                                    >
                                        {row.getVisibleCells().map((cell) => (
                                            <TableCell key={cell.id}>
                                                {flexRender(
                                                    cell.column.columnDef.cell,
                                                    cell.getContext(),
                                                )}
                                            </TableCell>
                                        ))}
                                    </TableRow>
                                    {row.getIsExpanded() && (
                                        <TableRow className="hover:bg-transparent">
                                            <TableCell
                                                colSpan={
                                                    row.getVisibleCells().length
                                                }
                                                className="bg-muted/40 p-3 pl-12"
                                            >
                                                <ItemTable
                                                    items={row.original.details}
                                                />
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </Fragment>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell
                                    colSpan={columns.length}
                                    className="text-center"
                                >
                                    Data tidak ditemukan
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>

            <TablePagination pagination={pagination} />
        </div>
    );
}
