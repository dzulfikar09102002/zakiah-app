import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Ban } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { toast } from 'sonner';

import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { capitalize, cn, toRupiah } from '@/lib/utils';
import saleTransactions from '@/routes/sale-transactions';
import type { BreadcrumbItem } from '@/types';

type SaleTransactionDetail = {
    id: number;
    product_name: string;
    product_sku: string | null;
    product_unit_name: string | null;
    quantity: number;
    sell_price: number;
    promo_amount: number;
    discount_amount: number;
    total_amount: number;
    notes: string | null;
    status: string;
};

type SaleTransactionPayment = {
    id: number;
    payment_method_name: string;
    amount_receive: number;
    change: number;
    platform_fee: number;
    card_number: string | null;
    approval_code: string | null;
};

type SaleTransaction = {
    id: number;
    sales_no: string;
    receipt_no: string;
    status: 'ok' | 'void';
    location_name: string;
    order_type_name: string;
    local_sales_at: string;
    local_paid_at: string | null;
    customer_first_name: string | null;
    customer_last_name: string | null;
    customer_phone_number: string | null;
    cashier_first_name: string | null;
    cashier_last_name: string | null;
    employee_sales_first_name: string | null;
    employee_sales_last_name: string | null;
    void_by_first_name: string | null;
    void_by_last_name: string | null;
    local_void_at: string | null;
    void_reason: string | null;
    void_notes: string | null;
    notes: string | null;
    subtotal: number;
    discount_amount: number;
    promo_amount: number;
    surcharge_amount: number;
    tax_amount: number;
    rounding_amount: number;
    payment_platform_fee: number;
    net_sales_after_tax: number;
    refunded_amount: number;
    sale_transaction_details: SaleTransactionDetail[];
    sale_transaction_payments: SaleTransactionPayment[];
};

type Props = {
    saleTransaction: SaleTransaction;
};

