import { Form, Head, Link } from '@inertiajs/react';
import {
    createColumnHelper,
    getCoreRowModel,
    useReactTable,
    type ColumnDef,
} from '@tanstack/react-table';
import { startOfMonth } from 'date-fns';
import type { LucideIcon } from 'lucide-react';
import {
    Banknote,
    CreditCard,
    Eye,
    QrCode,
    RotateCcw,
    Scale,
    Search,
    Wallet,
} from 'lucide-react';
import QueryString from 'qs';
import { useState } from 'react';

import DataTable from '@/components/data-table';
import DateRangePicker from '@/components/date-range-picker';
import LocationDropdown from '@/components/location-dropdown';
import PrintPdfButton from '@/components/print-pdf-button';
import TablePagination from '@/components/table-pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { Pagination } from '@/lib/model';
import { cn, formatDate, toRupiah } from '@/lib/utils';
import dailySales from '@/routes/daily-sales';
import paymentRecaps from '@/routes/payment-recaps';
import type { BreadcrumbItem } from '@/types';

type Amounts = {
    sales_count: number;
    sales_amount: number;
    refund_count: number;
    refund_amount: number;
    recorded_amount: number;
    counted_amount: number;
    difference_amount: number;
};

type RecapRow = Amounts & {
    recap_id: number;
    date: string;
    location: string;
    shift: string;
    is_closed: boolean;
    employee: string;
    payment_method: string;
};

type Summary = {
    payments: (Amounts & { payment_method: string })[];
    totals: Amounts;
    recap_count: number;
    transaction_count: number;
};

type LocationOption = {
    id: number;
    name: string;
};

type Props = {
    pagination: Pagination<RecapRow>;
    summary: Summary;
    locationOptions: LocationOption[];
};

const title = 'Detail Rekapan';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: paymentRecaps.index().url,
    },
];

const toNumberArray = (value: unknown): number[] => {
    if (!value) {
        return [];
    }

    return (Array.isArray(value) ? value : String(value).split(',')).map(
        Number,
    );
};

const paymentIcon = (name: string): LucideIcon => {
    const lower = name.toLowerCase();

    if (lower.includes('tunai') || lower.includes('cash')) {
        return Banknote;
    }

    if (lower.includes('qris')) {
        return QrCode;
    }

    return CreditCard;
};

const columnHelper = createColumnHelper<RecapRow>();

const amount = (header: string, key: keyof Amounts) =>
    columnHelper.accessor(key, {
        header: () => <div className="text-right">{header}</div>,
        cell: (info) => (
            <div
                className={cn(
                    'text-right tabular-nums',
                    key === 'difference_amount' &&
                        info.getValue() < 0 &&
                        'text-destructive',
                )}
            >
                {key === 'sales_count'
                    ? info.getValue().toLocaleString('id-ID')
                    : toRupiah(info.getValue())}
            </div>
        ),
    });

const columns = [
    columnHelper.accessor('date', {
        header: 'Tanggal',
        cell: (info) => formatDate(info.getValue(), true),
    }),
    columnHelper.accessor('location', {
        header: 'Lokasi',
    }),
    columnHelper.accessor('shift', {
        header: 'Shift',
        cell: ({ row }) => (
            <div className="flex items-center gap-1">
                {row.original.shift}
                {!row.original.is_closed && (
                    <Badge variant="outline">Belum Tutup</Badge>
                )}
            </div>
        ),
    }),
    columnHelper.accessor('employee', {
        header: 'Ditutup Oleh',
    }),
    columnHelper.accessor('payment_method', {
        header: 'Metode Pembayaran',
    }),
    amount('Transaksi', 'sales_count'),
    amount('Penjualan', 'sales_amount'),
    amount('Pengembalian', 'refund_amount'),
    amount('Tercatat', 'recorded_amount'),
    amount('Dihitung', 'counted_amount'),
    amount('Selisih', 'difference_amount'),
    columnHelper.display({
        id: 'action',
        header: '',
        cell: ({ row }) => (
            <Button variant="outline" size="sm" asChild>
                <Link href={dailySales.show(row.original.recap_id).url}>
                    <Eye /> Detail
                </Link>
            </Button>
        ),
    }),
] as ColumnDef<RecapRow>[];

type StatCardProps = {
    label: string;
    value: string;
    description: string;
    icon: LucideIcon;
    negative?: boolean;
};

/** Jumlah card per baris di layar lebar (xl:grid-cols-5). */
const CARDS_PER_ROW = 5;

const percentOf = (value: number, total: number) =>
    (total ? (value / total) * 100 : 0).toLocaleString('id-ID', {
        maximumFractionDigits: 1,
    });

