import AppLayout from "@/layouts/app-layout"
import products from "@/routes/products"
import { BreadcrumbItem } from "@/types"
import {
    Plus,
    FileDown,
    FileUp,
    Pencil,
    Trash2,
    SearchIcon
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
import { Head, Link } from "@inertiajs/react";

const title = 'Kelola Produk'

const breadcrumbs: BreadcrumbItem[] = [
    {
        href: products.index().url,
        title
    }
]

const data = [
    { name: "Pita Serut Kecil", sku: "900602202535", barcode: "900602202535", category: "GIFT", buyPrice: "450", price: "1.000", stock: 544 },
    { name: "Jarum pentul 5K", sku: "910403202336", barcode: "910403202336", category: "ACCESORIES", buyPrice: "700", price: "5.000", stock: 290 },
    { name: "Bross Pin 2K", sku: "913001202534", barcode: "913001202534", category: "ACCESORIES", buyPrice: "750", price: "2.000", stock: 469 },
    { name: "Strap masker Tali Polos 5K", sku: "592706202410", barcode: "592706202410", category: "ACCESORIES", buyPrice: "980", price: "5.000", stock: 205 },
    { name: "Kartu Ucapan Kecil", sku: "9928062021002", barcode: "9928062021002", category: "GIFT", buyPrice: "1.000", price: "2.000", stock: 235 },
];

export default function ProductTable() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            {/* Action Button */}
            <div className="flex justify-between mb-4">
                <Button asChild>
                    <Link href={products.create().url}>
                        <Plus /> Tambah Produk
                    </Link>
                </Button>
                <div className="flex gap-2">
                    <Button variant="outline">
                        <FileDown /> Export
                    </Button>
                    <Button variant="outline">
                        <FileUp /> Import Produk
                    </Button>
                </div>
            </div>

            {/* Table Section */}
            <Card>
                <CardHeader>
                    <form className="flex gap-2">
                        <Select>
                            <SelectTrigger>
                                <SelectValue placeholder="Semua Kategori" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="gift">GIFT</SelectItem>
                                <SelectItem value="accessories">ACCESORIES</SelectItem>
                            </SelectContent>
                        </Select>
                        <Input placeholder="Filter name..." />
                        <Button><SearchIcon /> Cari</Button>
                    </form>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>No.</TableHead>
                                <TableHead>Produk</TableHead>
                                <TableHead>SKU</TableHead>
                                <TableHead>Barcode</TableHead>
                                <TableHead>Kategori</TableHead>
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
                                    <TableCell>{product.sku}</TableCell>
                                    <TableCell>{product.barcode}</TableCell>
                                    <TableCell>{product.category}</TableCell>
                                    <TableCell>{product.buyPrice}</TableCell>
                                    <TableCell>{product.price}</TableCell>
                                    <TableCell>{product.stock}</TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            <Button variant="outline" size="icon">
                                                <Pencil />
                                            </Button>
                                            <Button variant="destructive" size="icon">
                                                <Trash2 />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </AppLayout>
    );
}