const formatDateTime = (value?: string | null) =>
    value
        ? new Date(value).toLocaleString('id-ID', {
              day: '2-digit',
              month: 'long',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '-';

const personName = (
    first?: string | null,
    last?: string | null,
    placeholderLast?: string,
) => {
    const name = [first, last === placeholderLast ? null : last]
        .filter(Boolean)
        .join(' ')
        .replace(/_/g, ' ')
        .trim();
    return name ? capitalize(name) : '-';
};

function InfoItem({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className="text-sm font-medium">{value}</p>
        </div>
    );
}

function SummaryRow({
    label,
    value,
    bold,
}: {
    label: string;
    value: string;
    bold?: boolean;
}) {
    return (
        <div
            className={cn(
                'flex items-center justify-between py-1.5 text-sm',
                bold && 'text-base font-semibold',
            )}
        >
            <span className={cn(!bold && 'text-muted-foreground')}>
                {label}
            </span>
            <span className="tabular-nums">{value}</span>
        </div>
    );
}

function VoidDialog({
    saleTransaction,
    open,
    onOpenChange,
}: {
    saleTransaction: SaleTransaction;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const {
        data,
        setData,
        patch,
        processing,
        errors,
        reset,
        setError,
        clearErrors,
    } = useForm({
        reason: '',
        notes: '',
    });

    const handleChange = (field: 'reason' | 'notes', value: string) => {
        setData(field, value);
        if (errors[field] && value.trim()) {
            clearErrors(field);
        }
    };

    const validate = () => {
        const clientErrors: Partial<Record<'reason' | 'notes', string>> = {};

        if (!data.reason.trim()) {
            clientErrors.reason = 'Alasan wajib diisi';
        }
        if (!data.notes.trim()) {
            clientErrors.notes = 'Catatan wajib diisi';
        }

        clearErrors();
        setError(clientErrors);

        return Object.keys(clientErrors).length === 0;
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();

        if (!validate()) {
            return;
        }

        patch(saleTransactions.void(saleTransaction.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Transaksi berhasil dibatalkan');
                reset();
                onOpenChange(false);
            },
            onError: () => {
                toast.error('Gagal membatalkan transaksi');
            },
        });
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(value) => {
                if (processing) return;
                if (!value) {
                    reset();
                    clearErrors();
                }
                onOpenChange(value);
            }}
        >
            <DialogContent>
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Batalkan Transaksi</DialogTitle>
                        <DialogDescription>
                            Transaksi {saleTransaction.sales_no} akan dibatalkan
                            dan semua produk dikembalikan ke stok. Tindakan ini
                            tidak dapat diurungkan.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-2">
                        <Label htmlFor="reason">
                            Alasan <span className="text-destructive">*</span>
                        </Label>
                        <Input
                            id="reason"
                            value={data.reason}
                            onChange={(e) =>
                                handleChange('reason', e.target.value)
                            }
                            aria-invalid={!!errors.reason}
                            placeholder="Contoh: Salah input pesanan"
                        />
                        <InputError message={errors.reason} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="notes">
                            Catatan <span className="text-destructive">*</span>
                        </Label>
                        <Textarea
                            id="notes"
                            value={data.notes}
                            onChange={(e) =>
                                handleChange('notes', e.target.value)
                            }
                            aria-invalid={!!errors.notes}
                            placeholder="Keterangan tambahan"
                        />
                        <InputError message={errors.notes} />
                    </div>

                    <DialogFooter>
                        <DialogClose asChild>
                            <Button
                                type="button"
                                variant="outline"
                                disabled={processing}
                            >
                                Batal
                            </Button>
                        </DialogClose>
                        <Button
                            type="submit"
                            variant="destructive"
                            disabled={processing}
                        >
                            {processing && <Spinner />}
                            Batalkan Transaksi
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function SaleTransactionShow({ saleTransaction: t }: Props) {
    const [voidOpen, setVoidOpen] = useState(false);

    const title = t.sales_no;
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Data Penjualan', href: saleTransactions.index().url },
        { title, href: saleTransactions.show(t.id).url },
    ];

    const totalItem = t.sale_transaction_details.reduce(
        (sum, d) => sum + d.quantity,
        0,
    );
    const totalDiscount = t.discount_amount + t.promo_amount;
    const isVoid = t.status === 'void';

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <div className="flex flex-col gap-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-xl font-semibold">
                                {t.sales_no}
                            </h1>
                            {isVoid ? (
                                <Badge variant="destructive">Dibatalkan</Badge>
                            ) : (
                                <Badge variant="secondary">Berhasil</Badge>
                            )}
                        </div>
                        <p className="text-sm text-muted-foreground">
                            No. Struk {t.receipt_no} ·{' '}
                            {formatDateTime(t.local_sales_at)}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={saleTransactions.index().url}>
                                <ArrowLeft /> Kembali
                            </Link>
                        </Button>
                        {!isVoid && (
                            <Button
                                variant="destructive"
                                onClick={() => setVoidOpen(true)}
                            >
                                <Ban /> Batalkan
                            </Button>
                        )}
                    </div>
                </div>

                {isVoid && (
                    <Alert variant="destructive">
                        <Ban />
                        <AlertTitle>
                            Dibatalkan oleh{' '}
                            {personName(
                                t.void_by_first_name,
                                t.void_by_last_name,
                            )}{' '}
                            pada {formatDateTime(t.local_void_at)}
                        </AlertTitle>
                        <AlertDescription>
                            <p>Alasan: {t.void_reason || '-'}</p>
                            <p>Catatan: {t.void_notes || '-'}</p>
                        </AlertDescription>
                    </Alert>
                )}

                <Card>
                    <CardContent className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                        <InfoItem
                            label="Lokasi"
                            value={capitalize(t.location_name ?? '-')}
                        />
                        <InfoItem
                            label="Jenis Pesanan"
                            value={t.order_type_name ?? '-'}
                        />
                        <InfoItem
                            label="Pelanggan"
                            value={personName(
                                t.customer_first_name,
                                t.customer_last_name,
                            )}
                        />
                        <InfoItem
                            label="Kasir"
                            value={personName(
                                t.cashier_first_name,
                                t.cashier_last_name,
                                'Kasir',
                            )}
                        />
                        <InfoItem
                            label="Sales"
                            value={personName(
                                t.employee_sales_first_name,
                                t.employee_sales_last_name,
                                'Sales',
                            )}
                        />
                        <InfoItem
                            label="Waktu Bayar"
                            value={formatDateTime(t.local_paid_at)}
                        />
                    </CardContent>
                </Card>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Produk ({totalItem} item)</CardTitle>
                        </CardHeader>
                        <CardContent className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Produk</TableHead>
                                        <TableHead className="text-right">
                                            Qty
                                        </TableHead>
                                        <TableHead className="text-right">
                                            Harga
                                        </TableHead>
                                        <TableHead className="text-right">
                                            Diskon
                                        </TableHead>
                                        <TableHead className="text-right">
                                            Subtotal
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {t.sale_transaction_details.map((d) => {
                                        const discount =
                                            d.promo_amount + d.discount_amount;

                                        return (
                                            <TableRow key={d.id}>
                                                <TableCell>
                                                    <p className="font-medium">
                                                        {d.product_name}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {[
                                                            d.product_sku,
                                                            d.product_unit_name,
                                                        ]
                                                            .filter(Boolean)
                                                            .join(' · ')}
                                                    </p>
                                                    {d.notes && (
                                                        <p className="text-xs text-muted-foreground italic">
                                                            {d.notes}
                                                        </p>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-right tabular-nums">
                                                    {d.quantity}
                                                </TableCell>
                                                <TableCell className="text-right tabular-nums">
                                                    {toRupiah(d.sell_price)}
                                                </TableCell>
                                                <TableCell className="text-right tabular-nums">
                                                    {discount > 0
                                                        ? toRupiah(
                                                              discount * -1,
                                                          )
                                                        : '-'}
                                                </TableCell>
                                                <TableCell className="text-right font-medium tabular-nums">
                                                    {toRupiah(
                                                        d.sell_price *
                                                            d.quantity -
                                                            discount,
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        );
                                    })}
                                </TableBody>
                            </Table>
                            {t.notes && (
                                <p className="mt-4 text-sm text-muted-foreground">
                                    Catatan: {t.notes}
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <div className="flex flex-col gap-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Ringkasan</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <SummaryRow
                                    label="Subtotal"
                                    value={toRupiah(t.subtotal)}
                                />
                                {totalDiscount > 0 && (
                                    <SummaryRow
                                        label="Diskon"
                                        value={toRupiah(totalDiscount * -1)}
                                    />
                                )}
                                {t.surcharge_amount > 0 && (
                                    <SummaryRow
                                        label="Penyesuaian"
                                        value={toRupiah(t.surcharge_amount)}
                                    />
                                )}
                                <SummaryRow
                                    label="Pajak"
                                    value={toRupiah(t.tax_amount)}
                                />
                                {t.rounding_amount !== 0 && (
                                    <SummaryRow
                                        label="Pembulatan"
                                        value={toRupiah(t.rounding_amount)}
                                    />
                                )}
                                {t.payment_platform_fee > 0 && (
                                    <SummaryRow
                                        label="Biaya Layanan"
                                        value={toRupiah(t.payment_platform_fee)}
                                    />
                                )}
                                <Separator className="my-1" />
                                <SummaryRow
                                    label="Total"
                                    value={toRupiah(
                                        t.net_sales_after_tax +
                                            t.payment_platform_fee,
                                    )}
                                    bold
                                />
                                {t.refunded_amount > 0 && (
                                    <SummaryRow
                                        label="Dikembalikan"
                                        value={toRupiah(t.refunded_amount * -1)}
                                    />
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Pembayaran</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {t.sale_transaction_payments.length ? (
                                    t.sale_transaction_payments.map((p) => (
                                        <div key={p.id} className="py-1.5">
                                            <SummaryRow
                                                label={p.payment_method_name}
                                                value={toRupiah(
                                                    p.amount_receive,
                                                )}
                                            />
                                            {p.change > 0 && (
                                                <SummaryRow
                                                    label="Kembalian"
                                                    value={toRupiah(p.change)}
                                                />
                                            )}
                                            {p.approval_code && (
                                                <p className="text-xs text-muted-foreground">
                                                    Kode approval:{' '}
                                                    {p.approval_code}
                                                </p>
                                            )}
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        Tidak ada data pembayaran
                                    </p>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>

            <VoidDialog
                saleTransaction={t}
                open={voidOpen}
                onOpenChange={setVoidOpen}
            />
        </AppLayout>
    );
}