/**
 * Card ringkasan: total + satu per metode pembayaran. Bila belum memenuhi satu baris,
 * ditambah card Total Pengembalian lalu Total Selisih agar barisnya penuh.
 */
const summaryCards = (summary: Summary): StatCardProps[] => {
    const { totals } = summary;
    const count = (value: number) => value.toLocaleString('id-ID');

    const cards: StatCardProps[] = [
        {
            label: 'Total Pendapatan',
            value: toRupiah(totals.recorded_amount),
            description: `${count(summary.recap_count)} rekapan · ${count(summary.transaction_count)} transaksi`,
            icon: Wallet,
        },
        ...summary.payments.map((payment) => ({
            label: payment.payment_method,
            value: toRupiah(payment.recorded_amount),
            description: `${count(payment.sales_count)} transaksi · ${percentOf(payment.recorded_amount, totals.recorded_amount)}% dari total`,
            icon: paymentIcon(payment.payment_method),
        })),
    ];

    const extras: StatCardProps[] = [
        {
            label: 'Total Pengembalian',
            value: toRupiah(totals.refund_amount),
            description: `${count(totals.refund_count)} transaksi dikembalikan`,
            icon: RotateCcw,
        },
        {
            label: 'Total Selisih',
            value: toRupiah(totals.difference_amount),
            description: totals.difference_amount
                ? 'Uang dihitung dibanding tercatat'
                : 'Uang dihitung sesuai tercatat',
            icon: Scale,
            negative: totals.difference_amount < 0,
        },
    ];

    while (extras.length && cards.length % CARDS_PER_ROW !== 0) {
        cards.push(extras.shift()!);
    }

    return cards;
};

function StatCard({
    label,
    value,
    description,
    icon: Icon,
    negative,
}: StatCardProps) {
    return (
        <Card>
            <CardHeader className="flex-row items-center justify-between">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {label}
                </CardTitle>
                <div className="flex size-8 items-center justify-center rounded-lg bg-secondary">
                    <Icon className="size-4 text-muted-foreground" />
                </div>
            </CardHeader>
            <CardContent>
                <div
                    className={cn(
                        'text-xl font-bold whitespace-nowrap tabular-nums',
                        negative && 'text-destructive',
                    )}
                >
                    {value}
                </div>
                <p className="text-xs text-muted-foreground">{description}</p>
            </CardContent>
        </Card>
    );
}

export default function PaymentRecapIndex({
    pagination,
    summary,
    locationOptions,
}: Props) {
    const params = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    });

    const initialSelectAll = params.select_all_location !== '0';
    const initialLocs = toNumberArray(params.locs);
    const initialExcludeLocs = toNumberArray(params.exclude_locs);

    const [selectAllLocation, setSelectAllLocation] =
        useState<boolean>(initialSelectAll);
    const [locs, setLocs] = useState<number[]>(initialLocs);
    const [excludeLocs, setExcludeLocs] =
        useState<number[]>(initialExcludeLocs);

    const table = useReactTable({
        data: pagination.data,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    const cards = summaryCards(summary);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <div className="grid gap-4">
                <Form className="grid gap-2 lg:flex lg:justify-end">
                    <DateRangePicker
                        defaultStartDate={startOfMonth(new Date())}
                    />

                    <LocationDropdown
                        multiSelect
                        options={locationOptions}
                        defaultSelectAll={initialSelectAll}
                        defaultIds={initialLocs}
                        defaultExcludeIds={initialExcludeLocs}
                        handleSelectAllChange={setSelectAllLocation}
                        handleIdsChange={setLocs}
                        handleExcludeIdsChange={setExcludeLocs}
                    />
                    <input
                        type="hidden"
                        name="select_all_location"
                        value={selectAllLocation ? '1' : '0'}
                    />
                    {locs.map((id) => (
                        <input
                            key={id}
                            type="hidden"
                            name="locs[]"
                            value={id}
                        />
                    ))}
                    {excludeLocs.map((id) => (
                        <input
                            key={id}
                            type="hidden"
                            name="exclude_locs[]"
                            value={id}
                        />
                    ))}

                    <Button type="submit">
                        <Search /> Cari
                    </Button>
                    <PrintPdfButton url={paymentRecaps.pdf().url} />
                </Form>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    {cards.map((card) => (
                        <StatCard key={card.label} {...card} />
                    ))}
                </div>

                <Card>
                    <CardContent>
                        <DataTable columns={columns} table={table} />
                        <TablePagination pagination={pagination} />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
