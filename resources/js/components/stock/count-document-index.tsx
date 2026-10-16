import { Head, Link } from '@inertiajs/react';
import { Plus, SquareArrowOutUpRight } from 'lucide-react';
import DocumentFilters from '@/components/stock/document-filters';
import {
    DifferenceValue,
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
import type { Pagination, StockCountDocument } from '@/lib/model';
import { capitalize } from '@/lib/utils';
import type { Option } from '@/types';
import type { CountDocumentConfig } from './count-document';

type Props = {
    config: CountDocumentConfig;
    pagination: Pagination<StockCountDocument>;
    locationOptions: Option[];
};

export default function CountDocumentIndex({
    config,
    pagination,
    locationOptions,
}: Props) {
    const startIndex = (pagination.current_page - 1) * pagination.per_page;

    return (
        <AppLayout
            breadcrumbs={[
                { title: config.title, href: config.routes.index().url },
            ]}
        >
            <Head title={config.title} />

            <div className="mb-4">
                <Button className="size-9 lg:size-auto" asChild>
                    <Link href={config.routes.create().url}>
                        <Plus />{' '}
                        <span className="hidden lg:inline">
                            {config.title} Baru
                        </span>
                    </Link>
                </Button>
            </div>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="p-0 lg:px-6">
                    <DocumentFilters
                        indexUrl={config.routes.index().url}
                        locationOptions={locationOptions}
                        statusTabs={stockStatusTabs}
                    />
                </CardHeader>

                <CardContent className="border-t p-0 lg:border-0 lg:px-6">
                    <Table className="stripped">
                        <TableHeader>
                            <TableRow>
                                <TableHead>No.</TableHead>
                                <TableHead>Kode</TableHead>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>Lokasi</TableHead>
                                <TableHead>Karyawan</TableHead>
                                <TableHead className="text-right">
                                    Produk
                                </TableHead>
                                <TableHead className="text-right">
                                    Selisih
                                </TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-center">
                                    Aksi
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {pagination.data.map((document, index) => (
                                <TableRow key={document.id}>
                                    <TableCell>
                                        {startIndex + index + 1}.
                                    </TableCell>
                                    <TableCell className="font-medium">
                                        {document.code}
                                    </TableCell>
                                    <TableCell>
                                        {formatDocumentDate(
                                            document.local_requested_at,
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {capitalize(
                                            document.location?.name ?? '-',
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {employeeName(
                                            document.employee_requested_by,
                                        )}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        {document.details_count ?? 0}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <DifferenceValue
                                            value={document.difference_stock}
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <StockStatusBadge
                                            status={document.status}
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
                                                        config.routes.show(
                                                            document.id,
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
}
