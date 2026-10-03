import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Check, Pencil, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import ApprovalDialog from '@/components/stock/approval-dialog';
import type { ApprovalAction } from '@/components/stock/approval-dialog';
import {
    DifferenceValue,
    StockStatusBadge,
    employeeName,
    formatDocumentDate,
} from '@/components/stock/helpers';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import type { StockCountDocument } from '@/lib/model';
import { capitalize, toRupiah } from '@/lib/utils';
import type { CountDocumentConfig } from './count-document';
import { documentDetails } from './count-document';

type Props = {
    config: CountDocumentConfig;
    document: StockCountDocument;
};

function InfoItem({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div>
            <p className="text-xs text-muted-foreground">{label}</p>
            <div className="font-medium">{value}</div>
        </div>
    );
}

export default function CountDocumentShow({ config, document }: Props) {
    const [onlyDifference, setOnlyDifference] = useState(false);
    const [action, setAction] = useState<ApprovalAction | null>(null);
    const [confirmDelete, setConfirmDelete] = useState(false);
    const [deleting, setDeleting] = useState(false);

    const details = documentDetails(document, config);
    const shownDetails = onlyDifference
        ? details.filter((detail) => detail.difference_stock !== 0)
        : details;
    const totalValue = details.reduce(
        (sum, detail) =>
            sum + detail.difference_stock * (detail.product?.sell_price ?? 0),
        0,
    );
    const isRequested = document.status === 'requested';

    const destroy = () => {
        if (!config.routes.destroy) return;

        router.delete(config.routes.destroy(document.id).url, {
            onBefore: () => setDeleting(true),
            onSuccess: () => toast.success(`${config.title} berhasil dihapus`),
            onError: (errors) =>
                toast.error(
                    Object.values(errors)[0] ??
                        `Gagal menghapus ${config.noun}`,
                ),
            onFinish: () => {
                setDeleting(false);
                setConfirmDelete(false);
            },
        });
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: config.title, href: config.routes.index().url },
                { title: document.code, href: '#' },
            ]}
        >
            <Head title={`${config.title} ${document.code}`} />

            <ApprovalDialog action={action} onClose={() => setAction(null)} />

            <AlertDialog
                open={confirmDelete}
                onOpenChange={(open) => !deleting && setConfirmDelete(open)}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Hapus {config.title}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            Dokumen {document.code} akan dihapus. Stok tidak
                            berubah karena dokumen belum disetujui.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel disabled={deleting}>
                            Batal
                        </AlertDialogCancel>
                        <Button
                            variant="destructive"
                            disabled={deleting}
                            onClick={destroy}
                        >
                            Hapus
                        </Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <div className="mb-4 flex flex-wrap gap-2">
                <Button variant="outline" asChild>
                    <Link href={config.routes.index().url}>
                        <ArrowLeft /> Kembali
                    </Link>
                </Button>
                {isRequested && (
                    <>
                        <Button
                            onClick={() =>
                                setAction({
                                    title: `Setujui ${config.title}`,
                                    description:
                                        'Stok lokasi akan disesuaikan sesuai selisih pada dokumen ini.',
                                    confirmLabel: 'Setujui',
                                    successMessage: `${config.title} disetujui`,
                                    url: config.routes.approve(document.id).url,
                                })
                            }
                        >
                            <Check /> Setujui
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={() =>
                                setAction({
                                    title: `Tolak ${config.title}`,
                                    description:
                                        'Dokumen ditolak dan stok tidak berubah.',
                                    confirmLabel: 'Tolak',
                                    successMessage: `${config.title} ditolak`,
                                    url: config.routes.reject(document.id).url,
                                    destructive: true,
                                })
                            }
                        >
                            <X /> Tolak
                        </Button>
                        {config.editable && config.routes.edit && (
                            <Button variant="outline" asChild>
                                <Link
                                    href={config.routes.edit(document.id).url}
                                >
                                    <Pencil /> Ubah
                                </Link>
                            </Button>
                        )}
                        {config.editable && config.routes.destroy && (
                            <Button
                                variant="outline"
                                onClick={() => setConfirmDelete(true)}
                            >
                                <Trash2 className="text-destructive" /> Hapus
                            </Button>
                        )}
                    </>
                )}
            </div>

            <div className="grid gap-4">
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            {config.title} {document.code}
                            <StockStatusBadge status={document.status} />
                        </CardTitle>
                        {document.note && (
                            <CardDescription>{document.note}</CardDescription>
                        )}
                    </CardHeader>
                    <CardContent className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        <InfoItem
                            label="Lokasi"
                            value={capitalize(document.location?.name ?? '-')}
                        />
                        <InfoItem
                            label="Diajukan"
                            value={
                                <>
                                    {employeeName(
                                        document.employee_requested_by,
                                    )}
                                    <p className="text-xs font-normal text-muted-foreground">
                                        {formatDocumentDate(
                                            document.local_requested_at,
                                        )}
                                    </p>
                                </>
                            }
                        />
                        {document.status === 'approved' && (
                            <InfoItem
                                label="Disetujui"
                                value={
                                    <>
                                        {employeeName(
                                            document.employee_approved_by,
                                        )}
                                        <p className="text-xs font-normal text-muted-foreground">
                                            {formatDocumentDate(
                                                document.local_approved_at,
                                            )}
                                            {document.approval_note &&
                                                ` • ${document.approval_note}`}
                                        </p>
                                    </>
                                }
                            />
                        )}
                        {document.status === 'rejected' && (
                            <InfoItem
                                label="Ditolak"
                                value={
                                    <>
                                        {employeeName(
                                            document.employee_rejected_by,
                                        )}
                                        <p className="text-xs font-normal text-muted-foreground">
                                            {formatDocumentDate(
                                                document.local_rejected_at,
                                            )}
                                            {document.rejected_note &&
                                                ` • ${document.rejected_note}`}
                                        </p>
                                    </>
                                }
                            />
                        )}
                    </CardContent>
                </Card>

                <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    {[
                        { label: 'Produk', value: details.length },
                        {
                            label: 'Produk Selisih',
                            value: document.difference_product_count,
                        },
                        {
                            label: 'Total Selisih',
                            value: (
                                <DifferenceValue
                                    value={document.difference_stock}
                                />
                            ),
                        },
                        { label: 'Nilai Selisih', value: toRupiah(totalValue) },
                    ].map((item) => (
                        <Card key={item.label} className="gap-1 py-4">
                            <CardContent className="px-4">
                                <p className="text-xs text-muted-foreground uppercase">
                                    {item.label}
                                </p>
                                <p className="text-xl font-semibold">
                                    {item.value}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between gap-2">
                        <CardTitle>Detail Produk</CardTitle>
                        <div className="flex items-center gap-2">
                            <Switch
                                id="only-difference"
                                checked={onlyDifference}
                                onCheckedChange={setOnlyDifference}
                            />
                            <Label htmlFor="only-difference">
                                Hanya yang selisih
                            </Label>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table className="stripped">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>No.</TableHead>
                                    <TableHead>Produk</TableHead>
                                    <TableHead>Kategori</TableHead>
                                    <TableHead>Satuan</TableHead>
                                    <TableHead className="text-right">
                                        Tercatat
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Fisik
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Selisih
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Nilai Selisih
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {shownDetails.map((detail, index) => (
                                    <TableRow key={detail.id}>
                                        <TableCell>{index + 1}.</TableCell>
                                        <TableCell>
                                            <p className="font-medium">
                                                {detail.product_name}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {detail.product_sku}
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            {detail.product_category_name ||
                                                '-'}
                                        </TableCell>
                                        <TableCell>
                                            {detail.product_unit_name || '-'}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {detail.recorded_stock}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {detail.counted_stock}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <DifferenceValue
                                                value={detail.difference_stock}
                                            />
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {toRupiah(
                                                detail.difference_stock *
                                                    (detail.product
                                                        ?.sell_price ?? 0),
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {!shownDetails.length && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={8}
                                            className="py-6 text-center text-muted-foreground"
                                        >
                                            Tidak ada produk
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
