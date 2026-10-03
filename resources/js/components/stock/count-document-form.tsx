import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Save, Trash2 } from 'lucide-react';
import type { SubmitEventHandler } from 'react';
import { toast } from 'sonner';
import LocationDropdown from '@/components/location-dropdown';
import ProductPicker from '@/components/product-picker';
import type { ProductOption } from '@/components/product-picker';
import { DifferenceValue } from '@/components/stock/helpers';
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
import type { StockCountDocument } from '@/lib/model';
import { toRupiah } from '@/lib/utils';
import type { Option } from '@/types';
import type { CountDocumentConfig } from './count-document';
import { documentDetails } from './count-document';

type Row = {
    product: ProductOption | null;
    product_id: number | '';
    recorded_stock: number;
    counted_stock: number | '';
};

type Props = {
    config: CountDocumentConfig;
    document: StockCountDocument | null;
    locationOptions: Option[];
};

export default function CountDocumentForm({
    config,
    document,
    locationOptions,
}: Props) {
    const editing = document !== null;
    const title = editing
        ? `Ubah ${config.title} ${document.code}`
        : `${config.title} Baru`;

    const initialRows: Row[] = document
        ? documentDetails(document, config).map((detail) => ({
              product: {
                  id: detail.product_id,
                  name: detail.product?.name ?? detail.product_name,
                  sku: detail.product?.sku ?? detail.product_sku,
                  barcode: detail.product?.barcode ?? null,
                  sell_price: detail.product?.sell_price ?? 0,
                  cost_of_goods_sold: 0,
                  product_unit: {
                      id: detail.product_unit_id,
                      name: detail.product_unit_name ?? '-',
                  },
                  stock: detail.recorded_stock,
              },
              product_id: detail.product_id,
              recorded_stock: detail.recorded_stock,
              counted_stock: detail.counted_stock,
          }))
        : [];

    const { data, setData, post, put, processing, errors, transform } = useForm(
        {
            location_id: (document?.location_id ?? '') as number | '',
            note: document?.note ?? '',
            auto_approve: config.autoApproveDefault,
            products: initialRows,
        },
    );

    const rowError = (index: number, field: string) =>
        (errors as Record<string, string>)[`products.${index}.${field}`];

    const updateRow = (index: number, patch: Partial<Row>) =>
        setData(
            'products',
            data.products.map((row, i) =>
                i === index ? { ...row, ...patch } : row,
            ),
        );

    const changeLocation = (id: number) => {
        if (!id || id === data.location_id) return;

        if (data.products.length) {
            toast.info('Lokasi berubah, daftar produk dikosongkan.');
        }

        setData((form) => ({ ...form, location_id: id, products: [] }));
    };

    const differenceOf = (row: Row) =>
        row.counted_stock === '' ? 0 : row.counted_stock - row.recorded_stock;

    const totals = data.products.reduce(
        (sum, row) => ({
            recorded: sum.recorded + row.recorded_stock,
            counted: sum.counted + Number(row.counted_stock || 0),
            difference: sum.difference + differenceOf(row),
            value:
                sum.value + differenceOf(row) * (row.product?.sell_price ?? 0),
        }),
        { recorded: 0, counted: 0, difference: 0, value: 0 },
    );

    const selectedIds = data.products
        .map((row) => row.product_id)
        .filter((id): id is number => id !== '');

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        transform((form) => ({
            location_id: form.location_id,
            note: form.note,
            auto_approve: form.auto_approve,
            products: form.products.map((row) => ({
                product_id: row.product_id,
                counted_stock: row.counted_stock,
            })),
        }));

        const options = {
            onSuccess: () =>
                toast.success(
                    `${config.title} berhasil ${editing ? 'diperbarui' : 'disimpan'}`,
                ),
            onError: (formErrors: Record<string, string>) =>
                toast.error(
                    formErrors.employee ??
                        `Periksa kembali data ${config.noun}`,
                ),
        };

        if (editing && config.routes.update) {
            put(config.routes.update(document.id).url, options);
        } else {
            post(config.routes.store().url, options);
        }
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: config.title, href: config.routes.index().url },
                { title, href: '#' },
            ]}
        >
            <Head title={title} />

            <form onSubmit={submit} className="space-y-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{title}</CardTitle>
                        <CardDescription>{config.description}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <FieldSet className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <Field>
                                <FieldLabel>Lokasi</FieldLabel>
                                <LocationDropdown
                                    full
                                    options={locationOptions.map((option) => ({
                                        id: Number(option.value),
                                        name: option.label,
                                    }))}
                                    disabled={editing || processing}
                                    defaultId={data.location_id || undefined}
                                    handleIdChange={changeLocation}
                                />
                                <FieldError>{errors.location_id}</FieldError>
                            </Field>

                            <Field className="lg:col-span-2">
                                <FieldLabel htmlFor="note">Catatan</FieldLabel>
                                <Textarea
                                    id="note"
                                    placeholder="Catatan (opsional)"
                                    disabled={processing}
                                    value={data.note}
                                    onChange={(e) =>
                                        setData('note', e.target.value)
                                    }
                                />
                                <FieldError>{errors.note}</FieldError>
                            </Field>

                            {!editing && (
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
                                            Stok langsung disesuaikan saat
                                            disimpan. Matikan bila perlu
                                            persetujuan terlebih dahulu.
                                        </FieldDescription>
                                    </div>
                                </Field>
                            )}
                        </FieldSet>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-2">
                        <div className="space-y-1.5">
                            <CardTitle>Produk</CardTitle>
                            <CardDescription>
                                Stok tercatat diambil dari stok lokasi saat ini;
                                isi stok fisik hasil hitung.
                            </CardDescription>
                        </div>
                        <Button
                            type="button"
                            variant="secondary"
                            disabled={!data.location_id || processing}
                            onClick={() =>
                                setData('products', [
                                    ...data.products,
                                    {
                                        product: null,
                                        product_id: '',
                                        recorded_stock: 0,
                                        counted_stock: '',
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
                                    <TableHead className="w-[36%]">
                                        Produk
                                    </TableHead>
                                    <TableHead>Satuan</TableHead>
                                    <TableHead className="text-right">
                                        Tercatat
                                    </TableHead>
                                    <TableHead className="w-32">
                                        Stok Fisik
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Selisih
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Nilai Selisih
                                    </TableHead>
                                    <TableHead className="w-12" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {data.products.map((row, index) => (
                                    <TableRow
                                        key={`${row.product_id}-${index}`}
                                    >
                                        <TableCell className="align-top">
                                            <ProductPicker
                                                value={row.product}
                                                locationId={
                                                    data.location_id || null
                                                }
                                                excludeIds={selectedIds}
                                                disabled={processing}
                                                onSelect={(product) =>
                                                    updateRow(index, {
                                                        product,
                                                        product_id: product.id,
                                                        recorded_stock:
                                                            product.stock ?? 0,
                                                    })
                                                }
                                            />
                                            <FieldError>
                                                {rowError(index, 'product_id')}
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
                                                {row.recorded_stock}
                                            </div>
                                        </TableCell>
                                        <TableCell className="align-top">
                                            <Input
                                                type="number"
                                                min="0"
                                                placeholder="0"
                                                disabled={processing}
                                                value={row.counted_stock}
                                                onChange={(e) =>
                                                    updateRow(index, {
                                                        counted_stock:
                                                            e.target.value ===
                                                            ''
                                                                ? ''
                                                                : Number(
                                                                      e.target
                                                                          .value,
                                                                  ),
                                                    })
                                                }
                                            />
                                            <FieldError>
                                                {rowError(
                                                    index,
                                                    'counted_stock',
                                                )}
                                            </FieldError>
                                        </TableCell>
                                        <TableCell className="text-right align-top">
                                            <div className="flex h-9 items-center justify-end">
                                                <DifferenceValue
                                                    value={differenceOf(row)}
                                                />
                                            </div>
                                        </TableCell>
                                        <TableCell className="text-right align-top">
                                            <div className="flex h-9 items-center justify-end">
                                                {toRupiah(
                                                    differenceOf(row) *
                                                        (row.product
                                                            ?.sell_price ?? 0),
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
                                ))}
                                {!data.products.length && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={7}
                                            className="py-6 text-center text-muted-foreground"
                                        >
                                            {data.location_id
                                                ? 'Belum ada produk'
                                                : 'Pilih lokasi terlebih dahulu'}
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                            {data.products.length > 0 && (
                                <TableFooter>
                                    <TableRow>
                                        <TableCell colSpan={2}>Total</TableCell>
                                        <TableCell className="text-right">
                                            {totals.recorded}
                                        </TableCell>
                                        <TableCell>{totals.counted}</TableCell>
                                        <TableCell className="text-right">
                                            <DifferenceValue
                                                value={totals.difference}
                                            />
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {toRupiah(totals.value)}
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
                        <Link
                            href={
                                editing
                                    ? config.routes.show(document.id).url
                                    : config.routes.index().url
                            }
                        >
                            <ArrowLeft /> Kembali
                        </Link>
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? <Spinner /> : <Save />}
                        Simpan
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
}
