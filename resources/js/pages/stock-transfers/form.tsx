import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Save, Trash2, TriangleAlert } from 'lucide-react';
import type { SubmitEventHandler } from 'react';
import { toast } from 'sonner';
import LocationDropdown from '@/components/location-dropdown';
import ProductPicker from '@/components/product-picker';
import type { ProductOption } from '@/components/product-picker';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Field,
    FieldDescription,
    FieldError,
    FieldLabel,
    FieldSet,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { toRupiah } from '@/lib/utils';
import productTransferServices from '@/routes/product-transfer-services';
import type { BreadcrumbItem, Option } from '@/types';

const title = 'Pindah Stok Baru';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pindah Stok', href: productTransferServices.index().url },
    { title, href: productTransferServices.create().url },
];

type Row = {
    product: ProductOption | null;
    product_id: number | '';
    quantity: number | '';
};

type Props = {
    locationOptions: Option[];
};

export default ({ locationOptions }: Props) => {
    const locations = locationOptions.map((option) => ({
        id: Number(option.value),
        name: option.label,
    }));

    const { data, setData, post, processing, errors, transform } = useForm({
        from_location_id: '' as number | '',
        to_location_id: '' as number | '',
        request_note: '',
        auto_approve: false,
        products: [] as Row[],
    });

    const rowError = (index: number, field: string) =>
        (errors as Record<string, string>)[`products.${index}.${field}`];

    const updateRow = (index: number, patch: Partial<Row>) =>
        setData(
            'products',
            data.products.map((row, i) =>
                i === index ? { ...row, ...patch } : row,
            ),
        );

    const changeFromLocation = (id: number) => {
        if (!id || id === data.from_location_id) return;

        if (data.products.length) {
            toast.info('Lokasi asal berubah, daftar produk dikosongkan.');
        }

        setData((form) => ({ ...form, from_location_id: id, products: [] }));
    };

    const selectedIds = data.products
        .map((row) => row.product_id)
        .filter((id): id is number => id !== '');

    const totalQuantity = data.products.reduce(
        (sum, row) => sum + Number(row.quantity || 0),
        0,
    );
    const totalValue = data.products.reduce(
        (sum, row) =>
            sum + Number(row.quantity || 0) * (row.product?.sell_price ?? 0),
        0,
    );

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        transform((form) => ({
            ...form,
            products: form.products.map((row) => ({
                product_id: row.product_id,
                quantity: row.quantity,
            })),
        }));

        post(productTransferServices.store().url, {
            onSuccess: () => toast.success('Pindah stok berhasil diajukan'),
            onError: (formErrors) =>
                toast.error(
                    formErrors.employee ?? 'Periksa kembali data pindah stok',
                ),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <form onSubmit={submit} className="space-y-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{title}</CardTitle>
                        <CardDescription>
                            Stok lokasi asal langsung dicadangkan saat diajukan,
                            lalu masuk ke lokasi tujuan setelah disetujui.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <FieldSet className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <Field>
                                <FieldLabel>Lokasi Asal</FieldLabel>
                                <LocationDropdown
                                    full
                                    options={locations}
                                    disabled={processing}
                                    handleIdChange={changeFromLocation}
                                />
                                <FieldError>
                                    {errors.from_location_id}
                                </FieldError>
                            </Field>

                            <Field>
                                <FieldLabel>Lokasi Tujuan</FieldLabel>
                                <LocationDropdown
                                    full
                                    options={locations.filter(
                                        (location) =>
                                            location.id !==
                                            data.from_location_id,
                                    )}
                                    disabled={processing}
                                    handleIdChange={(id) =>
                                        id &&
                                        id !== data.to_location_id &&
                                        setData('to_location_id', id)
                                    }
                                />
                                <FieldError>{errors.to_location_id}</FieldError>
                            </Field>

                            <Field className="lg:col-span-2">
                                <FieldLabel htmlFor="request_note">
                                    Catatan
                                </FieldLabel>
                                <Textarea
                                    id="request_note"
                                    placeholder="Catatan (opsional)"
                                    disabled={processing}
                                    value={data.request_note}
                                    onChange={(e) =>
                                        setData('request_note', e.target.value)
                                    }
                                />
                                <FieldError>{errors.request_note}</FieldError>
                            </Field>

                            <Field
                                orientation="horizontal"
                                className="lg:col-span-2"
                            >
                                <Checkbox
                                    id="auto_approve"
                                    checked={data.auto_approve}
                                    disabled={processing}
                                    onCheckedChange={(checked) =>
                                        setData(
                                            'auto_approve',
                                            checked === true,
                                        )
                                    }
                                />
                                <div>
                                    <FieldLabel htmlFor="auto_approve">
                                        Langsung setujui
                                    </FieldLabel>
                                    <FieldDescription>
                                        Stok langsung masuk ke lokasi tujuan
                                        tanpa menunggu persetujuan.
                                    </FieldDescription>
                                </div>
                            </Field>
                        </FieldSet>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-2">
                        <div className="space-y-1.5">
                            <CardTitle>Produk</CardTitle>
                            <CardDescription>
                                Stok yang tampil adalah stok di lokasi asal.
                            </CardDescription>
                        </div>
                        <Button
                            type="button"
                            variant="secondary"
                            disabled={!data.from_location_id || processing}
                            onClick={() =>
                                setData('products', [
                                    ...data.products,
                                    {
                                        product: null,
                                        product_id: '',
                                        quantity: '',
                                    },
                                ])
                            }
                        >
                            <Plus /> Tambah Produk
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-[40%]">
                                        Produk
                                    </TableHead>
                                    <TableHead>Satuan</TableHead>
                                    <TableHead className="text-right">
                                        Stok Asal
                                    </TableHead>
                                    <TableHead className="w-32">
                                        Jumlah
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Nilai
                                    </TableHead>
                                    <TableHead className="w-12" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {data.products.map((row, index) => {
                                    const stock = row.product?.stock ?? 0;
                                    const exceeds =
                                        row.product !== null &&
                                        Number(row.quantity || 0) > stock;

                                    return (
                                        <TableRow
                                            key={`${row.product_id}-${index}`}
                                        >
                                            <TableCell className="align-top">
                                                <ProductPicker
                                                    value={row.product}
                                                    locationId={
                                                        data.from_location_id ||
                                                        null
                                                    }
                                                    excludeIds={selectedIds}
                                                    disabled={processing}
                                                    onSelect={(product) =>
                                                        updateRow(index, {
                                                            product,
                                                            product_id:
                                                                product.id,
                                                        })
                                                    }
                                                />
                                                <FieldError>
                                                    {rowError(
                                                        index,
                                                        'product_id',
                                                    )}
                                                </FieldError>
                                            </TableCell>
                                            <TableCell className="align-top">
                                                <div className="flex h-9 items-center">
                                                    {row.product?.product_unit
                                                        ?.name ?? '-'}
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-right align-top">
                                                <div className="flex h-9 items-center justify-end">
                                                    {row.product ? stock : '-'}
                                                </div>
                                            </TableCell>
                                            <TableCell className="align-top">
                                                <Input
                                                    type="number"
                                                    min="1"
                                                    placeholder="0"
                                                    disabled={processing}
                                                    value={row.quantity}
                                                    aria-invalid={exceeds}
                                                    onChange={(e) =>
                                                        updateRow(index, {
                                                            quantity:
                                                                e.target
                                                                    .value ===
                                                                ''
                                                                    ? ''
                                                                    : Number(
                                                                          e
                                                                              .target
                                                                              .value,
                                                                      ),
                                                        })
                                                    }
                                                />
                                                {exceeds && (
                                                    <p className="mt-1 flex items-center gap-1 text-xs text-amber-600">
                                                        <TriangleAlert className="size-3" />
                                                        Melebihi stok asal
                                                    </p>
                                                )}
                                                <FieldError>
                                                    {rowError(
                                                        index,
                                                        'quantity',
                                                    )}
                                                </FieldError>
                                            </TableCell>
                                            <TableCell className="text-right align-top">
                                                <div className="flex h-9 items-center justify-end">
                                                    {toRupiah(
                                                        Number(
                                                            row.quantity || 0,
                                                        ) *
                                                            (row.product
                                                                ?.sell_price ??
                                                                0),
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="align-top">
                                                <Button
                                                    type="button"
                                                    size="icon"
                                                    variant="outline"
                                                    disabled={processing}
                                                    onClick={() =>
                                                        setData(
                                                            'products',
                                                            data.products.filter(
                                                                (_, i) =>
                                                                    i !== index,
                                                            ),
                                                        )
                                                    }
                                                >
                                                    <Trash2 className="text-destructive" />
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                                {!data.products.length && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-6 text-center text-muted-foreground"
                                        >
                                            {data.from_location_id
                                                ? 'Belum ada produk'
                                                : 'Pilih lokasi asal terlebih dahulu'}
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                            {data.products.length > 0 && (
                                <TableFooter>
                                    <TableRow>
                                        <TableCell colSpan={3}>Total</TableCell>
                                        <TableCell>{totalQuantity}</TableCell>
                                        <TableCell className="text-right">
                                            {toRupiah(totalValue)}
                                        </TableCell>
                                        <TableCell />
                                    </TableRow>
                                </TableFooter>
                            )}
                        </Table>
                        <FieldError className="mt-2">
                            {errors.products}
                        </FieldError>
                    </CardContent>
                </Card>

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" asChild>
                        <Link href={productTransferServices.index().url}>
                            <ArrowLeft /> Kembali
                        </Link>
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? <Spinner /> : <Save />}
                        Ajukan
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
};
