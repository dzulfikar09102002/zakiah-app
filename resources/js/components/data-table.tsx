import type { ColumnDef } from '@tanstack/react-table';
import { flexRender, type Table as ITable } from '@tanstack/react-table';
import { Fragment, type ReactNode } from 'react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { TableRowsSkeleton } from '@/components/ui/table-rows-skeleton';

type Props<T> = {
    table: ITable<T>;
    columns: ColumnDef<T>[];
    emptyMessage?: string;
    /**
     * Isi baris detail (master-detail). Baris tampil bila row.getIsExpanded(); tabel perlu
     * getRowCanExpand dan state expanded di useReactTable.
     */
    renderDetail?: (row: T) => ReactNode;
    /** Data belum datang (prop deferred): header tetap, isi diganti baris skeleton. */
    loading?: boolean;
};

export default function <T>({
    table,
    columns,
    emptyMessage = 'Data tidak ditemukan',
    renderDetail,
    loading = false,
}: Props<T>) {
    return (
        <Table>
            <TableHeader>
                {table.getHeaderGroups().map((headerGroup) => (
                    <TableRow key={headerGroup.id}>
                        {headerGroup.headers.map((header) => {
                            return (
                                <TableHead key={header.id}>
                                    {header.isPlaceholder
                                        ? null
                                        : flexRender(
                                              header.column.columnDef.header,
                                              header.getContext(),
                                          )}
                                </TableHead>
                            );
                        })}
                    </TableRow>
                ))}
            </TableHeader>
            <TableBody>
                {loading ? (
                    <TableRowsSkeleton
                        columns={table.getVisibleLeafColumns().length}
                    />
                ) : table.getRowModel().rows?.length ? (
                    table.getRowModel().rows.map((row) => (
                        <Fragment key={row.id}>
                            <TableRow
                                data-state={row.getIsSelected() && 'selected'}
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
                            {renderDetail && row.getIsExpanded() && (
                                <TableRow className="bg-muted/30 hover:bg-muted/30">
                                    <TableCell
                                        colSpan={row.getVisibleCells().length}
                                        className="p-3 whitespace-normal"
                                    >
                                        {renderDetail(row.original)}
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
                            {emptyMessage}
                        </TableCell>
                    </TableRow>
                )}
            </TableBody>
        </Table>
    );
}
