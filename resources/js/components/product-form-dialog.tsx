import { useEffect, useRef, useState } from "react";
import { useForm, usePage } from "@inertiajs/react";
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
import { Option, SharedData } from "@/types";
import { Product } from "@/lib/model";
import products from "@/routes/products";
import { toast } from "sonner";


export interface StockMovementRow {
    location_id: number;
    buying_price: number;
    stock: number;
    stock_new: number;
}

function buildStockMovements(product: Product | undefined, locations: Option[]): StockMovementRow[] {
    const existingMap = new Map(
        product?.product_location_stocks?.map((sm) => [sm.location_id, sm]) ?? []
    );

    return locations.map((loc) => {
        const id = loc.value as unknown as number;
        const existing = existingMap.get(id);
        return {
            location_id: id,
            buying_price: product?.last_buying_price ?? 0,
            stock: existing?.stock ?? 0,
            stock_new: existing?.stock ?? 0,
        };
    });
}

interface ProductFormDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    locations: Option[];
    categories: Option[];
    units: Option[];
    product?: Product;
    onSuccess?: () => void;
}

export function ProductFormDialog({
    open,
    onOpenChange,
    locations,
    units,
    categories,
    product,
    onSuccess,
}: ProductFormDialogProps) {

    const isEdit = Boolean(product);
    const employee_code = usePage<SharedData>().props.auth.user.employee.code

    const { post, put, processing, errors, clearErrors, ...iform } = useForm<any>({});

    const [categoryId, setCategoryId] = useState(String(product?.product_category_id ?? ""));
    const [locationId, setLocationId] = useState(String(product?.location_id ?? ""));
    const [unitId, setUnitId] = useState(String(product?.product_unit_id ?? ""));

    const [isSubmitting, setIsSubmitting] = useState(false);

    const [stockMovements, setStockMovements] = useState<StockMovementRow[]>(() =>
        buildStockMovements(product, locations)
    );

    const formRef = useRef<HTMLFormElement>(null);

    useEffect(() => {
        if (!open) return;

        clearErrors();
        setCategoryId(String(product?.product_category_id ?? ""));
        setLocationId(String(product?.location_id ?? ""));
        setUnitId(String(product?.product_unit_id ?? ""));
        setStockMovements(buildStockMovements(product, locations));

    }, [open]);

    const handleSubmit = () => {
        if (!formRef.current) return;

        const fd = new FormData(formRef.current);
        const get = (name: string) => fd.get(name) as string ?? "";

        const last_buying_price = Number(get("last_buying_price"));

        const payload: Partial<Product> = {
            name: get("name"),
            sku: get("sku"),
            barcode: get("barcode"),
            description: get("description"),
            sell_price: Number(get("sell_price")),
            last_buying_price,
            product_category_id: Number(categoryId),
            location_id: Number(locationId),
            product_unit_id: Number(unitId),
            product_sell_unit_id: Number(unitId), // default sama dengan product_unit_id
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
            // buying_price disinkronkan dengan last_buying_price saat submit
            stock_movements: stockMovements.map((row, i) => ({
                location_id: row.location_id,
                buying_price: last_buying_price,
                stock: Number(get(`stock_new_${i}`)),
            })),
            product_unit_conversions: [],
            product_sell_prices: [],
        };

        iform.setData(payload)
        setIsSubmitting(true);

    };

    useEffect(() => {

        if (isSubmitting) {
            const options = {
                preserveState: true,
                headers: { "x-employee-code": employee_code },
                onSuccess: () => {
                    onOpenChange(false);
                    onSuccess?.();
                },
                onFinish: () => setIsSubmitting(false)
            };

            if (product) {
                put(products.update(product.id).url, options);
            } else {
                post(products.store().url, options);
            }
            setIsSubmitting(false);
        }
    }, [isSubmitting]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{isEdit ? "Edit Produk" : "Produk Baru"}</DialogTitle>
                </DialogHeader>

                <ScrollArea className="max-h-[70vh] -mx-1 px-1">
                    <form ref={formRef} onSubmit={(e) => e.preventDefault()}>
                        <div className="grid gap-6 py-2">
                            <h4 className="text-sm font-medium">Informasi Dasar</h4>

                            <div className="grid gap-4">
                                {/* Nama */}
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Nama</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={product?.name ?? ""}
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
                                        name="description"
                                        defaultValue={product?.description ?? ""}
                                        placeholder="Keterangan produk"
                                    />
                                    {errors.description && (
                                        <p className="text-xs text-destructive">{errors.description}</p>
                                    )}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="category">Satuan</Label>
                                    <Select value={unitId} onValueChange={setUnitId}>
                                        <SelectTrigger id="category">
                                            <SelectValue placeholder="Pilih satuan" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {units.map((unit) => (
                                                <SelectItem key={unit.value} value={String(unit.value)}>
                                                    {unit.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.product_unit_id && (
                                        <p className="text-xs text-destructive">{errors.product_unit_id}</p>
                                    )}
                                </div>

                                {/* SKU & Barcode */}
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="sku">SKU</Label>
                                        <Input
                                            id="sku"
                                            name="sku"
                                            defaultValue={product?.sku ?? ""}
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
                                            name="barcode"
                                            defaultValue={product?.barcode ?? ""}
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
                                            name="sell_price"
                                            type="number"
                                            defaultValue={product?.sell_price}
                                            placeholder="0"
                                            min={0}
                                        />
                                        {errors.sell_price && (
                                            <p className="text-xs text-destructive">{errors.sell_price}</p>
                                        )}
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="last_buying_price">Harga Beli</Label>
                                        <Input
                                            id="last_buying_price"
                                            name="last_buying_price"
                                            type="number"
                                            defaultValue={product?.last_buying_price}
                                            placeholder="0"
                                            min={0}
                                        />
                                        {errors.last_buying_price && (
                                            <p className="text-xs text-destructive">{errors.last_buying_price}</p>
                                        )}
                                    </div>
                                </div>

                                {/* Kategori & Lokasi — tetap pakai state karena bukan <select> native */}
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="category">Kategori</Label>
                                        <Select value={categoryId} onValueChange={setCategoryId}>
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
                                        <Select value={locationId} onValueChange={setLocationId}>
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

                                {stockMovements.length > 0 && (
                                    <div className="grid grid-cols-12 gap-3 px-1">
                                        <span className="col-span-5 text-xs text-muted-foreground">Lokasi</span>
                                        <span className="col-span-3 text-xs text-muted-foreground">Stok Saat Ini</span>
                                        <span className="col-span-4 text-xs text-muted-foreground">Stok Baru</span>
                                    </div>
                                )}

                                <div className="grid gap-2">
                                    {stockMovements.map((row, index) => (
                                        <div key={row.location_id} className="grid grid-cols-12 gap-3 items-center">
                                            <div className="col-span-5">
                                                <Input
                                                    value={locations.find((l) => (l.value as unknown as number) == row.location_id)?.label ?? `Lokasi #${row.location_id}`}
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
                                                {/* name="stock_new_{index}" dibaca FormData saat submit */}
                                                <Input
                                                    type="number"
                                                    name={`stock_new_${index}`}
                                                    defaultValue={row.stock_new || ''}
                                                    placeholder="0"
                                                    min={0}
                                                />
                                            </div>
                                        </div>
                                    ))}

                                    {stockMovements.length === 0 && (
                                        <p className="text-sm text-muted-foreground py-2">
                                            Tidak ada data lokasi.
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </form>
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