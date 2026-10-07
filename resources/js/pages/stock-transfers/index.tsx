import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Plus, SquareArrowOutUpRight } from 'lucide-react';
import DocumentFilters from '@/components/stock/document-filters';
import {
    StockStatusBadge,
    employeeName,
    formatDocumentDate,
    stockStatusTabs,
} from '@/components/stock/helpers';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import type { Pagination, StockTransfer } from '@/lib/model';
import { capitalize } from '@/lib/utils';
import productTransferServices from '@/routes/product-transfer-services';
import type { BreadcrumbItem, Option } from '@/types';

const title = 'Pindah Stok';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: productTransferServices.index().url,
    },
];

const statusTabs = [
    ...stockStatusTabs,
    { value: 'cancelled', label: 'Dibatalkan' },
];

type Props = {
    pagination: Pagination<StockTransfer>;
    locationOptions: Option[];
};

export default ({ pagination, locationOptions }: Props) => {
    const startIndex = (pagination.current_page - 1) * pagination.per_page;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <div className="mb-4">
                <Button className="size-9 lg:size-auto" asChild>
                    <Link href={productTransferServices.create().url}>
                        <Plus />{' '}
                        <span className="hidden lg:inline">
                            Pindah Stok Baru
                        </span>
                    </Link>
                </Button>
            </div>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="p-0 lg:px-6">
                    <DocumentFilters
                        indexUrl={productTransferServices.index().url}
                        locationOptions={locationOptions}
                        statusTabs={statusTabs}
                    />
                </CardHeader>

                <CardContent className="border-t p-0 lg:border-0 lg:px-6">
                    <Table className="stripped">
                        <TableHeader>
                            <TableRow>
                                <TableHead>No.</TableHead>
                                <TableHead>Kode</TableHead>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>Asal → Tujuan</TableHead>
                                <TableHead>Karyawan</TableHead>
                                <TableHead className="text-right">
                                    Produk
                                </TableHead>
                                <TableHead className="text-right">
                                    Jumlah
                                </TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-center">
                                    Aksi
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {pagination.data.map((transfer, index) => (
                                <TableRow key={transfer.id}>
                                    <TableCell>
                                        {startIndex + index + 1}.
                                    </TableCell>
                                    <TableCell className="font-medium">
                                        {transfer.code}
                                    </TableCell>
                                    <TableCell>
                                        {formatDocumentDate(
                                            transfer.local_requested_at,
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <span className="flex items-center gap-1">
                                            {capitalize(
                                                transfer.from_location?.name ??
                                                    '-',
                                            )}
                                            <ArrowRight className="size-3 text-muted-foreground" />
                                            {capitalize(
                                                transfer.to_location?.name ??
                                                    '-',
                                            )}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        {employeeName(
                                            transfer.employee_requested_by,
                                        )}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        {transfer.details_count ?? 0}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        {Number(transfer.total_quantity ?? 0)}
                                    </TableCell>
                                    <TableCell>
                                        <StockStatusBadge
                                            status={transfer.status}
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex justify-center">
                                            <Button
                                                size="icon"
                                                variant="outline"
                                                asChild
                                            >
                                                <Link
                                                    href={
                                                        productTransferServices.show(
                                                            transfer.id,
                                                        ).url
                                                    }
                                                >
                                                    <SquareArrowOutUpRight />
                                                </Link>
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                            {!pagination.data.length && (
                                <TableRow>
                                    <TableCell
                                        colSpan={9}
                                        className="py-4 text-center text-muted-foreground"
                                    >
                                        Data tidak ditemukan
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>

                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
};
