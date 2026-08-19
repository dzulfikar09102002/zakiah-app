import { Form, Head } from '@inertiajs/react';
import { Search, Layers, Package, Wallet, TrendingUp } from 'lucide-react';
import { useEffect, useState } from 'react';
import QueryString from 'qs';
import {
    createColumnHelper,
    getCoreRowModel,
    useReactTable,
    type ColumnDef,
} from '@tanstack/react-table';

import TablePagination from '@/components/table-pagination';
import DataTable from '@/components/data-table';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import LocationDropdown from '@/components/location-dropdown';
import AppLayout from '@/layouts/app-layout';
import type { Pagination } from '@/lib/model';
import { cn, toRupiah } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';

export type CategoryAsset = {
    id: number;
    name: string;
    total_products: number;
    total_stock: number;
    total_buying_asset: number;
    total_selling_asset: number;
};

export type AssetSummary = {
    total_categories: number;
    total_products: number;
    grand_total_stock: number;
    grand_buying_asset: number;
    grand_selling_asset: number;
};

type Option = {
    label: string;
    value: string | number;
};

type Props = {
    pagination: Pagination<CategoryAsset>;
    locationOptions: Option[];
    summary?: AssetSummary;
};

type Params = {
    search: string;
    locs: string[];
    exclude_locs: string[];
    select_all_location: string;
};

const title = 'Nilai Aset per Kategori';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Laporan',
        href: '#',
    },
    {
        title,
        href: '/reports/assets-by-category',
    },
];

const columnHelper = createColumnHelper<CategoryAsset>();

// Reusable summary stat card. Keeps every card structurally identical —
// only the icon color and content change — so the row stays consistent.
type SummaryCardProps = {
    icon: React.ElementType;
    iconClassName?: string;
    label: string;
    value: string;
    valueSuffix?: string;
    valueClassName?: string;
    description: string;
};

const SummaryCard = ({
    icon: Icon,
    iconClassName,
    label,
    value,
    valueSuffix,
    valueClassName,
    description,
}: SummaryCardProps) => (
    <Card>
        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">
                {label}
            </CardTitle>
            <div
                className={cn(
                    'flex size-8 items-center justify-center rounded-md bg-muted',
                    iconClassName,
                )}
            >
                <Icon className="size-4" />
            </div>
        </CardHeader>
        <CardContent>
            <div className={cn('text-2xl font-bold', valueClassName)}>
                {value}
                {valueSuffix && (
                    <span className="ml-1 text-sm font-normal text-muted-foreground">
                        {valueSuffix}
                    </span>
                )}
            </div>
            <CardDescription className="mt-1">{description}</CardDescription>
        </CardContent>
    </Card>
);

