import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Pencil, Plus, Save, Trash2 } from 'lucide-react';
import type { SubmitEventHandler } from 'react';
import { toast } from 'sonner';
import { loyaltyStatusLabel } from '@/components/loyalties/table';
import ProductPicker from '@/components/product-picker';
import type { ProductOption } from '@/components/product-picker';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { Loyalty } from '@/lib/model';
import { toRupiah } from '@/lib/utils';
import loyalties from '@/routes/loyalties';
import type { BreadcrumbItem } from '@/types';

type Mode = 'create' | 'edit' | 'show';

type Props = {
    loyalty: Loyalty | null;
    mode: Mode;
};

type RewardRow = {
    id?: number;
    product: ProductOption | null;
    product_id: number | '';
    product_unit_id: number | '';
    point_needed: number | '';
    maximum_quantity: number | '';
};

const titles: Record<Mode, string> = {
    create: 'Loyalty Baru',
    edit: 'Ubah Loyalty',
    show: 'Detail Loyalty',
};

const emptyRow = (): RewardRow => ({
    product: null,
    product_id: '',
    product_unit_id: '',
    point_needed: '',
    maximum_quantity: '',
});

const toNumber = (value: string): number | '' =>
    value === '' ? '' : Number(value);

export default ({ loyalty, mode }: Props) => {
    const readOnly = mode === 'show';
    const title = titles[mode];

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Loyalty', href: loyalties.index().url },
        { title, href: '#' },
    ];

    const { data, setData, post, put, processing, errors, transform } = useForm(
        {
            name: loyalty?.name ?? '',
            description: loyalty?.description ?? '',
            miniminal_transaction_value:
                (loyalty?.miniminal_transaction_value ?? '') as number | '',
            reward_point: (loyalty?.reward_point ?? '') as number | '',
            allow_multiple: loyalty?.allow_multiple ?? true,
            reward_products: (loyalty?.reward_products ?? []).map(
                (row): RewardRow => ({
                    id: row.id,
                    product: row.product
                        ? {
                              ...row.product,
                              cost_of_goods_sold: 0,
                              product_unit: row.product_unit ?? null,
                              stock: null,
                          }
                        : null,
                    product_id: row.product_id,
                    product_unit_id: row.product_unit_id,
                    point_needed: row.point_needed,
                    maximum_quantity: row.maximum_quantity ?? '',
                }),
            ),
        },
    );

    const rowError = (index: number, field: string) =>
        (errors as Record<string, string>)[`reward_products.${index}.${field}`];

    const updateRow = (index: number, patch: Partial<RewardRow>) =>
        setData(
            'reward_products',
            data.reward_products.map((row, i) =>
                i === index ? { ...row, ...patch } : row,
            ),
        );

    const selectProduct = (index: number, product: ProductOption) =>
        updateRow(index, {
            product,
            product_id: product.id,
            product_unit_id: product.product_unit?.id ?? '',
        });

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        transform((form) => ({
            ...form,
            reward_products: form.reward_products.map((row) => ({
                id: row.id,
                product_id: row.product_id,
                product_unit_id: row.product_unit_id,
                point_needed: row.point_needed,
                maximum_quantity:
                    row.maximum_quantity === '' ? null : row.maximum_quantity,
            })),
        }));

        const options = {
            onSuccess: () =>
                toast.success(
                    `Loyalty berhasil ${mode === 'edit' ? 'diperbarui' : 'ditambahkan'}`,
                ),
            onError: () => toast.error('Periksa kembali data loyalty'),
        };

        if (mode === 'edit' && loyalty) {
            put(loyalties.update(loyalty.id).url, options);
        } else {
            post(loyalties.store().url, options);
        }
    };

    const selectedIds = data.reward_products
        .map((row) => row.product_id)
        .filter((id): id is number => id !== '');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <form onSubmit={submit} className="space-y-4">
                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-2">
                        <div className="space-y-1.5">
                            <CardTitle className="flex items-center gap-2">
                                {title}
                                {loyalty && (
                                    <Badge variant="secondary">
                                        {loyaltyStatusLabel[loyalty.status]}
                                    </Badge>
                                )}
                            </CardTitle>
                            <CardDescription>
                                Pelanggan mendapat poin setiap transaksi yang
                                mencapai minimal transaksi, lalu poin dapat
                                ditukar dengan produk hadiah.
                            </CardDescription>
                        </div>
                        {readOnly && loyalty && (
                            <Button variant="outline" asChild>
                                <Link href={loyalties.edit(loyalty.id).url}>
                                    <Pencil /> Ubah
                                </Link>
                            </Button>
                        )}
                    </CardHeader>
                    <CardContent>
                        <FieldSet className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <Field className="lg:col-span-2">
                                <FieldLabel htmlFor="name">
                                    Nama Loyalty
                                </FieldLabel>
                                <Input
                                    id="name"
                                    placeholder="Masukkan nama loyalty"
                                    disabled={readOnly || processing}
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                />
                                <FieldError>{errors.name}</FieldError>
                            </Field>

                            <Field>
                                <FieldLabel htmlFor="miniminal_transaction_value">
                                    Minimal Transaksi (Rp)
                                </FieldLabel>
                                <Input
                                    id="miniminal_transaction_value"
                                    type="number"
                                    min="1"
                                    placeholder="0"
                                    disabled={readOnly || processing}
                                    value={data.miniminal_transaction_value}
                                    onChange={(e) =>
                                        setData(
                                            'miniminal_transaction_value',
                                            toNumber(e.target.value),
                                        )
                                    }
                                />
                                <FieldError>
                                    {errors.miniminal_transaction_value}
                                </FieldError>
                            </Field>

                            <Field>
                                <FieldLabel htmlFor="reward_point">
                                    Poin Hadiah
                                </FieldLabel>
                                <Input
                                    id="reward_point"
                                    type="number"
                                    min="1"
                                    placeholder="0"
                                    disabled={readOnly || processing}
                                    value={data.reward_point}
                                    onChange={(e) =>
                                        setData(
                                            'reward_point',
                                            toNumber(e.target.value),
                                        )
                                    }
                                />
                                <FieldError>{errors.reward_point}</FieldError>
                            </Field>

                            <Field className="lg:col-span-2">
                                <FieldLabel htmlFor="description">
                                    Deskripsi
                                </FieldLabel>
                                <Textarea
                                    id="description"
                                    placeholder="Masukkan deskripsi"
                                    disabled={readOnly || processing}
                                    value={data.description}
                                    onChange={(e) =>
                                        setData('description', e.target.value)
                                    }
                                />
                                <FieldError>{errors.description}</FieldError>
                            </Field>

                            <Field
                                orientation="horizontal"
                                className="lg:col-span-2"
                            >
                                <Checkbox
                                    id="allow_multiple"
                                    checked={data.allow_multiple}
                                    disabled={readOnly || processing}
                                    onCheckedChange={(checked) =>
                                        setData(
                                            'allow_multiple',
                                            checked === true,
                                        )
                                    }
                                />
                                <FieldLabel htmlFor="allow_multiple">
                                    Poin berlipat (kelipatan minimal transaksi
                                    mendapat poin berlipat)
                                </FieldLabel>
                            </Field>
                        </FieldSet>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-2">
                        <div className="space-y-1.5">
                            <CardTitle>Produk Hadiah</CardTitle>
                            <CardDescription>
                                Produk yang dapat ditukar dengan poin.
                            </CardDescription>
                        </div>
                        {!readOnly && (
                            <Button
                                type="button"
                                variant="secondary"
                                disabled={processing}
                                onClick={() =>
                                    setData('reward_products', [
                                        ...data.reward_products,
                                        emptyRow(),
                                    ])
                                }
                            >
                                <Plus /> Tambah Produk
                            </Button>
                        )}
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-[40%]">
                                        Produk
                                    </TableHead>
                                    <TableHead>Satuan</TableHead>
                                    <TableHead>Poin Dibutuhkan</TableHead>
                                    <TableHead>Maks. Kuantitas</TableHead>
                                    {!readOnly && (
                                        <TableHead className="w-12" />
                                    )}
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {data.reward_products.map((row, index) => (
                                    <TableRow key={row.id ?? `new-${index}`}>
                                        <TableCell className="align-top">
                                            {readOnly ? (
                                                <div>
                                                    <p className="font-medium">
                                                        {row.product?.name}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {row.product?.sku}
                                                        {row.product &&
                                                            ` • ${toRupiah(row.product.sell_price)}`}
                                                    </p>
                                                </div>
                                            ) : (
                                                <ProductPicker
                                                    value={row.product}
                                                    excludeIds={selectedIds}
                                                    disabled={processing}
                                                    onSelect={(product) =>
                                                        selectProduct(
                                                            index,
                                                            product,
                                                        )
                                                    }
                                                />
                                            )}
                                            <FieldError>
                                                {rowError(index, 'product_id')}
                                            </FieldError>
                                        </TableCell>
                                        <TableCell className="align-top">
                                            <div className="flex h-9 items-center">
                                                {row.product?.product_unit
                                                    ?.name ?? '-'}
                                            </div>
                                            <FieldError>
                                                {rowError(
                                                    index,
                                                    'product_unit_id',
                                                )}
                                            </FieldError>
                                        </TableCell>
                                        <TableCell className="align-top">
                                            <Input
                                                type="number"
                                                min="1"
                                                placeholder="0"
                                                disabled={
                                                    readOnly || processing
                                                }
                                                value={row.point_needed}
                                                onChange={(e) =>
                                                    updateRow(index, {
                                                        point_needed: toNumber(
                                                            e.target.value,
                                                        ),
                                                    })
                                                }
                                            />
                                            <FieldError>
                                                {rowError(
                                                    index,
                                                    'point_needed',
                                                )}
                                            </FieldError>
                                        </TableCell>
                                        <TableCell className="align-top">
                                            <Input
                                                type="number"
                                                min="1"
                                                placeholder="Tanpa batas"
                                                disabled={
                                                    readOnly || processing
                                                }
                                                value={row.maximum_quantity}
                                                onChange={(e) =>
                                                    updateRow(index, {
                                                        maximum_quantity:
                                                            toNumber(
                                                                e.target.value,
                                                            ),
                                                    })
                                                }
                                            />
                                            <FieldError>
                                                {rowError(
                                                    index,
                                                    'maximum_quantity',
                                                )}
                                            </FieldError>
                                        </TableCell>
                                        {!readOnly && (
                                            <TableCell className="align-top">
                                                <Button
                                                    type="button"
                                                    size="icon"
                                                    variant="outline"
                                                    disabled={processing}
                                                    onClick={() =>
                                                        setData(
                                                            'reward_products',
                                                            data.reward_products.filter(
                                                                (_, i) =>
                                                                    i !== index,
                                                            ),
                                                        )
                                                    }
                                                >
                                                    <Trash2 className="text-destructive" />
                                                </Button>
                                            </TableCell>
                                        )}
                                    </TableRow>
                                ))}
                                {!data.reward_products.length && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={readOnly ? 4 : 5}
                                            className="py-6 text-center text-muted-foreground"
                                        >
                                            Belum ada produk hadiah
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                        <FieldError className="mt-2">
                            {errors.reward_products}
                        </FieldError>
                    </CardContent>
                </Card>

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" asChild>
                        <Link href={loyalties.index().url}>
                            <ArrowLeft /> Kembali
                        </Link>
                    </Button>
                    {!readOnly && (
                        <Button type="submit" disabled={processing}>
                            {processing ? <Spinner /> : <Save />}
                            Simpan
                        </Button>
                    )}
                </div>
            </form>
        </AppLayout>
    );
};
