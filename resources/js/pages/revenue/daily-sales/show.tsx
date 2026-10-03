import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

import SaleTransactionTable, {
    type DailySaleTransaction,
} from '@/components/daily-sales/sale-transaction-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
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
import type { Option, Pagination } from '@/lib/model';
import { capitalize, cn, toRupiah } from '@/lib/utils';
import dailySales from '@/routes/daily-sales';
import type { BreadcrumbItem } from '@/types';

type PaymentDetail = {
    counted_amount: number;
    recorded_amount: number;
    difference_amount: number;
    sales_amount: number;
    payment_method: { id: number; name: string } | null;
};

type TakingAll = {
    local_taking_at: string;
    employee_first_name: string | null;
    employee_last_name: string | null;
    sales_count: number;
    refund_count: number;
    gross_sales: number;
    gross_refund: number;
    discount_amount: number;
    discount_amount_refund: number;
    promo_amount: number;
    promo_amount_refund: number;
    surcharge_amount: number;
    surcharge_amount_refund: number;
    net_sales: number;
    net_sales_refund: number;
    tax_amount: number;
    tax_amount_refund: number;
    net_sales_after_tax: number;
    net_sales_after_tax_refund: number;
    counted_amount: number;
    recorded_amount: number;
    taking_payment_details: PaymentDetail[];
};

type Taking = {
    id: number;
    is_shift: boolean;
    shift_number: number | null;
    local_taking_at: string;
    employee_first_name: string | null;
    employee_last_name: string | null;
    sales_count: number;
    refund_count: number;
    counted_amount: number;
    recorded_amount: number;
    salesSummaries: {
        grossSales: number;
        discountBeforeTax: number;
        promoBeforeTax: number;
        surchargeBeforeTax: number;
        netSales: number;
        taxAmount: number;
        netSalesAfterTax: number;
    };
};

type DailySale = {
    id: number;
    taking_id: number | null;
    local_sales_at: string;
    location: { id: number; name: string } | null;
    takingAll: TakingAll;
    takings: Taking[];
};

type Props = {
    dailySale: DailySale;
    saleTransactions: Pagination<DailySaleTransaction>;
    shiftOptions: Option[];
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

const employeeName = (first?: string | null, last?: string | null) => {
    const name = [first, last].filter(Boolean).join(' ').replace(/_/g, ' ');
    return name ? capitalize(name) : '-';
};

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
                bold && 'font-semibold',
            )}
        >
            <span className={cn(!bold && 'text-muted-foreground')}>
                {label}
            </span>
            <span className="tabular-nums">{value}</span>
        </div>
    );
}

function StatCard({ label, value }: { label: string; value: string }) {
    return (
        <Card className="gap-1 py-4">
            <CardContent className="px-4">
                <p className="text-sm text-muted-foreground">{label}</p>
                <p className="text-xl font-semibold tabular-nums">{value}</p>
            </CardContent>
        </Card>
    );
}