export default ({ pagination, locationOptions, summary }: Props) => {
    const query = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    }) as Partial<Params>;

    const parseToNumberArray = (val: any): number[] => {
        if (!val) return [];
        if (Array.isArray(val)) {
            return val.map(Number);
        }
        return String(val).split(',').map(Number);
    };

    const initialSelectAll = query.select_all_location !== '0';
    const initialLocs = parseToNumberArray(query.locs);
    const initialExcludeLocs = parseToNumberArray(query.exclude_locs);

    const [selectAllLocation, setSelectAllLocation] =
        useState<boolean>(initialSelectAll);
    const [locs, setLocs] = useState<number[]>(initialLocs);
    const [excludeLocs, setExcludeLocs] =
        useState<number[]>(initialExcludeLocs);

    useEffect(() => {
        const params = QueryString.parse(window.location.search, {
            ignoreQueryPrefix: true,
        }) as Partial<Params>;

        if (params.select_all_location === '0') {
            setSelectAllLocation(false);
        } else {
            setSelectAllLocation(true);
        }

        if (params.locs) {
            setLocs(parseToNumberArray(params.locs));
        }

        if (params.exclude_locs) {
            setExcludeLocs(parseToNumberArray(params.exclude_locs));
        }
    }, [window.location.search]);

    const startIndex = (pagination.current_page - 1) * pagination.per_page;

    const columns = [
        columnHelper.display({
            id: 'no',
            header: () => <div className="text-center">No.</div>,
            cell: (info) => (
                <div className="text-center text-muted-foreground">
                    {startIndex + info.row.index + 1}.
                </div>
            ),
        }),
        columnHelper.accessor('name', {
            header: 'Nama Kategori',
            cell: (info) => (
                <span className="font-semibold tracking-tight">
                    {info.getValue()}
                </span>
            ),
        }),
        columnHelper.accessor('total_products', {
            header: () => <div className="text-center">Varian Produk</div>,
            cell: (info) => (
                <div className="text-center font-medium">
                    {Number(info.getValue()).toLocaleString('id-ID')}
                </div>
            ),
        }),
        columnHelper.accessor('total_stock', {
            header: () => <div className="text-center">Total Stok Unit</div>,
            cell: (info) => {
                const stock = Number(info.getValue());
                return (
                    <div
                        className={`text-center font-medium ${stock < 0 ? 'font-bold text-destructive' : ''}`}
                    >
                        {stock.toLocaleString('id-ID')}
                    </div>
                );
            },
        }),
        columnHelper.accessor('total_buying_asset', {
            header: () => (
                <div className="text-right">Valuasi Aset (Harga Beli)</div>
            ),
            cell: (info) => (
                <div className="text-right font-semibold text-blue-600 dark:text-blue-400">
                    {toRupiah(Number(info.getValue()))}
                </div>
            ),
        }),
        columnHelper.accessor('total_selling_asset', {
            header: () => <div className="text-right">Estimasi Nilai Jual</div>,
            cell: (info) => (
                <div className="text-right font-semibold text-emerald-600 dark:text-emerald-400">
                    {toRupiah(Number(info.getValue()))}
                </div>
            ),
        }),
    ] as ColumnDef<CategoryAsset>[];

    const table = useReactTable({
        data: pagination.data,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    const totalCategories = summary?.total_categories ?? pagination.total;
    const totalStock =
        summary?.grand_total_stock ??
        pagination.data.reduce((acc, cur) => acc + Number(cur.total_stock), 0);
    const buyingAsset =
        summary?.grand_buying_asset ??
        pagination.data.reduce(
            (acc, cur) => acc + Number(cur.total_buying_asset),
            0,
        );
    const sellingAsset =
        summary?.grand_selling_asset ??
        pagination.data.reduce(
            (acc, cur) => acc + Number(cur.total_selling_asset),
            0,
        );
    const potentialMargin = sellingAsset - buyingAsset;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <SummaryCard
                    icon={Layers}
                    label="Kategori & Produk"
                    value={Number(totalCategories).toLocaleString('id-ID')}
                    valueSuffix="Kategori"
                    description={`Total ${Number(summary?.total_products ?? 0).toLocaleString('id-ID')} produk terdaftar`}
                />

                <SummaryCard
                    icon={Package}
                    label="Total Stok Fisik"
                    value={Number(totalStock).toLocaleString('id-ID')}
                    valueSuffix="Unit"
                    description="Akumulasi filter lokasi aktif"
                />

                <SummaryCard
                    icon={Wallet}
                    iconClassName="bg-blue-500/10 text-blue-600 dark:text-blue-400"
                    label="Total Valuasi Aset"
                    value={toRupiah(Number(buyingAsset))}
                    valueClassName="text-blue-600 dark:text-blue-400"
                    description="Berdasarkan harga beli terakhir"
                />

                <SummaryCard
                    icon={TrendingUp}
                    iconClassName="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                    label="Estimasi Nilai Jual"
                    value={toRupiah(Number(sellingAsset))}
                    valueClassName="text-emerald-600 dark:text-emerald-400"
                    description={`+ Potensi Margin: ${toRupiah(Number(potentialMargin))}`}
                />
            </div>

            {/* Table Area */}
            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="p-0 lg:px-6">
                    <Form method="GET" action="/assets-by-category">
                        <div className="grid gap-2 lg:flex">
                            <input type="hidden" name="page" value={1} />
                            <LocationDropdown
                                key={`loc-dropdown-${selectAllLocation}-${locs.join(',')}-${excludeLocs.join(',')}`}
                                multiSelect
                                options={locationOptions.map((l) => ({
                                    id: Number(l.value),
                                    name: l.label,
                                }))}
                                defaultSelectAll={selectAllLocation}
                                defaultIds={locs}
                                defaultExcludeIds={excludeLocs}
                                handleSelectAllChange={setSelectAllLocation}
                                handleIdsChange={setLocs}
                                handleExcludeIdsChange={setExcludeLocs}
                            />
                            <input
                                type="hidden"
                                name="select_all_location"
                                value={selectAllLocation ? '1' : '0'}
                            />
                            {locs.map((id, i) => (
                                <input
                                    key={`loc-${i}`}
                                    type="hidden"
                                    name="locs[]"
                                    value={id}
                                />
                            ))}
                            {excludeLocs.map((id, i) => (
                                <input
                                    key={`ex-loc-${i}`}
                                    type="hidden"
                                    name="exclude_locs[]"
                                    value={id}
                                />
                            ))}

                            <Input
                                placeholder="Cari kategori..."
                                name="search"
                                defaultValue={query.search || ''}
                            />

                            <Button variant="secondary" type="submit">
                                <Search /> Cari
                            </Button>
                        </div>
                    </Form>
                </CardHeader>

                <CardContent className="mt-4 border-t p-0 lg:border-0 lg:px-6">
                    <DataTable columns={columns} table={table} />
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
};
