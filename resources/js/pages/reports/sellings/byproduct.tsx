import { Form, Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import sellings from '@/routes/sellings';
import { Card, CardContent } from '@/components/ui/card';
import { capitalize, toRupiah } from '@/lib/utils';
import {
    createColumnHelper,
    getCoreRowModel,
    useReactTable,
    VisibilityState,
    type ColumnDef,
} from '@tanstack/react-table';
import { useEffect, useState } from 'react';
import ColumnVisibilityDropdown from '@/components/column-visibility-dropdown';
import DataTable from '@/components/data-table';
import TablePagination from '@/components/table-pagination';
import { Pagination } from '@/lib/model';
import DateRangePicker from '@/components/date-range-picker';
import { Button } from '@/components/ui/button';
import { Search } from 'lucide-react';
import Select from '@/components/select';
import MultiSelect from '@/components/multi-select';
import QueryString from 'qs';
import LocationDropdown from '@/components/location-dropdown';

type ProductSalesData = {
    product_name: string;
    product_sku: string;
    category: string;
    description: string;
    quantity: number;
    sell_price: number;
    cost_of_goods_sold: number;
    gross_sales: number;
    discount: number;
    total: number;
    profit: number;
};

const columnHelper = createColumnHelper<ProductSalesData>();

export const columns = [
    columnHelper.accessor('product_name', {
        header: 'Produk',
    }),

    columnHelper.accessor('product_sku', {
        header: 'SKU Produk',
    }),

    columnHelper.accessor('category', {
        header: 'Kategori Produk',
    }),

    columnHelper.accessor('quantity', {
        header: 'Qty',
    }),

    columnHelper.accessor('sell_price', {
        header: 'Harga Jual',
        cell: (info) => toRupiah(info.getValue()),
    }),

    columnHelper.accessor('cost_of_goods_sold', {
        header: 'Harga Beli',
        cell: (info) => toRupiah(info.getValue()),
    }),

    columnHelper.accessor('gross_sales', {
        header: 'Penjualan Kotor',
        cell: (info) => toRupiah(info.getValue()),
    }),

    columnHelper.accessor('discount', {
        header: 'Total Diskon',
        cell: (info) => toRupiah(info.getValue()),
    }),

    columnHelper.accessor('total', {
        header: 'Penjualan Bersih',
        cell: (info) => toRupiah(info.getValue()),
    }),

    columnHelper.accessor('profit', {
        header: 'Laba',
        cell: (info) => toRupiah(info.getValue()),
    }),
] as ColumnDef<ProductSalesData>[];

const title = 'Penjualan Per Produk';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: sellings.summary().url,
    },
];

const defaultColumn = {
    cashier: false,
    sales: false,
};

const cachedColumnKey = 'salesSummaryColumnVisibility';
const cachedColumn = JSON.parse(
    localStorage.getItem(cachedColumnKey) || JSON.stringify(defaultColumn),
);

type Option = {
    value: string;
    label: string;
};

const discountOption: Option[] = [
    {
        label: 'Semua Diskon',
        value: 'all',
    },
    {
        label: 'Dengan Diskon',
        value: 'available',
    },
    {
        label: 'Tanpa Diskon',
        value: 'none',
    },
];

type Props = {
    pagination: Pagination<ProductSalesData>;
    locationOptions: Option[];
};

type Params = {
    discount: string;
    locations: string[];
    start_at: string;
    end_at: string;
};

export default ({ pagination, locationOptions }: Props) => {
    const { data } = pagination;

    const query = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    }) as Partial<Params>;

    const [columnVisibility, setColumnVisibility] =
        useState<VisibilityState>(cachedColumn);
    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        onColumnVisibilityChange: setColumnVisibility,
        state: {
            columnVisibility,
        },
    });

    const multiSelectPlaceholder = (values: string[]) => {
        if (values.length === 0) {
            return 'Pilih lokasi';
        } else if (values.length >= locationOptions.length) {
            return 'Semua lokasi';
        } else {
            return `${values.length} lokasi`;
        }
    };

    useEffect(() => {
        localStorage.setItem(cachedColumnKey, JSON.stringify(columnVisibility));
    }, [columnVisibility]);
    useEffect(() => {
        const params = QueryString.parse(window.location.search, {
            ignoreQueryPrefix: true,
        });

        if (params.select_all_location === '0') {
            setSelectAllLocation(false);
        }

        if (params.locs) {
            const arr = Array.isArray(params.locs)
                ? params.locs
                : String(params.locs).split(',');

            setLocs(arr.map(Number));
        }

        if (params.exclude_locs) {
            const arr = Array.isArray(params.exclude_locs)
                ? params.exclude_locs
                : String(params.exclude_locs).split(',');

            setExcludeLocs(arr.map(Number));
        }
    }, []);
    const params = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    });

    // helper biar aman
    const parseToNumberArray = (val: any): number[] => {
        if (!val) return [];

        if (Array.isArray(val)) {
            return val.map(Number);
        }

        return String(val).split(',').map(Number);
    };

    const initialSelectAll = params.select_all_location !== '0';
    const initialLocs = parseToNumberArray(params.locs);
    const initialExcludeLocs = parseToNumberArray(params.exclude_locs);

    const [selectAllLocation, setSelectAllLocation] =
        useState<boolean>(initialSelectAll);
    const [locs, setLocs] = useState<number[]>(initialLocs);
    const [excludeLocs, setExcludeLocs] =
        useState<number[]>(initialExcludeLocs);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <Card>
                <CardContent>
                    <Form className="mb-4 grid gap-2 lg:flex lg:justify-between">
                        <ColumnVisibilityDropdown table={table} />

                        <div className="grid gap-2 lg:flex">
                            {/* DATE */}
                            <DateRangePicker />

                            {/* DISCOUNT */}
                            <Select
                                name="discount"
                                defaultValue={query.discount || 'all'}
                                options={discountOption}
                                placeholder="Pilih diskon"
                            />

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
                            {locs.map((id, i) => (
                                <input
                                    key={i}
                                    type="hidden"
                                    name="locs[]"
                                    value={id}
                                />
                            ))}

                            {excludeLocs.map((id, i) => (
                                <input
                                    key={i}
                                    type="hidden"
                                    name="exclude_locs[]"
                                    value={id}
                                />
                            ))}
                            <Button type="submit">
                                <Search /> Cari
                            </Button>
                        </div>
                    </Form>
                    <DataTable columns={columns} table={table} />
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
};
