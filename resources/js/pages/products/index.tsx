import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Plus, Pencil, Search, X, Upload } from 'lucide-react';
import { useState, useEffect } from 'react';
import {
    createColumnHelper,
    getCoreRowModel,
    useReactTable,
    type ColumnDef,
} from '@tanstack/react-table';

import TablePagination from '@/components/table-pagination';
import DataTable from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import {
    Combobox,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList,
} from '@/components/ui/combobox';
import { Input } from '@/components/ui/input';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { Pagination, Product } from '@/lib/model';
import { toRupiah } from '@/lib/utils';
import products from '@/routes/products';
import type { BreadcrumbItem, SharedData } from '@/types';
import { ProductFormDialog } from '../../components/product-form-dialog';
import { toast } from 'sonner';

const title = 'Kelola Produk';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: products.index().url,
    },
];

type Option = {
    label: string;
    value: any;
};

type Props = {
    categoryOptions: Option[];
    locationOptions: Option[];
    unitOptions: Option[];
    pagination: Pagination<Product>;
    suppliers: string[];
};

const defaultCategoryOption: Option = {
    label: 'Semua Kategori',
    value: 'all',
};

const columnHelper = createColumnHelper<Product>();

export default ({
    categoryOptions: coptions,
    pagination,
    locationOptions,
    unitOptions,
    suppliers,
}: Props) => {
    const flash = usePage<SharedData>().flash as {
        success?: string;
        error?: string;
    };

    useEffect(() => {
        if (flash.success) {
            toast.success(flash.success);
        }
        if (flash.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    const categoryOptions = [defaultCategoryOption, ...coptions];

    const [isModalOpen, setIsModalOpen] = useState(false);
    const [selectedProduct, setSelectedProduct] = useState<Product>();

    const query = useQuery();
    const search = query.search || '';
    const product_category_id = query.product_category_id || 'all';

    const [categoryValue, setCategoryValue] = useState(product_category_id);

    const startIndex = (pagination.current_page - 1) * pagination.per_page;

    const columns = [
        columnHelper.display({
            id: 'no',
            header: 'No.',
            cell: (info) => startIndex + info.row.index + 1 + '.',
        }),
        columnHelper.accessor('name', {
            header: 'Nama',
            cell: (info) => info.getValue(),
        }),
        columnHelper.accessor((row) => row.product_category?.name, {
            id: 'category',
            header: 'Kategori',
            cell: (info) => (
                <Badge variant="secondary">{info.getValue()}</Badge>
            ),
        }),
        columnHelper.accessor('sku', {
            header: 'SKU',
            cell: (info) => info.getValue(),
        }),
        columnHelper.accessor('barcode', {
            header: 'Barcode',
            cell: (info) => info.getValue(),
        }),
        columnHelper.accessor('last_buying_price', {
            header: 'Harga Beli',
            cell: (info) => toRupiah(info.getValue()),
        }),
        columnHelper.accessor('sell_price', {
            header: 'Harga Jual',
            cell: (info) => toRupiah(info.getValue()),
        }),
        columnHelper.accessor('total_stock', {
            header: 'Stok',
            cell: (info) => info.getValue(),
        }),
        columnHelper.accessor((row) => row.supplier?.name, {
            id: 'supplier',
            header: 'Supplier',
            cell: (info) => info.getValue() ?? '-',
        }),
        columnHelper.accessor('updated_at', {
            header: 'Update',
            cell: (info) => {
                const val = info.getValue();
                return val
                    ? new Intl.DateTimeFormat('id-ID', {
                          day: '2-digit',
                          month: 'long',
                          year: 'numeric',
                          hour: '2-digit',
                          minute: '2-digit',
                      }).format(new Date(val))
                    : '-';
            },
        }),
        columnHelper.display({
            id: 'actions',
            header: 'Aksi',
            cell: (info) => (
                <div className="flex gap-2">
                    <Button
                        variant="outline"
                        size="icon"
                        onClick={() => {
                            setSelectedProduct(info.row.original);
                            setIsModalOpen(true);
                        }}
                    >
                        <Pencil />
                    </Button>
                    <Button variant="destructive" size="icon">
                        <X />
                    </Button>
                </div>
            ),
        }),
    ] as ColumnDef<Product>[];

    const table = useReactTable({
        data: pagination.data,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <div className="mb-4 grid flex-wrap gap-2 lg:flex">
                <Button onClick={() => setIsModalOpen(true)}>
                    <Plus /> Produk Baru
                </Button>
                <Link href="/products/import-page">
                    <Button variant="outline" type="button">
                        <Upload /> Impor Produk
                    </Button>
                </Link>
            </div>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="p-0 lg:px-6">
                    <Form method="GET" action={products.index().url}>
                        <div className="grid gap-2 lg:flex">
                            <input type="hidden" name="page" value={1} />
                            <input
                                type="hidden"
                                name="product_category_id"
                                value={categoryValue}
                            />
                            <Combobox
                                items={categoryOptions}
                                value={categoryOptions.find(
                                    (el) => el.value == categoryValue,
                                )}
                                onValueChange={(val: Option | null) => {
                                    const newValue = val?.value ?? 'all';
                                    setCategoryValue(newValue);
                                }}
                            >
                                <ComboboxInput
                                    placeholder="Pilih Kategori"
                                    className="w-full lg:w-[250px]"
                                />
                                <ComboboxContent>
                                    <ComboboxEmpty>
                                        Tidak ditemukan
                                    </ComboboxEmpty>
                                    <ComboboxList>
                                        {(el) => (
                                            <ComboboxItem
                                                key={el.value}
                                                value={el}
                                                className="cursor-pointer"
                                            >
                                                {el.label}
                                            </ComboboxItem>
                                        )}
                                    </ComboboxList>
                                </ComboboxContent>
                            </Combobox>
                            <Input
                                placeholder="Cari..."
                                name="search"
                                defaultValue={search}
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

            <ProductFormDialog
                locations={locationOptions}
                units={unitOptions}
                onOpenChange={(open) => {
                    setSelectedProduct(undefined);
                    setIsModalOpen(open);
                }}
                product={selectedProduct}
                open={isModalOpen}
                categories={coptions}
                suppliers={suppliers}
            />
        </AppLayout>
    );
};
