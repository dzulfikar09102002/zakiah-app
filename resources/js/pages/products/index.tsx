import { Head, router, usePage } from '@inertiajs/react';
import { Plus, FileDown, FileUp, Pencil, SearchIcon, X } from 'lucide-react';
import { useState, useCallback, useEffect } from 'react';
import { debounce } from 'lodash';

import TablePagination from '@/components/table-pagination';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { Pagination, Product } from '@/lib/model';
import { toRupiah } from '@/lib/utils';
import products from '@/routes/products';
import type { BreadcrumbItem, SharedData } from '@/types';
import { ProductFormDialog } from '../../components/product-form-dialog';
import { toast } from 'sonner';
import { ProductImportButton } from '@/components/product-import-button';

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
};

const defaultCategoryOption: Option = {
    label: 'Semua Kategori',
    value: 'all',
};

export default ({
    categoryOptions: coptions,
    pagination,
    locationOptions,
    unitOptions,
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

    const query = useQuery();
    const search = query.search || '';
    const product_category_id = query.product_category_id || 'all';

    const [searchValue, setSearchValue] = useState(search);
    const [categoryValue, setCategoryValue] = useState(product_category_id);

    const startIndex = (pagination.current_page - 1) * pagination.per_page;

    const [selectedProduct, setSelectedProduct] = useState<Product>();

    const debouncedSearch = useCallback(
        debounce((value: string, category: any) => {
            router.get(
                products.index().url,
                {
                    search: value,
                    product_category_id: category,
                    page: 1,
                },
                {
                    preserveState: true,
                    replace: true,
                },
            );
        }, 500),
        [],
    );

    useEffect(() => {
        return () => {
            debouncedSearch.cancel();
        };
    }, [debouncedSearch]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <div className="mb-4 grid flex-wrap gap-2 lg:flex">
                <Button onClick={() => setIsModalOpen(true)}>
                    <Plus /> Produk Baru
                </Button>

                {/* <Button variant="outline"><FileDown /> Export</Button> */}
                {/* <Button variant="outline"><FileUp /> Import</Button> */}
                <ProductImportButton locations={locationOptions} onSuccess={() => console.log('berhasil')} categories={categoryOptions.slice(1)} units={unitOptions} />
            </div>

            <Card>
                <CardHeader>
                    <div className="grid gap-2 lg:flex">
                        <Combobox
                            items={categoryOptions}
                            value={categoryOptions.find(
                                (el) => el.value == categoryValue,
                            )}
                            onValueChange={(val: Option | null) => {
                                const newValue = val?.value ?? 'all';

                                setCategoryValue(newValue);
                                router.get(
                                    products.index().url,
                                    {
                                        search: searchValue,
                                        product_category_id: newValue,
                                        page: 1,
                                    },
                                    {
                                        preserveState: true,
                                        replace: true,
                                    },
                                );
                            }}
                        >
                            <ComboboxInput
                                placeholder="Pilih Kategori"
                                className="w-full"
                            />
                            <ComboboxContent>
                                <ComboboxEmpty>Tidak ditemukan</ComboboxEmpty>
                                <ComboboxList>
                                    {(el) => (
                                        <ComboboxItem key={el.value} value={el}>
                                            {el.label}
                                        </ComboboxItem>
                                    )}
                                </ComboboxList>
                            </ComboboxContent>
                        </Combobox>
                        <Input
                            placeholder="Cari..."
                            value={searchValue}
                            onChange={(e) => {
                                const value = e.target.value;
                                setSearchValue(value);
                                debouncedSearch(value, categoryValue);
                            }}
                        />

                        <Button
                            variant="secondary"
                            onClick={() =>
                                debouncedSearch(searchValue, categoryValue)
                            }
                        >
                            <SearchIcon /> Cari
                        </Button>
                    </div>
                </CardHeader>

                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>No.</TableHead>
                                <TableHead>Nama</TableHead>
                                <TableHead>Kategori</TableHead>
                                <TableHead>SKU</TableHead>
                                <TableHead>Barcode</TableHead>
                                <TableHead>Harga Beli</TableHead>
                                <TableHead>Harga Jual</TableHead>
                                <TableHead>Stok</TableHead>
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {pagination.data.map((product, idx) => (
                                <TableRow key={product.sku}>
                                    <TableCell>
                                        {startIndex + idx + 1}.
                                    </TableCell>
                                    <TableCell>{product.name}</TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {product.product_category?.name}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{product.sku}</TableCell>
                                    <TableCell>{product.barcode}</TableCell>
                                    <TableCell>
                                        {toRupiah(product.last_buying_price)}
                                    </TableCell>
                                    <TableCell>
                                        {toRupiah(product.sell_price)}
                                    </TableCell>
                                    <TableCell>{product.total_stock}</TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                onClick={() => {
                                                    setSelectedProduct(product);
                                                    setIsModalOpen(true);
                                                }}
                                            >
                                                <Pencil />
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="icon"
                                            >
                                                <X />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}

                            {!pagination.data.length && (
                                <TableRow>
                                    <TableCell
                                        colSpan={9}
                                        className="py-2 text-center text-muted-foreground"
                                    >
                                        Data tidak ditemukan
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>

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
            />
        </AppLayout>
    );
};
