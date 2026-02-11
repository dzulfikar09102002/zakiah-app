import { Pencil } from "lucide-react";
import { Form, Head, Link } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Field,
    FieldSet,
    FieldGroup,
    FieldLabel,
    FieldError
} from "@/components/ui/field";
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

import AppLayout from "@/layouts/app-layout";
import { BreadcrumbItem } from "@/types";
import products from "@/routes/products";

const title = 'Tambah Produk'

const breadcrumbs: BreadcrumbItem[] = [
    { href: products.index().url, title: 'Kelola Produk' },
    { href: products.create().url, title }
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

export default () => {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <Form className="space-y-4">
                <FieldSet>
                    <FieldGroup className="grid grid-cols-2 gap-x-8 gap-y-4">
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

                <div className="flex gap-2">
                    <Button type="submit">
                        Tambah
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href={products.index().url}>Batal</Link>
                    </Button>
                </div>
            </Form>
        </AppLayout>
    );
}