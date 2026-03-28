import { Form, Head, router } from "@inertiajs/react";
import {
    Plus,
    FileDown,
    FileUp,
    Pencil,
    SearchIcon,
    X,
} from "lucide-react";
import { useState, useCallback, useEffect } from "react";
import { debounce } from "lodash";

import TablePagination from "@/components/table-pagination";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardHeader
} from "@/components/ui/card";
import {
    Combobox,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList
} from "@/components/ui/combobox";
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from "@/components/ui/dialog";
import {
    Field,
    FieldGroup,
    FieldLabel,
    FieldSet
} from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { useQuery } from "@/hooks/use-query";
import AppLayout from "@/layouts/app-layout"
import type { Pagination, Product } from "@/lib/model";
import { toRupiah } from "@/lib/utils";
import products from "@/routes/products"
import type { BreadcrumbItem } from "@/types"

const title = 'Kelola Produk'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: products.index().url
    }
]

type Option = {
    label: string
    value: any
}

type Props = {
    categoryOptions: Option[]
    pagination: Pagination<Product>
}

export default ({ categoryOptions, pagination }: Props) => {

    const [isModalOpen, setIsModalOpen] = useState(false);

    const query = useQuery()
    const search = query.search || ''
    const product_category_id = query.product_category_id || 'all'

    const [searchValue, setSearchValue] = useState(search)
    const [categoryValue, setCategoryValue] = useState(product_category_id)

    const startIndex = (pagination.current_page - 1) * pagination.per_page

    const debouncedSearch = useCallback(
        debounce((value: string, category: any) => {
            router.get(products.index().url, {
                search: value,
                product_category_id: category,
                page: 1
            }, {
                preserveState: true,
                replace: true
            })
        }, 500),
        []
    )

    useEffect(() => {
        return () => {
            debouncedSearch.cancel()
        }
    }, [debouncedSearch])

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <div className="grid lg:flex gap-2 flex-wrap mb-4">
                <Button onClick={() => setIsModalOpen(true)}>
                    <Plus /> Produk Baru
                </Button>

                <Button variant="outline"><FileDown /> Export</Button>
                <Button variant="outline"><FileUp /> Import</Button>
            </div>

            <Card>
                <CardHeader>
                    <div className="grid lg:flex gap-2">
                        <Combobox
                            items={categoryOptions}
                            value={categoryOptions.find(el => el.value == categoryValue)}
                            onValueChange={(val: Option | null) => {
                                const newValue = val?.value ?? 'all'

                                setCategoryValue(newValue)
                                router.get(products.index().url, {
                                    search: searchValue,
                                    product_category_id: newValue,
                                    page: 1
                                }, {
                                    preserveState: true,
                                    replace: true
                                })
                            }}
                        >
                            <ComboboxInput placeholder="Pilih Kategori" className="w-full" />
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
                                const value = e.target.value
                                setSearchValue(value)
                                debouncedSearch(value, categoryValue)
                            }}
                        />

                        <Button
                            variant="secondary"
                            onClick={() => debouncedSearch(searchValue, categoryValue)}
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
                                    <TableCell>{startIndex + idx + 1}.</TableCell>
                                    <TableCell>{product.name}</TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {product.product_category?.name}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{product.sku}</TableCell>
                                    <TableCell>{product.barcode}</TableCell>
                                    <TableCell>{toRupiah(product.last_buying_price)}</TableCell>
                                    <TableCell>{toRupiah(product.sell_price)}</TableCell>
                                    <TableCell>{product.total_stock}</TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            <Button variant="outline" size="icon">
                                                <Pencil />
                                            </Button>
                                            <Button variant="destructive" size="icon">
                                                <X />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}

                            {!pagination.data.length && (
                                <TableRow>
                                    <TableCell colSpan={9} className="text-center py-2 text-muted-foreground">
                                        Data tidak ditemukan
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>

                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>

            <Dialog open={isModalOpen} onOpenChange={() => setIsModalOpen(false)}>
                <Form>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Produk Baru</DialogTitle>
                        </DialogHeader>

                        <DialogFooter>
                            <DialogClose asChild>
                                <Button variant="outline">Batal</Button>
                            </DialogClose>
                            <Button type="submit">Simpan</Button>
                        </DialogFooter>
                    </DialogContent>
                </Form>
            </Dialog>

        </AppLayout>
    )
}