import { useEffect } from "react";
import { useForm } from "@inertiajs/react";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogClose,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Separator } from "@/components/ui/separator";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Option } from "@/types";
import { Product } from "@/lib/model";
import products from "@/routes/products";

// ─── Types ────────────────────────────────────────────────────────────────────

export interface StockMovementRow {
    location_id: number;
    buying_price: number;
    stock: number;      // readonly — current/preview
    stock_new: number;  // editable — value sent to API
}


// ─── Helpers ──────────────────────────────────────────────────────────────────

/** Nilai default untuk form kosong (mode tambah) */
function defaultValues(locations: Option[]): Partial<Product> {
    return {
        name: "",
        sku: "",
        barcode: "",
        description: "",
        sell_price: 0,
        last_buying_price: 0,
        product_category_id: 0,
        product_unit_id: 5,
        product_sell_unit_id: 5,
        location_id: 0,
        image_url: null,
        sell_to_customer: true,
        service: false,
        modifier: false,
        allow_custom_price: false,
        select_all_location: true,
        location_ids: [],
        exclude_location_ids: [],
        tax_id: 0,
        tax_setting: null,
        stock_movements: locations.map((loc) => ({
            location_id: loc.value as unknown as number,
            buying_price: 0,
            stock: 0,
            stock_new: 0,
        })) as any,
        product_unit_conversions: [],
        product_sell_prices: [],
    };
}

/** Nilai awal untuk form edit (merge data produk yang ada) */
function editValues(product: Product, locations: Option[]): Partial<Product> {
    const existingMap = new Map(
        product.stock_movements!.map((sm) => [sm.location_id, sm])
    );

    const stock_movements = locations.map((loc) => {
        const id = loc.value as unknown as number;
        const existing = existingMap.get(id);
        return {
            location_id: id,
            buying_price: existing?.buying_price ?? product.last_buying_price,
            stock: existing?.stock ?? 0,
            stock_new: existing?.stock ?? 0,
        };
    });

    return {
        name: product.name,
        sku: product.sku,
        barcode: product.barcode,
        description: product.description,
        sell_price: product.sell_price,
        last_buying_price: product.last_buying_price,
        product_category_id: product.product_category_id,
        product_unit_id: 5,
        product_sell_unit_id: 5,
        location_id: product.location_id,
        image_url: null,
        sell_to_customer: true,
        service: false,
        modifier: false,
        allow_custom_price: false,
        select_all_location: true,
        location_ids: [],
        exclude_location_ids: [],
        tax_id: 0,
        tax_setting: null,
        stock_movements: stock_movements as any,
        product_unit_conversions: [],
        product_sell_prices: [],
    };
}

// ─── Props ────────────────────────────────────────────────────────────────────

interface ProductFormDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    locations: Option[];
    categories: Option[];
    /**
     * Jika diisi → mode EDIT; jika `undefined` → mode TAMBAH.
     */
    product?: Product;
    /** Callback opsional setelah submit berhasil */
    onSuccess?: () => void;
}

// ─── Component ────────────────────────────────────────────────────────────────

