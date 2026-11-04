import { Check, FileText, PackageSearch } from 'lucide-react';
import { useState } from 'react';
import SearchableSelect from '@/components/searchable-select';
import { DifferenceValue } from '@/components/stock/helpers';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Field, FieldDescription, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import productOpnameSessions from '@/routes/product-opname-sessions';
import FinishDialog from './finish-dialog';
import { postSessionAction, sessionEmployeeName } from './helpers';
import type { OpnameSession, ReviewRow, RevisionData, Viewer } from './types';
import { REASON_LOST } from './types';

type Props = {
    session: OpnameSession;
    viewer: Viewer;
    revision: RevisionData;
};

const signed = (value: number) => (value > 0 ? `+${value}` : `${value}`);

export default function RevisionPanel({ session, viewer, revision }: Props) {
    const editable = viewer.is_pic && session.status === 'started';

    const [product, setProduct] = useState<ReviewRow | null>(null);
    const [reason, setReason] = useState('');
    const [qty, setQty] = useState('0');
    const [note, setNote] = useState('');
    const [processing, setProcessing] = useState(false);
    const [finishOpen, setFinishOpen] = useState(false);

    const closeForm = () => {
        if (processing) {
            return;
        }

        setProduct(null);
        setReason('');
        setQty('0');
        setNote('');
    };

    const isLost = Number(reason) === REASON_LOST;
    const qtyValue = Number(qty);
    const validQty = qty.trim() !== '' && Number.isInteger(qtyValue);

    const submit = () => {
        if (!product) {
            return;
        }

        setProcessing(true);
        postSessionAction(
            productOpnameSessions.storeRevision(session.id).url,
            {
                product_id: product.product_id,
                reason: Number(reason),
                adjustment_qty: isLost ? 0 : qtyValue,
                note: note.trim() || null,
            },
            {
                success: 'Hasil revisi disimpan',
                onSuccess: () => {
                    setProduct(null);
                    setReason('');
                    setQty('0');
                    setNote('');
                },
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <div className="space-y-4">
            {session.status === 'finished' ? (
                <Button className="h-11 w-full md:h-9 md:w-auto" asChild>
                    <a
                        href={productOpnameSessions.report(session.id).url}
                        target="_blank"
                        rel="noreferrer"
                    >
                        <FileText /> Lihat Laporan Revisi
                    </a>
                </Button>
            ) : (
                editable && (
                    <Button
                        type="button"
                        className="h-11 w-full md:h-9 md:w-auto"
                        disabled={revision.rows.length === 0}
                        onClick={() => setFinishOpen(true)}
                    >
                        <Check /> Simpan dan Akhiri Revisi
                    </Button>
                )
            )}

            {session.status !== 'finished' && (
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <PackageSearch className="size-5 text-primary" />{' '}
                            Produk dari Lampiran SO
                        </CardTitle>
                        <CardDescription>
                            Catat hasil pencarian barang. Penyesuaian langsung
                            mengubah stok saat disimpan.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul className="divide-y rounded-lg border">
                            {revision.products.map((row) => (
                                <li
                                    key={row.product_id}
                                    className="flex items-center gap-3 p-3"
                                >
                                    <div className="min-w-0 flex-1">
                                        <p className="font-medium break-words">
                                            {row.name}
                                        </p>
                                        <p className="truncate text-xs text-muted-foreground">
                                            {row.barcode ?? row.sku ?? '-'} •
                                            selisih SO{' '}
                                            <DifferenceValue
                                                value={row.difference_stock}
                                            />
                                        </p>
                                    </div>
                                    {editable && (
                                        <Button
                                            type="button"
                                            variant="outline"
                                            className="h-11 shrink-0 md:h-9"
                                            onClick={() => setProduct(row)}
                                        >
                                            Input
                                        </Button>
                                    )}
                                </li>
                            ))}
                        </ul>
                    </CardContent>
                </Card>
            )}

            <Card>
                <CardHeader>
                    <CardTitle>Hasil Revisi</CardTitle>
                </CardHeader>
                <CardContent>
                    <ul className="divide-y rounded-lg border">
                        {revision.rows.map((row) => (
                            <li
                                key={`${row.product_id}-${row.seq}`}
                                className="flex items-center gap-3 p-3"
                            >
                                <div className="min-w-0 flex-1">
                                    <p className="font-medium break-words">
                                        {row.name}
                                    </p>
                                    <p className="text-xs break-words text-muted-foreground">
                                        {row.reason_label}
                                        {row.note ? ` • ${row.note}` : ''}
                                    </p>
                                </div>
                                <span className="shrink-0 font-semibold">
                                    {signed(row.adjustment_qty)}
                                </span>
                            </li>
                        ))}
                        {!revision.rows.length && (
                            <li className="p-6 text-center text-sm text-muted-foreground">
                                Belum ada hasil revisi
                            </li>
                        )}
                    </ul>
                </CardContent>
            </Card>

            <Dialog
                open={product !== null}
                onOpenChange={(open) => !open && closeForm()}
            >
                <DialogContent className="max-h-[90dvh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Hasil Pencarian Barang</DialogTitle>
                        <DialogDescription className="break-words">
                            {product?.name}
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <Field>
                            <FieldLabel>Alasan</FieldLabel>
                            <SearchableSelect
                                className="h-11 md:h-9"
                                value={reason}
                                disabled={processing}
                                placeholder="Pilih alasan"
                                searchPlaceholder="Cari alasan..."
                                options={revision.reasons.map((item) => ({
                                    value: String(item.value),
                                    label: item.label,
                                }))}
                                onChange={setReason}
                            />
                        </Field>

                        <Field>
                            <FieldLabel htmlFor="adjustment-qty">
                                Penyesuaian stok
                            </FieldLabel>
                            <Input
                                id="adjustment-qty"
                                type="number"
                                inputMode="numeric"
                                step={1}
                                className="h-11 text-base md:h-9 md:text-sm"
                                disabled={isLost || processing}
                                value={isLost ? '0' : qty}
                                onChange={(event) => setQty(event.target.value)}
                            />
                            <FieldDescription>
                                {isLost
                                    ? 'Barang hilang tidak mengubah stok, karena sudah dikurangi saat SO.'
                                    : 'Isi plus bila barang ketemu, minus bila stok perlu dikurangi lagi.'}
                            </FieldDescription>
                        </Field>

                        <Field>
                            <FieldLabel htmlFor="revision-note">
                                Catatan
                            </FieldLabel>
                            <Textarea
                                id="revision-note"
                                placeholder="Mis. ketemu di gudang belakang"
                                disabled={processing}
                                value={note}
                                onChange={(event) =>
                                    setNote(event.target.value)
                                }
                            />
                        </Field>
                    </div>

                    <DialogFooter className="gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            className="h-11 md:h-9"
                            disabled={processing}
                            onClick={closeForm}
                        >
                            Batal
                        </Button>
                        <Button
                            type="button"
                            className="h-11 md:h-9"
                            disabled={
                                !reason || processing || (!isLost && !validQty)
                            }
                            onClick={submit}
                        >
                            {processing && <Spinner />}
                            Simpan
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <FinishDialog
                open={finishOpen}
                onOpenChange={setFinishOpen}
                sessionId={session.id}
                sessionCode={session.code}
                picName={sessionEmployeeName(session.pic)}
                statement={`Saya, ${sessionEmployeeName(session.pic)}, selaku PJ SO menyatakan seluruh hasil pencarian barang ini benar dan bertanggung jawab penuh atas setiap penyesuaian stok yang telah saya simpan.`}
            />
        </div>
    );
}
