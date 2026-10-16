import { Form, Head } from '@inertiajs/react';
import {
    createColumnHelper,
    getCoreRowModel,
    useReactTable,
    type ColumnDef,
} from '@tanstack/react-table';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';
import { Search } from 'lucide-react';
import QueryString from 'qs';
import { useState } from 'react';
import DataTable from '@/components/data-table';
import DateRangePicker from '@/components/date-range-picker';
import LocationDropdown from '@/components/location-dropdown';
import PrintPdfButton from '@/components/print-pdf-button';
import Select from '@/components/select';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { TopCustomer } from '@/lib/model';
import { toRupiah } from '@/lib/utils';
import reportTopCustomers from '@/routes/report-top-customers';
import type { BreadcrumbItem } from '@/types';

const columnHelper = createColumnHelper<TopCustomer>();

const columns: ColumnDef<TopCustomer>[] = [
    columnHelper.accessor('rank', {
        header: '#',
    }),

    columnHelper.accessor('customer_name', {
        header: 'Pelanggan',
    }),

    columnHelper.accessor('phone_number', {
        header: 'No. HP',
    }),

    columnHelper.accessor('category', {
        header: 'Kategori',
    }),

    columnHelper.accessor('transaction_count', {
        header: 'Jml Transaksi',
    }),

    columnHelper.accessor('total_spend', {
        header: 'Total Belanja',
        cell: (info) => toRupiah(info.getValue()),
    }),

    columnHelper.accessor('average_spend', {
        header: 'Rata-rata/Transaksi',
        cell: (info) => toRupiah(info.getValue()),
    }),

    columnHelper.accessor('last_sales_at', {
        header: 'Terakhir Belanja',
        cell: (info) => {
            const value = info.getValue();

            return value
                ? format(new Date(value), 'dd MMM yyyy HH:mm', { locale: id })
                : '-';
        },
    }),
] as ColumnDef<TopCustomer>[];

const title = 'Top Pelanggan';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: reportTopCustomers.index().url,
    },
];

type Option = {
    value: string;
    label: string;
};

type Props = {
    data: TopCustomer[];
    locationOptions: Option[];
    topOptions: number[];
    top: number;
};

const parseToNumberArray = (val: unknown): number[] => {
    if (!val) return [];

    if (Array.isArray(val)) {
        return val.map(Number);
    }

    return String(val).split(',').map(Number);
};

export default ({ data, locationOptions, topOptions, top }: Props) => {
    const params = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    });

    const initialSelectAll = params.select_all_location !== '0';
    const initialLocs = parseToNumberArray(params.locs);
    const initialExcludeLocs = parseToNumberArray(params.exclude_locs);

    const [selectAllLocation, setSelectAllLocation] =
        useState<boolean>(initialSelectAll);
    const [locs, setLocs] = useState<number[]>(initialLocs);
    const [excludeLocs, setExcludeLocs] =
        useState<number[]>(initialExcludeLocs);

    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    const totalSpend = data.reduce((sum, row) => sum + row.total_spend, 0);
    const totalTransactions = data.reduce(
        (sum, row) => sum + row.transaction_count,
        0,
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <Card>
                <CardContent>
                    <Form className="mb-4 grid gap-2 lg:flex lg:justify-end">
                        <div className="grid gap-2 lg:flex">
                            {/* TOP N */}
                            <Select
                                name="top"
                                defaultValue={String(top)}
                                options={topOptions.map((n) => ({
                                    label: `Top ${n}`,
                                    value: String(n),
                                }))}
                                placeholder="Jumlah"
                            />

                            {/* DATE */}
                            <DateRangePicker />

                            {/* LOCATION */}
                            <LocationDropdown
                                multiSelect
                                options={locationOptions.map((l) => ({
                                    id: Number(l.value),
                                    name: l.label,
                                }))}
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
                            {locs.map((locationId, i) => (
                                <input
                                    key={i}
                                    type="hidden"
                                    name="locs[]"
                                    value={locationId}
                                />
                            ))}

                            {excludeLocs.map((locationId, i) => (
                                <input
                                    key={i}
                                    type="hidden"
                                    name="exclude_locs[]"
                                    value={locationId}
                                />
                            ))}
                            <Button type="submit">
                                <Search /> Cari
                            </Button>
                            <PrintPdfButton
                                url={reportTopCustomers.pdf().url}
                                disabled={data.length === 0}
                            />
                        </div>
                    </Form>
                    <DataTable columns={columns} table={table} />
                    {data.length > 0 && (
                        <div className="mt-4 flex flex-wrap justify-end gap-6 text-sm">
                            <div>
                                <span className="text-muted-foreground">
                                    Total Transaksi:{' '}
                                </span>
                                <span className="font-semibold">
                                    {totalTransactions}
                                </span>
                            </div>
                            <div>
                                <span className="text-muted-foreground">
                                    Total Belanja:{' '}
                                </span>
                                <span className="font-semibold">
                                    {toRupiah(totalSpend)}
                                </span>
                            </div>
                        </div>
                    )}
                </CardContent>
            </Card>
        </AppLayout>
    );
};
