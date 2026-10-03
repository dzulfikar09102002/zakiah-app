import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Ban, Check, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { useState } from 'react';
import ApprovalDialog from '@/components/stock/approval-dialog';
import type { ApprovalAction } from '@/components/stock/approval-dialog';
import {
    StockStatusBadge,
    employeeName,
    formatDocumentDate,
} from '@/components/stock/helpers';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import type { EmployeeName, StockTransfer } from '@/lib/model';
import { capitalize, toRupiah } from '@/lib/utils';
import productTransferServices from '@/routes/product-transfer-services';

type Props = {
    document: StockTransfer;
};

function InfoItem({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div>
            <p className="text-xs text-muted-foreground">{label}</p>
            <div className="font-medium">{children}</div>
        </div>
    );
}

function ActorInfo({
    label,
    employee,
    at,
    note,
}: {
    label: string;
    employee?: EmployeeName | null;
    at: string | null;
    note?: string | null;
}) {
    return (
        <InfoItem label={label}>
            {employeeName(employee)}
            <p className="text-xs font-normal text-muted-foreground">
                {formatDocumentDate(at)}
                {note && ` • ${note}`}
            </p>
        </InfoItem>
    );
}

export default ({ document }: Props) => {
    const [action, setAction] = useState<ApprovalAction | null>(null);
    const details = document.product_transfer_service_details ?? [];
    const totalQuantity = details.reduce((sum, row) => sum + row.quantity, 0);
    const totalValue = details.reduce(
        (sum, row) => sum + row.quantity * (row.product?.sell_price ?? 0),
        0,
    );
    const title = `Pindah Stok ${document.code}`;

    return (
        <AppLayout
            breadcrumbs={[
                {
                    title: 'Pindah Stok',
                    href: productTransferServices.index().url,
                },
                { title: document.code, href: '#' },
            ]}
        >
            <Head title={title} />

            <ApprovalDialog action={action} onClose={() => setAction(null)} />

            <div className="mb-4 flex flex-wrap gap-2">
                <Button variant="outline" asChild>
                    <Link href={productTransferServices.index().url}>
                        <ArrowLeft /> Kembali
                    </Link>
                </Button>
                {document.status === 'requested' && (
                    <>
                        <Button
                            onClick={() =>
                                setAction({
                                    title: 'Setujui Pindah Stok',
                                    description:
                                        'Stok akan masuk ke lokasi tujuan.',
                                    confirmLabel: 'Setujui',
                                    successMessage: 'Pindah stok disetujui',
                                    url: productTransferServices.approve(
                                        document.id,
                                    ).url,
                                })
                            }
                        >
                            <Check /> Setujui
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={() =>
                                setAction({
                                    title: 'Tolak Pindah Stok',
                                    description:
                                        'Stok yang dicadangkan akan dikembalikan ke lokasi asal.',
                                    confirmLabel: 'Tolak',
                                    successMessage: 'Pindah stok ditolak',
                                    url: productTransferServices.reject(
                                        document.id,
                                    ).url,
                                    destructive: true,
                                })
                            }
                        >
                            <X /> Tolak
                        </Button>
                        <Button
                            variant="outline"
                            onClick={() =>
                                setAction({
                                    title: 'Batalkan Pindah Stok',
                                    description:
                                        'Pengajuan dibatalkan dan stok dikembalikan ke lokasi asal.',
                                    confirmLabel: 'Batalkan Pengajuan',
                                    successMessage: 'Pindah stok dibatalkan',
                                    url: productTransferServices.cancel(
                                        document.id,
                                    ).url,
                                    destructive: true,
                                })
                            }
                        >
                            <Ban /> Batalkan
                        </Button>
                    </>
                )}
            </div>

            <div className="grid gap-4">
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            {title}
                            <StockStatusBadge status={document.status} />
                        </CardTitle>
                        <CardDescription className="flex items-center gap-2 text-base">
                            {capitalize(document.from_location?.name ?? '-')}
                            <ArrowRight className="size-4" />
                            {capitalize(document.to_location?.name ?? '-')}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        <ActorInfo
                            label="Diajukan"
                            employee={document.employee_requested_by}
                            at={document.local_requested_at}
                            note={document.request_note}
                        />
                        {document.status === 'approved' && (
                            <ActorInfo
                                label="Disetujui"
                                employee={document.employee_approved_by}
                                at={document.local_approved_at}
                                note={document.approval_note}
                            />
                        )}
                        {document.status === 'rejected' && (
                            <ActorInfo
                                label="Ditolak"
                                employee={document.employee_rejected_by}
                                at={document.local_rejected_at}
                                note={document.rejected_note}
                            />
                        )}
                        {document.status === 'cancelled' && (
                            <ActorInfo
                                label="Dibatalkan"
                                employee={document.employee_cancelled_by}
                                at={document.local_cancelled_at}
                                note={document.cancelled_note}
                            />
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Produk</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table className="stripped">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>No.</TableHead>
                                    <TableHead>Produk</TableHead>
                                    <TableHead>Satuan</TableHead>
                                    <TableHead className="text-right">
                                        Jumlah
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Harga Jual
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Nilai
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {details.map((row, index) => (
                                    <TableRow key={row.id}>
                                        <TableCell>{index + 1}.</TableCell>
                                        <TableCell>
                                            <p className="font-medium">
                                                {row.product_name}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {row.product_sku}
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            {row.product_unit_name || '-'}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {row.quantity}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {toRupiah(
                                                row.product?.sell_price ?? 0,
                                            )}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {toRupiah(
                                                row.quantity *
                                                    (row.product?.sell_price ??
                                                        0),
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                            <TableFooter>
                                <TableRow>
                                    <TableCell colSpan={3}>Total</TableCell>
                                    <TableCell className="text-right">
                                        {totalQuantity}
                                    </TableCell>
                                    <TableCell />
                                    <TableCell className="text-right">
                                        {toRupiah(totalValue)}
                                    </TableCell>
                                </TableRow>
                            </TableFooter>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
};