export function ProductFormDialog({
    open,
    onOpenChange,
    locations,
    categories,
    product,
    onSuccess,
}: ProductFormDialogProps) {
    const isEdit = Boolean(product);

    // ── Inertia useForm ──────────────────────────────────────────────────────
    const { data, setData, post, put, patch, processing, errors, reset, clearErrors } =
        useForm<any>(
            isEdit ? editValues(product!, locations) : defaultValues(locations)
        );

    // ── Re-inisialisasi form setiap kali dialog dibuka ───────────────────────
    useEffect(() => {
        if (!open) return;

        clearErrors();
        if (isEdit) {
            // Perbarui data ke nilai produk yang diedit (berguna jika `product` berubah)
            setData(editValues(product!, locations));
        } else {
            reset();
            // reset() mengembalikan ke nilai awal useForm; paksa ulang stock_movements
            setData(defaultValues(locations));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    // ── Sinkronisasi buying_price di semua baris saat last_buying_price berubah
    useEffect(() => {
        setData("stock_movements", (data.stock_movements as unknown as StockMovementRow[]).map((row) => ({
            ...row,
            buying_price: data.last_buying_price,
        })) as any);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.last_buying_price]);

    // ── Helpers ──────────────────────────────────────────────────────────────
    const updateStockNew = (index: number, value: number) => {
        const updated = (data.stock_movements as unknown as StockMovementRow[]).map((row, i) =>
            i === index ? { ...row, stock_new: value } : row
        );
        setData("stock_movements", updated as any);
    };

    const getLocationName = (id: number) =>
        locations.find((l) => (l.value as unknown as number) == id)?.label ?? `Lokasi #${id}`;

    // ── Submit ───────────────────────────────────────────────────────────────
    const handleSubmit = () => {
        // Bentuk final payload: stock_movements → ganti `stock_new` → `stock`
        const finalPayload: Partial<Product> = {
            ...data,
            stock_movements: (data.stock_movements as unknown as StockMovementRow[]).map(
                ({ location_id, buying_price, stock_new }) => ({
                    location_id,
                    buying_price,
                    stock: stock_new,
                })
            ),
        };

        const options = {
            data: finalPayload,
            onSuccess: () => {
                onOpenChange(false);
                onSuccess?.();
            },
        };

        if (product) {
            put(products.update(product.id).url)
        } else {
            post(products.store().url, {
                headers: {
                    'x-employee-code': 'admin-zakiah',
                }
            })
        }
    };

    const stockMovementRows = data.stock_movements as unknown as StockMovementRow[];

    // ─────────────────────────────────────────────────────────────────────────

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{isEdit ? "Edit Produk" : "Produk Baru"}</DialogTitle>
                </DialogHeader>

                <ScrollArea className="max-h-[70vh] -mx-1 px-1">
                    <div className="grid gap-6 py-2">
                        <h4 className="text-sm font-medium">Informasi Dasar</h4>

                        {/* ── Info Dasar ─────────────────────────────────── */}
                        <div className="grid gap-4">
                            {/* Nama */}
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nama</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData("name", e.target.value)}
                                    placeholder="Nama produk"
                                />
                                {errors.name && (
                                    <p className="text-xs text-destructive">{errors.name}</p>
                                )}
                            </div>

                            {/* Deskripsi */}
                            <div className="grid gap-2">
                                <Label htmlFor="description">Deskripsi</Label>
                                <Input
                                    id="description"
                                    value={data.description ?? ""}
                                    onChange={(e) => setData("description", e.target.value || null)}
                                    placeholder="Keterangan produk"
                                />
                                {errors.description && (
                                    <p className="text-xs text-destructive">{errors.description}</p>
                                )}
                            </div>

                            {/* SKU & Barcode */}
                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="sku">SKU</Label>
                                    <Input
                                        id="sku"
                                        value={data.sku}
                                        onChange={(e) => setData("sku", e.target.value)}
                                        placeholder="Stock Keeping Unit"
                                    />
                                    {errors.sku && (
                                        <p className="text-xs text-destructive">{errors.sku}</p>
                                    )}
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="barcode">Barcode</Label>
                                    <Input
                                        id="barcode"
                                        value={data.barcode}
                                        onChange={(e) => setData("barcode", e.target.value)}
                                        placeholder="Kode barcode"
                                    />
                                    {errors.barcode && (
                                        <p className="text-xs text-destructive">{errors.barcode}</p>
                                    )}
                                </div>
                            </div>

                            {/* Harga Jual & Harga Beli */}
                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="sell_price">Harga Jual</Label>
                                    <Input
                                        id="sell_price"
                                        type="number"
                                        value={data.sell_price || ""}
                                        onChange={(e) =>
                                            setData("sell_price", e.target.value === "" ? 0 : Number(e.target.value))
                                        }
                                        placeholder="0"
                                    />
                                    {errors.sell_price && (
                                        <p className="text-xs text-destructive">{errors.sell_price}</p>
                                    )}
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="last_buying_price">Harga Beli</Label>
                                    <Input
                                        id="last_buying_price"
                                        type="number"
                                        value={data.last_buying_price || ""}
                                        onChange={(e) =>
                                            setData(
                                                "last_buying_price",
                                                e.target.value === "" ? 0 : Number(e.target.value)
                                            )
                                        }
                                        placeholder="0"
                                    />
                                    {errors.last_buying_price && (
                                        <p className="text-xs text-destructive">{errors.last_buying_price}</p>
                                    )}
                                </div>
                            </div>

                            {/* Kategori & Lokasi Input */}
                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="category">Kategori</Label>
                                    <Select
                                        value={data.product_category_id}
                                        onValueChange={(v) => setData("product_category_id", v)}
                                    >
                                        <SelectTrigger id="category">
                                            <SelectValue placeholder="Pilih kategori" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((cat) => (
                                                <SelectItem key={cat.value} value={String(cat.value)}>
                                                    {cat.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.product_category_id && (
                                        <p className="text-xs text-destructive">{errors.product_category_id}</p>
                                    )}
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="location">Lokasi Input</Label>
                                    <Select
                                        value={data.location_id ? String(data.location_id) : ""}
                                        onValueChange={(v) => setData("location_id", Number(v))}
                                    >
                                        <SelectTrigger id="location">
                                            <SelectValue placeholder="Pilih lokasi" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {locations.map((loc) => (
                                                <SelectItem key={loc.value} value={String(loc.value)}>
                                                    {loc.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.location_id && (
                                        <p className="text-xs text-destructive">{errors.location_id}</p>
                                    )}
                                </div>
                            </div>
                        </div>

                        <Separator />

                        {/* ── Stock per Lokasi ───────────────────────────── */}
                        <div className="grid gap-3">
                            <h4 className="text-sm font-medium">Stock per Lokasi</h4>

                            {stockMovementRows.length > 0 && (
                                <div className="grid grid-cols-12 gap-3 px-1">
                                    <span className="col-span-5 text-xs text-muted-foreground">Lokasi</span>
                                    <span className="col-span-3 text-xs text-muted-foreground">Stok Saat Ini</span>
                                    <span className="col-span-4 text-xs text-muted-foreground">Stok Baru</span>
                                </div>
                            )}

                            <div className="grid gap-2">
                                {stockMovementRows.map((row, index) => (
                                    <div key={index} className="grid grid-cols-12 gap-3 items-center">
                                        <div className="col-span-5">
                                            <Input
                                                value={getLocationName(row.location_id)}
                                                readOnly
                                                className="bg-muted text-muted-foreground cursor-not-allowed"
                                            />
                                        </div>
                                        <div className="col-span-3">
                                            <Input
                                                type="number"
                                                value={row.stock}
                                                readOnly
                                                className="bg-muted text-muted-foreground cursor-not-allowed"
                                            />
                                        </div>
                                        <div className="col-span-4">
                                            <Input
                                                type="number"
                                                value={row.stock_new || ""}
                                                onChange={(e) => updateStockNew(index, Number(e.target.value))}
                                                placeholder="0"
                                                min={0}
                                            />
                                        </div>
                                    </div>
                                ))}

                                {stockMovementRows.length === 0 && (
                                    <p className="text-sm text-muted-foreground py-2">
                                        Tidak ada data lokasi.
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                </ScrollArea>

                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline" type="button" disabled={processing}>
                            Batal
                        </Button>
                    </DialogClose>
                    <Button type="button" onClick={handleSubmit} disabled={processing}>
                        {processing ? "Menyimpan…" : "Simpan"}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}