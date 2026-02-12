import AppLayout from "@/layouts/app-layout"
import products from "@/routes/products"
import { BreadcrumbItem } from "@/types"
import {
    Plus,
    FileDown,
    FileUp,
    Pencil,
    SearchIcon,
    X,
} from "lucide-react";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { Form, Head, Link, router, usePage } from "@inertiajs/react";
import { ButtonGroup } from "@/components/ui/button-group";
import ProductInfiniteList from "@/components/product-infinite-list";
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Field, FieldGroup, FieldLabel, FieldSet } from "@/components/ui/field";
import { Label } from "@/components/ui/label";
import { useEffect, useState } from "react";
import { capitalize, toRupiah } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";

const title = 'Kelola Produk'

const breadcrumbs: BreadcrumbItem[] = [
    {
        href: products.index().url,
        title
    }
]

const data = [
    { name: "Pita Serut Kecil", sku: "900602202535", barcode: "900602202535", category: "GIFT", buyPrice: "450", price: 1000, stock: 544 },
    { name: "Jarum pentul 5K", sku: "910403202336", barcode: "910403202336", category: "ACCESORIES", buyPrice: "700", price: "5.000", stock: 290 },
    { name: "Bross Pin 2K", sku: "913001202534", barcode: "913001202534", category: "ACCESORIES", buyPrice: "750", price: "2.000", stock: 469 },
    { name: "Strap masker Tali Polos 5K", sku: "592706202410", barcode: "592706202410", category: "ACCESORIES", buyPrice: "980", price: "5.000", stock: 205 },
    { name: "Kartu Ucapan Kecil", sku: "9928062021002", barcode: "9928062021002", category: "GIFT", buyPrice: "1.000", price: "2.000", stock: 235 },
];

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

type Props = {
}

export default ({ }: Props) => {
    const [isModalOpen, setIsModalOpen] = useState(false);

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
                    <form className="grid lg:flex gap-2">
                        <Select>
                            <SelectTrigger>
                                <SelectValue placeholder="Semua Kategori" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="gift">GIFT</SelectItem>
                                <SelectItem value="accessories">ACCESORIES</SelectItem>
                            </SelectContent>
                        </Select>
                        <Input placeholder="Cari..." />
                        <Button><SearchIcon /> Cari</Button>
                    </form>
                </CardHeader>
                <CardContent className="p-0 lg:px-6 border-t lg:border-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>No.</TableHead>
                                <TableHead>Produk</TableHead>
                                <TableHead>Kategori</TableHead>
                                <TableHead>SKU</TableHead>
                                <TableHead>Barcode</TableHead>
                                <TableHead>Harga Beli</TableHead>
                                <TableHead>Harga</TableHead>
                                <TableHead>Stok</TableHead>
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {data.map((product, idx) => (
                                <TableRow key={product.sku}>
                                    <TableCell>{idx + 1}</TableCell>
                                    <TableCell>{product.name}</TableCell>
                                    <TableCell><Badge variant={"secondary"}>{capitalize(product.category)}</Badge></TableCell>
                                    <TableCell>{product.sku}</TableCell>
                                    <TableCell>{product.barcode}</TableCell>
                                    <TableCell>{toRupiah(product.buyPrice)}</TableCell>
                                    <TableCell>{toRupiah(product.price)}</TableCell>
                                    <TableCell>{product.stock}</TableCell>
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
                        </TableBody>
                    </Table>
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