export default function DailySaleShow({
    dailySale,
    saleTransactions,
    shiftOptions,
}: Props) {
    const all = dailySale.takingAll;

    const title = 'Detail Rekapan';
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Rekapan Penjualan', href: dailySales.index().url },
        { title, href: dailySales.show(dailySale.id).url },
    ];

    const totalDiscount =
        all.discount_amount -
        all.discount_amount_refund +
        (all.promo_amount - all.promo_amount_refund);
    const netSales = all.net_sales - all.net_sales_refund;
    const totalSales = all.net_sales_after_tax - all.net_sales_after_tax_refund;
    const difference = all.counted_amount - all.recorded_amount;

    const paymentTotals = all.taking_payment_details.reduce(
        (acc, p) => ({
            recorded: acc.recorded + p.recorded_amount,
            counted: acc.counted + p.counted_amount,
            difference: acc.difference + p.difference_amount,
        }),
        { recorded: 0, counted: 0, difference: 0 },
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <div className="flex flex-col gap-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h1 className="text-xl font-semibold">
                            {dailySale.location?.name
                                ? capitalize(dailySale.location.name)
                                : '-'}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {formatDateTime(all.local_taking_at)} · Kasir{' '}
                            {employeeName(
                                all.employee_first_name,
                                all.employee_last_name,
                            )}
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        {dailySale.taking_id ? (
                            <Badge variant="secondary">Tutup Shift</Badge>
                        ) : (
                            <Badge variant="outline">Belum Tutup Shift</Badge>
                        )}
                        <Button variant="outline" asChild>
                            <Link href={dailySales.index().url}>
                                <ArrowLeft /> Kembali
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        label="Penjualan Total"
                        value={toRupiah(totalSales)}
                    />
                    <StatCard
                        label="Penjualan Bersih"
                        value={toRupiah(netSales)}
                    />
                    <StatCard
                        label="Jumlah Transaksi"
                        value={`${all.sales_count} penjualan · ${all.refund_count} retur`}
                    />
                    <StatCard
                        label="Selisih Kas"
                        value={toRupiah(difference)}
                    />
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="lg:col-span-1">
                        <CardHeader>
                            <CardTitle>Ringkasan Penjualan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <SummaryRow
                                label="Penjualan Kotor"
                                value={toRupiah(all.gross_sales)}
                            />
                            <SummaryRow
                                label="Pengembalian Kotor"
                                value={toRupiah(all.gross_refund)}
                            />
                            <SummaryRow
                                label="Total Diskon"
                                value={toRupiah(totalDiscount * -1)}
                            />
                            <SummaryRow
                                label="Total Penyesuaian"
                                value={toRupiah(
                                    all.surcharge_amount -
                                        all.surcharge_amount_refund,
                                )}
                            />
                            <Separator className="my-1" />
                            <SummaryRow
                                label="Penjualan Bersih"
                                value={toRupiah(netSales)}
                                bold
                            />
                            <SummaryRow
                                label="Total Pajak"
                                value={toRupiah(
                                    all.tax_amount - all.tax_amount_refund,
                                )}
                            />
                            <Separator className="my-1" />
                            <SummaryRow
                                label="Penjualan Total"
                                value={toRupiah(totalSales)}
                                bold
                            />
                        </CardContent>
                    </Card>

                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Metode Pembayaran</CardTitle>
                            <CardDescription>
                                Perbandingan uang tercatat sistem dengan uang
                                yang dihitung kasir.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Metode</TableHead>
                                        <TableHead className="text-right">
                                            Tercatat
                                        </TableHead>
                                        <TableHead className="text-right">
                                            Dihitung
                                        </TableHead>
                                        <TableHead className="text-right">
                                            Selisih
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {all.taking_payment_details.length ? (
                                        all.taking_payment_details.map(
                                            (p, i) => (
                                                <TableRow key={i}>
                                                    <TableCell>
                                                        {p.payment_method
                                                            ?.name ?? '-'}
                                                    </TableCell>
                                                    <TableCell className="text-right tabular-nums">
                                                        {toRupiah(
                                                            p.recorded_amount,
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-right tabular-nums">
                                                        {toRupiah(
                                                            p.counted_amount,
                                                        )}
                                                    </TableCell>
                                                    <TableCell
                                                        className={cn(
                                                            'text-right tabular-nums',
                                                            p.difference_amount <
                                                                0 &&
                                                                'text-destructive',
                                                        )}
                                                    >
                                                        {toRupiah(
                                                            p.difference_amount,
                                                        )}
                                                    </TableCell>
                                                </TableRow>
                                            ),
                                        )
                                    ) : (
                                        <TableRow>
                                            <TableCell
                                                colSpan={4}
                                                className="text-center"
                                            >
                                                Data tidak ditemukan
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                                <TableFooter>
                                    <TableRow>
                                        <TableCell>Total</TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {toRupiah(paymentTotals.recorded)}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {toRupiah(paymentTotals.counted)}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {toRupiah(paymentTotals.difference)}
                                        </TableCell>
                                    </TableRow>
                                </TableFooter>
                            </Table>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Rincian Shift</CardTitle>
                    </CardHeader>
                    <CardContent className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Shift</TableHead>
                                    <TableHead>Waktu Tutup</TableHead>
                                    <TableHead>Kasir</TableHead>
                                    <TableHead className="text-right">
                                        Transaksi
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Penjualan Kotor
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Diskon
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Penjualan Bersih
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Pajak
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Total
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {dailySale.takings.length ? (
                                    dailySale.takings.map((t) => (
                                        <TableRow key={t.id}>
                                            <TableCell>
                                                {t.is_shift
                                                    ? `Shift ${t.shift_number ?? '-'}`
                                                    : 'Tutup Shift'}
                                            </TableCell>
                                            <TableCell>
                                                {formatDateTime(
                                                    t.local_taking_at,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {employeeName(
                                                    t.employee_first_name,
                                                    t.employee_last_name,
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {t.sales_count}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {toRupiah(
                                                    t.salesSummaries.grossSales,
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {toRupiah(
                                                    (t.salesSummaries
                                                        .discountBeforeTax +
                                                        t.salesSummaries
                                                            .promoBeforeTax) *
                                                        -1,
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {toRupiah(
                                                    t.salesSummaries.netSales,
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {toRupiah(
                                                    t.salesSummaries.taxAmount,
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right font-medium tabular-nums">
                                                {toRupiah(
                                                    t.salesSummaries
                                                        .netSalesAfterTax,
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                ) : (
                                    <TableRow>
                                        <TableCell
                                            colSpan={9}
                                            className="text-center"
                                        >
                                            Data tidak ditemukan
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>

                        <Separator className="my-6" />

                        <SaleTransactionTable
                            pagination={saleTransactions}
                            shiftOptions={shiftOptions}
                        />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
