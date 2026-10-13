import { useEffect, useRef, useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogClose,
    DialogCancel,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupButton,
    InputGroupInput,
} from '@/components/ui/input-group';
import { cn } from '@/lib/utils';
import { Minus, Plus } from 'lucide-react';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Option, SharedData } from '@/types';
import { Product } from '@/lib/model';
import products from '@/routes/products';
import { Spinner } from './ui/spinner';
import { toast } from 'sonner';

export interface StockMovementRow {
    location_id: number;
    buying_price: number;
    stock: number;
    stock_new: string;
}

function buildStockMovements(
    product: Product | undefined,
    locations: Option[],
): StockMovementRow[] {
    const existingMap = new Map(
        product?.product_location_stocks?.map((sm) => [sm.location_id, sm]) ??
            [],
    );

    return locations.map((loc) => {
        const id = loc.value as unknown as number;
        const existing = existingMap.get(id);
        return {
            location_id: id,
            buying_price: product?.last_buying_price ?? 0,
            stock: existing?.stock ?? 0,
            stock_new: '',
        };
    });
}

interface ProductFormDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    locations: Option[];
    categories: Option[];
    units: Option[];
    suppliers: string[];
    product?: Product;
    onSuccess?: () => void;
}

export function ProductFormDialog({
    open,
    onOpenChange,
    locations,
    units,
    categories,
    suppliers,
    product,
    onSuccess,
}: ProductFormDialogProps) {
    const isEdit = Boolean(product);
    const employee_code = usePage<SharedData>().props.auth.user.employee.code;

    const { post, put, processing, errors, clearErrors, ...iform } =
        useForm<any>({});

    const [categoryId, setCategoryId] = useState(
        String(product?.product_category_id ?? ''),
    );
    const [locationId, setLocationId] = useState(
        String(product?.location_id ?? ''),
    );
    const [unitId, setUnitId] = useState(
        String(product?.product_unit_id ?? ''),
    );

    // State untuk input supplier & autocomplete dropdown
    const [supplierName, setSupplierName] = useState(
        String((product as any)?.supplier?.name ?? ''),
    );
    const [showSupplierDropdown, setShowSupplierDropdown] = useState(false);
    const filteredSuppliers =
        suppliers?.filter((s) =>
            s.toLowerCase().includes(supplierName.toLowerCase()),
        ) || [];

    const [isSubmitting, setIsSubmitting] = useState(false);

    const [stockMovements, setStockMovements] = useState<StockMovementRow[]>(
        () => buildStockMovements(product, locations),
    );

    const formRef = useRef<HTMLFormElement>(null);

    const setStockDelta = (index: number, value: string) =>
        setStockMovements((rows) =>
            rows.map((row, i) =>
                i === index ? { ...row, stock_new: value } : row,
            ),
        );

    const stepStockDelta = (index: number, step: number) => {
        const row = stockMovements[index];
        const next = (Number(row.stock_new) || 0) + step;
        // stok akhir tidak boleh minus
        if (row.stock + next < 0) return;
        setStockDelta(index, next === 0 ? '' : String(next));
    };

    useEffect(() => {
        if (!open) return;

        clearErrors();
        setCategoryId(String(product?.product_category_id ?? ''));
        setLocationId(String(product?.location_id ?? ''));
        setUnitId(String(product?.product_unit_id ?? ''));
        setSupplierName(String((product as any)?.supplier?.name ?? ''));
        setStockMovements(buildStockMovements(product, locations));
    }, [open]);

    const handleSubmit = () => {
        if (!formRef.current) return;

        const fd = new FormData(formRef.current);
        const get = (name: string) => (fd.get(name) as string) ?? '';

        const last_buying_price = Number(get('last_buying_price'));

        const payload: Partial<Product> = {
            name: get('name'),
            sku: get('sku'),
            barcode: get('barcode'),
            description: get('description'),
            sell_price: Number(get('sell_price')),
            last_buying_price,
            product_category_id: Number(categoryId),
            location_id: Number(locationId),
            product_unit_id: Number(unitId),
            product_sell_unit_id: Number(unitId),
            supplier_name: supplierName,
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
            stock_movements: stockMovements.map((row, i) => ({
                location_id: row.location_id,
                buying_price: last_buying_price,
                stock: Number(stockMovements[i].stock_new) || 0,
            })),
            product_unit_conversions: [],
            product_sell_prices: [],
        };

        iform.setData(payload);
        setIsSubmitting(true);
    };

    useEffect(() => {
        if (isSubmitting) {
            const options = {
                headers: { 'x-employee-code': employee_code },
                onSuccess: () => {
                    onOpenChange(false);
                    onSuccess?.();
                },
                onFinish: () => setIsSubmitting(false),
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
            <DialogContent className="overflow-y-auto sm:max-w-2xl md:max-w-3xl lg:max-w-4xl">
                <DialogCancel />
                <DialogHeader>
                    <DialogTitle>
                        {isEdit ? 'Edit Produk' : 'Produk Baru'}
                    </DialogTitle>
                </DialogHeader>

                <ScrollArea className="-mx-4 max-h-[70vh] px-4">
                    <form ref={formRef} onSubmit={(e) => e.preventDefault()}>
                        <div className="grid gap-6 p-1 py-2">
                            <h4 className="text-sm font-medium">
                                Informasi Dasar
                            </h4>

                            <div className="grid gap-4">
                                {/* Nama */}
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Nama</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={product?.name ?? ''}
                                        placeholder="Nama produk"
                                    />
                                    {errors.name && (
                                        <p className="text-xs text-destructive">
                                            {errors.name}
                                        </p>
                                    )}
                                </div>

                                {/* Deskripsi */}
                                <div className="grid gap-2">
                                    <Label htmlFor="description">
                                        Deskripsi
                                    </Label>
                                    <Input
                                        id="description"
                                        name="description"
                                        defaultValue={
                                            product?.description ?? ''
                                        }
                                        placeholder="Keterangan produk"
                                    />
                                    {errors.description && (
                                        <p className="text-xs text-destructive">
                                            {errors.description}
                                        </p>
                                    )}
                                </div>

                                {/* Satuan & Supplier (Dibuat Sejajar / 2 Kolom) */}
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="category">Satuan</Label>
                                        <Select
                                            value={unitId}
                                            onValueChange={setUnitId}
                                        >
                                            <SelectTrigger id="category">
                                                <SelectValue placeholder="Pilih satuan" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {units.map((unit) => (
                                                    <SelectItem
                                                        key={unit.value}
                                                        value={String(
                                                            unit.value,
                                                        )}
                                                    >
                                                        {unit.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.product_unit_id && (
                                            <p className="text-xs text-destructive">
                                                {errors.product_unit_id}
                                            </p>
                                        )}
                                    </div>

                                    {/* Field Supplier dengan Autocomplete */}
                                    <div className="grid gap-2">
                                        <Label htmlFor="supplier_name">
                                            Supplier
                                        </Label>
                                        <div className="relative">
                                            <Input
                                                id="supplier_name"
                                                name="supplier_name"
                                                placeholder="Pilih atau ketik supplier"
                                                autoComplete="off"
                                                value={supplierName}
                                                onFocus={() =>
                                                    setShowSupplierDropdown(
                                                        true,
                                                    )
                                                }
                                                onBlur={() => {
                                                    setTimeout(
                                                        () =>
                                                            setShowSupplierDropdown(
                                                                false,
                                                            ),
                                                        200,
                                                    );
                                                }}
                                                onChange={(e) => {
                                                    setSupplierName(
                                                        e.target.value,
                                                    );
                                                    setShowSupplierDropdown(
                                                        true,
                                                    );
                                                }}
                                            />
                                            {showSupplierDropdown &&
                                                filteredSuppliers.length >
                                                    0 && (
                                                    <div className="absolute top-full left-0 z-50 mt-1 max-h-48 w-full overflow-y-auto rounded-md border bg-popover p-1 text-popover-foreground shadow-md">
                                                        {filteredSuppliers.map(
                                                            (
                                                                supName,
                                                                index,
                                                            ) => (
                                                                <div
                                                                    key={index}
                                                                    className="relative flex w-full cursor-pointer items-center rounded-sm px-2 py-1.5 text-sm outline-none select-none hover:bg-accent hover:text-accent-foreground"
                                                                    onClick={() => {
                                                                        setSupplierName(
                                                                            supName,
                                                                        );
                                                                        setShowSupplierDropdown(
                                                                            false,
                                                                        );
                                                                    }}
                                                                >
                                                                    {supName}
                                                                </div>
                                                            ),
                                                        )}
                                                    </div>
                                                )}
                                        </div>
                                        {errors.supplier_name && (
                                            <p className="text-xs text-destructive">
                                                {errors.supplier_name}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                {/* SKU & Barcode */}
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="sku">SKU</Label>
                                        <Input
                                            id="sku"
                                            name="sku"
                                            defaultValue={product?.sku ?? ''}
                                            placeholder="Stock Keeping Unit"
                                        />
                                        {errors.sku && (
                                            <p className="text-xs text-destructive">
                                                {errors.sku}
                                            </p>
                                        )}
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="barcode">Barcode</Label>
                                        <Input
                                            id="barcode"
                                            name="barcode"
                                            defaultValue={
                                                product?.barcode ?? ''
                                            }
                                            placeholder="Kode barcode"
                                        />
                                        {errors.barcode && (
                                            <p className="text-xs text-destructive">
                                                {errors.barcode}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                {/* Harga Jual & Harga Beli */}
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="sell_price">
                                            Harga Jual
                                        </Label>
                                        <Input
                                            id="sell_price"
                                            name="sell_price"
                                            type="number"
                                            defaultValue={product?.sell_price}
                                            placeholder="0"
                                            min={0}
                                        />
                                        {errors.sell_price && (
                                            <p className="text-xs text-destructive">
                                                {errors.sell_price}
                                            </p>
                                        )}
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="last_buying_price">
                                            Harga Beli
                                        </Label>
                                        <Input
                                            id="last_buying_price"
                                            name="last_buying_price"
                                            type="number"
                                            defaultValue={
                                                product?.last_buying_price
                                            }
                                            placeholder="0"
                                            min={0}
                                        />
                                        {errors.last_buying_price && (
                                            <p className="text-xs text-destructive">
                                                {errors.last_buying_price}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                {/* Kategori & Lokasi */}
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="category">
                                            Kategori
                                        </Label>
                                        <Select
                                            value={categoryId}
                                            onValueChange={setCategoryId}
                                        >
                                            <SelectTrigger id="category">
                                                <SelectValue placeholder="Pilih kategori" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {categories.map((cat) => (
                                                    <SelectItem
                                                        key={cat.value}
                                                        value={String(
                                                            cat.value,
                                                        )}
                                                        className="cursor-pointer"
                                                    >
                                                        {cat.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.product_category_id && (
                                            <p className="text-xs text-destructive">
                                                {errors.product_category_id}
                                            </p>
                                        )}
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="location">
                                            Lokasi Input
                                        </Label>
                                        <Select
                                            value={locationId}
                                            onValueChange={setLocationId}
                                        >
                                            <SelectTrigger id="location">
                                                <SelectValue placeholder="Pilih lokasi" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {locations.map((loc) => (
                                                    <SelectItem
                                                        key={loc.value}
                                                        value={String(
                                                            loc.value,
                                                        )}
                                                        className="cursor-pointer"
                                                    >
                                                        {loc.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.location_id && (
                                            <p className="text-xs text-destructive">
                                                {errors.location_id}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            {/* ── Stock per Lokasi ───────────────────────────── */}
                            <div className="grid gap-3">
                                <h4 className="text-sm font-medium">
                                    Stock per Lokasi
                                </h4>

                                {stockMovements.length > 0 && (
                                    <div className="hidden grid-cols-12 gap-3 px-1 sm:grid">
                                        <span className="col-span-5 text-xs text-muted-foreground">
                                            Lokasi
                                        </span>
                                        <span className="col-span-3 text-xs text-muted-foreground">
                                            Stok Saat Ini
                                        </span>
                                        <span className="col-span-4 text-xs text-muted-foreground">
                                            Tambah Stok (minus = kurangi)
                                        </span>
                                    </div>
                                )}

                                <div className="grid gap-2">
                                    {stockMovements.map((row, index) => {
                                        const locationLabel =
                                            locations.find(
                                                (l) =>
                                                    (l.value as unknown as number) ==
                                                    row.location_id,
                                            )?.label ??
                                            `Lokasi #${row.location_id}`;
                                        const delta =
                                            Number(row.stock_new) || 0;
                                        const finalStock = row.stock + delta;
                                        const error =
                                            errors[
                                                `stock_movements.${index}.stock`
                                            ];

                                        return (
                                            <div
                                                key={row.location_id}
                                                className="grid gap-3 rounded-lg border p-3 sm:grid-cols-12 sm:items-start sm:rounded-none sm:border-0 sm:p-0"
                                            >
                                                {/* Mobile: nama lokasi + stok saat ini */}
                                                <div className="flex items-center justify-between gap-2 sm:hidden">
                                                    <span className="truncate text-sm font-medium">
                                                        {locationLabel}
                                                    </span>
                                                    <span className="shrink-0 text-xs text-muted-foreground">
                                                        Stok saat ini:{' '}
                                                        <span className="font-medium text-foreground">
                                                            {row.stock}
                                                        </span>
                                                    </span>
                                                </div>

                                                {/* Desktop: kolom lokasi & stok saat ini */}
                                                <div className="hidden sm:col-span-5 sm:block">
                                                    <Input
                                                        value={locationLabel}
                                                        readOnly
                                                        className="cursor-not-allowed bg-muted text-muted-foreground"
                                                    />
                                                </div>
                                                <div className="hidden sm:col-span-3 sm:block">
                                                    <Input
                                                        type="number"
                                                        value={row.stock}
                                                        readOnly
                                                        className="cursor-not-allowed bg-muted text-muted-foreground"
                                                    />
                                                </div>

                                                <div className="grid gap-1 sm:col-span-4">
                                                    <InputGroup>
                                                        <InputGroupAddon align="inline-start">
                                                            <InputGroupButton
                                                                size="icon-sm"
                                                                aria-label="Kurangi stok"
                                                                onClick={() =>
                                                                    stepStockDelta(
                                                                        index,
                                                                        -1,
                                                                    )
                                                                }
                                                                disabled={
                                                                    finalStock <=
                                                                    0
                                                                }
                                                            >
                                                                <Minus />
                                                            </InputGroupButton>
                                                        </InputGroupAddon>
                                                        <InputGroupInput
                                                            type="number"
                                                            inputMode="numeric"
                                                            name={`stock_new_${index}`}
                                                            value={
                                                                row.stock_new
                                                            }
                                                            onChange={(e) =>
                                                                setStockDelta(
                                                                    index,
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            placeholder="0"
                                                            min={-row.stock}
                                                            aria-invalid={
                                                                Boolean(
                                                                    error,
                                                                ) ||
                                                                finalStock < 0
                                                            }
                                                            className="[appearance:textfield] text-center tabular-nums [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                                        />
                                                        <InputGroupAddon align="inline-end">
                                                            <InputGroupButton
                                                                size="icon-sm"
                                                                aria-label="Tambah stok"
                                                                onClick={() =>
                                                                    stepStockDelta(
                                                                        index,
                                                                        1,
                                                                    )
                                                                }
                                                            >
                                                                <Plus />
                                                            </InputGroupButton>
                                                        </InputGroupAddon>
                                                    </InputGroup>

                                                    {delta !== 0 && (
                                                        <p
                                                            className={cn(
                                                                'text-xs tabular-nums',
                                                                finalStock < 0
                                                                    ? 'text-destructive'
                                                                    : 'text-muted-foreground',
                                                            )}
                                                        >
                                                            Stok akhir:{' '}
                                                            {row.stock}{' '}
                                                            {delta > 0
                                                                ? '+'
                                                                : '−'}{' '}
                                                            {Math.abs(delta)} ={' '}
                                                            <span className="font-medium">
                                                                {finalStock}
                                                            </span>
                                                        </p>
                                                    )}

                                                    {error && (
                                                        <p className="text-xs text-destructive">
                                                            {error}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        );
                                    })}

                                    {stockMovements.length === 0 && (
                                        <p className="py-2 text-sm text-muted-foreground">
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
                        <Button
                            variant="outline"
                            type="button"
                            disabled={processing}
                        >
                            Batal
                        </Button>
                    </DialogClose>
                    <Button
                        type="button"
                        onClick={handleSubmit}
                        disabled={processing}
                    >
                        {processing && <Spinner />}
                        {processing ? 'Menyimpan…' : 'Simpan'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
