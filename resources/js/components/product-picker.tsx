import { Check, ChevronsUpDown, Package } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Spinner } from '@/components/ui/spinner';
import { cn, toRupiah } from '@/lib/utils';
import products from '@/routes/products';

export type ProductOption = {
    id: number;
    name: string;
    sku: string | null;
    barcode: string | null;
    sell_price: number;
    cost_of_goods_sold: number;
    product_unit: { id: number; name: string } | null;
    /** Stok di lokasi `locationId` (null bila locationId tidak diberikan). */
    stock: number | null;
};

type Props = {
    value?: ProductOption | null;
    onSelect: (product: ProductOption) => void;
    /** Bila diisi, opsi menampilkan stok produk di lokasi ini. */
    locationId?: number | null;
    /** Produk yang sudah dipilih di baris lain, ditandai & tidak bisa dipilih lagi. */
    excludeIds?: number[];
    placeholder?: string;
    disabled?: boolean;
    className?: string;
};

/**
 * Pemilih produk dengan pencarian ke server (nama/SKU/barcode), dipakai di form
 * loyalty, promosi, dan modul stok.
 */
export default function ProductPicker({
    value,
    onSelect,
    locationId,
    excludeIds = [],
    placeholder = 'Pilih produk',
    disabled,
    className,
}: Props) {
    const [open, setOpen] = useState(false);
    const [keyword, setKeyword] = useState('');
    const [options, setOptions] = useState<ProductOption[]>([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!open) return;

        const controller = new AbortController();
        const timer = setTimeout(async () => {
            setLoading(true);

            try {
                const url = products.dropdown({
                    query: {
                        search: keyword,
                        location_id: locationId ?? undefined,
                    },
                }).url;
                const res = await fetch(url, {
                    signal: controller.signal,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (res.ok) {
                    setOptions(await res.json());
                }
            } catch {
                // dibatalkan karena keyword berubah / popover ditutup
            } finally {
                if (!controller.signal.aborted) {
                    setLoading(false);
                }
            }
        }, 300);

        return () => {
            clearTimeout(timer);
            controller.abort();
        };
    }, [open, keyword, locationId]);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    type="button"
                    variant="outline"
                    role="combobox"
                    disabled={disabled}
                    className={cn(
                        'w-full cursor-pointer justify-between font-normal',
                        className,
                    )}
                >
                    <span className="flex min-w-0 items-center gap-2">
                        <Package className="shrink-0 opacity-60" />
                        <span
                            className={cn(
                                'truncate',
                                !value && 'text-muted-foreground',
                            )}
                        >
                            {value ? value.name : placeholder}
                        </span>
                    </span>
                    <ChevronsUpDown className="shrink-0 opacity-50" />
                </Button>
            </PopoverTrigger>

            <PopoverContent className="w-[--radix-popover-trigger-width] min-w-80 p-0">
                <Command shouldFilter={false}>
                    <CommandInput
                        placeholder="Cari nama, SKU, atau barcode..."
                        value={keyword}
                        onValueChange={setKeyword}
                    />
                    <CommandList>
                        {loading ? (
                            <div className="flex justify-center py-6">
                                <Spinner />
                            </div>
                        ) : (
                            <>
                                <CommandEmpty>
                                    Produk tidak ditemukan
                                </CommandEmpty>
                                {options.map((product) => {
                                    const taken =
                                        excludeIds.includes(product.id) &&
                                        product.id !== value?.id;

                                    return (
                                        <CommandItem
                                            key={product.id}
                                            value={String(product.id)}
                                            disabled={taken}
                                            className="cursor-pointer"
                                            onSelect={() => {
                                                onSelect(product);
                                                setOpen(false);
                                            }}
                                        >
                                            <Check
                                                className={cn(
                                                    'shrink-0',
                                                    value?.id === product.id
                                                        ? 'opacity-100'
                                                        : 'opacity-0',
                                                )}
                                            />
                                            <div className="min-w-0 flex-1">
                                                <p className="truncate font-medium">
                                                    {product.name}
                                                </p>
                                                <p className="truncate text-xs text-muted-foreground">
                                                    {[
                                                        product.sku,
                                                        product.product_unit
                                                            ?.name,
                                                        taken
                                                            ? 'sudah dipilih'
                                                            : null,
                                                    ]
                                                        .filter(Boolean)
                                                        .join(' • ')}
                                                </p>
                                            </div>
                                            <div className="text-right text-xs">
                                                <p>
                                                    {toRupiah(
                                                        product.sell_price,
                                                    )}
                                                </p>
                                                {product.stock !== null && (
                                                    <p className="text-muted-foreground">
                                                        Stok: {product.stock}
                                                    </p>
                                                )}
                                            </div>
                                        </CommandItem>
                                    );
                                })}
                            </>
                        )}
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
