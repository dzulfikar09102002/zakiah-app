import { Form, Head } from "@inertiajs/react";
import {
    Plus,
    FileDown,
    FileUp,
    Pencil,
    SearchIcon,
    X,
} from "lucide-react";
import { useState } from "react";
import TablePagination from "@/components/table-pagination";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { ButtonGroup } from "@/components/ui/button-group";
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
import { Label } from "@/components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
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

const stockData = [
    { lokasi: "STORE PANDAAN", sekarang: 0, stok: 0 },
    { lokasi: "STORE JOMBANG", sekarang: 0, stok: 0 },
    { lokasi: "STORE MOJOKERTO", sekarang: 0, stok: 0 },
    { lokasi: "STORE MOJOSARI", sekarang: 0, stok: 0 },
    { lokasi: "STORE PORONG", sekarang: 0, stok: 0 },
    { lokasi: "ZAKIAH OFFICE", sekarang: 0, stok: 0 },
    { lokasi: "GUDANG", sekarang: 0, stok: 0 },
    { lokasi: "Zakiah Tulangan", sekarang: 0, stok: 0 },
];

type Option = {
    label: string
    value: unknown
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
    const startIndex = (pagination.current_page - 1) * pagination.per_page

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <ButtonGroup className="mb-4 justify-between w-full">
                <ButtonGroup>
                    <Button className="size-9 lg:size-auto" onClick={() => setIsModalOpen(true)}>
                        <Plus /> <span className="hidden lg:inline">Produk Baru</span>
                    </Button>
                </ButtonGroup>
                <ButtonGroup>
                    <Button variant="outline"><FileDown /> Export</Button>
                    <Button variant="outline"><FileUp /> Import</Button>
                </ButtonGroup>
            </ButtonGroup>

            {/* Table Section */}
            <Card className="bg-background lg:bg-card p-0 lg:py-6 border-0 lg:border">
                <CardHeader className="p-0 lg:px-6">
                    <Form method="GET" className="grid lg:flex gap-2">
                        <Combobox
                            items={categoryOptions}
                            name="product_category_id"
                            defaultValue={categoryOptions.find(el => el.value == product_category_id)}
                        >
                            <ComboboxInput placeholder="Pilih Kategori" className={'w-full'} />
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
                        <Input placeholder="Cari..." name="search" defaultValue={search} />
                        <input type="hidden" name="page" value={1} />
                        <Button variant={"secondary"}><SearchIcon /> Cari</Button>
                    </Form>
                </CardHeader>
                <CardContent className="p-0 lg:px-6 border-t lg:border-0">
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
                                    <TableCell><Badge variant={"secondary"}>{product.product_category?.name}</Badge></TableCell>
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
            {/* <ProductInfiniteList /> */}
            <Dialog open={isModalOpen} onOpenChange={() => setIsModalOpen(false)}>
                <Form>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Produk Baru</DialogTitle>
                        </DialogHeader>
                        <div className="max-h-[50vh] overflow-y-auto">
                            <FieldSet>
                                <FieldGroup className="grid gap-y-4">
                                    <Field>
                                        <FieldLabel>Nama</FieldLabel>
                                        <Input name="nama" placeholder="Masukkan nama" />
                                    </Field>

                                    <Field>
                                        <FieldLabel>Deskripsi</FieldLabel>
                                        <Input name="deskripsi" placeholder="Masukkan deskripsi" />
                                    </Field>

                                    <Field>
                                        <FieldLabel>SKU</FieldLabel>
                                        <Input name="sku" placeholder="Masukkan sku" />
                                    </Field>

                                    <Field>
                                        <FieldLabel>Barcode</FieldLabel>
                                        <Input name="barcode" placeholder="Masukkan barcode" />
                                    </Field>

                                    <Field>
                                        <FieldLabel>Kategori</FieldLabel>
                                        <Select name="kategori">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Masukkan kategori" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="gift">GIFT</SelectItem>
                                                <SelectItem value="acc">ACCESORIES</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </Field>

                                    <Field>
                                        <FieldLabel>Satuan</FieldLabel>
                                        <Select name="satuan">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Masukkan satuan" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="pcs">Pcs</SelectItem>
                                                <SelectItem value="box">Box</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </Field>

                                    <Field>
                                        <FieldLabel>Lokasi</FieldLabel>
                                        <Select name="lokasi">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Masukkan lokasi" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="pusat">Pusat</SelectItem>
                                                <SelectItem value="cabang">Cabang</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </Field>

                                    <div /> {/* Spacer */}

                                    <Field>
                                        <FieldLabel>Harga Jual</FieldLabel>
                                        <Input name="harga_jual" placeholder="Masukkan harga Jual" />
                                    </Field>

                                    <Field>
                                        <FieldLabel>Harga Beli</FieldLabel>
                                        <Input name="harga_beli" placeholder="Masukkan harga beli" />
                                    </Field>
                                </FieldGroup>
                            </FieldSet>

                            {/* Stock Table Section */}
                            <div className="space-y-4">
                                <Label className="mb-4 inline-block">Stok</Label>
                                <div className="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead className="w-[40%]">Lokasi</TableHead>
                                                <TableHead className="text-center">Stok Sekarang</TableHead>
                                                <TableHead className="text-center">Stok</TableHead>
                                                <TableHead className="text-right">Aksi</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {stockData.map((item, index) => (
                                                <TableRow key={index}>
                                                    <TableCell className="font-medium">{item.lokasi}</TableCell>
                                                    <TableCell className="text-center">{item.sekarang}</TableCell>
                                                    <TableCell className="text-center">{item.stok}</TableCell>
                                                    <TableCell className="text-right">
                                                        <Button type="button" variant="outline" size="icon">
                                                            <Pencil />
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>
                        </div>
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
    );
}

