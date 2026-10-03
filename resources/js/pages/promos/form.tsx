import { Head, Link, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgeDollarSign,
    Pencil,
    Percent,
    Save,
} from 'lucide-react';
import type { SubmitEventHandler } from 'react';
import { toast } from 'sonner';
import DatePicker from '@/components/date-picker';
import LocationDropdown from '@/components/location-dropdown';
import { promoPeriod } from '@/components/promos/table';
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
import {
    Field,
    FieldDescription,
    FieldError,
    FieldLabel,
    FieldSet,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { Promo, PromoTemplate } from '@/lib/model';
import { cn } from '@/lib/utils';
import promos from '@/routes/promos';
import type { BreadcrumbItem } from '@/types';

type Mode = 'create' | 'edit' | 'show';

type Props = {
    promo: Promo | null;
    mode: Mode;
    locationOptions: { id: number; name: string }[];
    customerCategoryOptions: { id: number; name: string }[];
};

const titles: Record<Mode, string> = {
    create: 'Promo Baru',
    edit: 'Ubah Promo',
    show: 'Detail Promo',
};

const templates: {
    value: PromoTemplate;
    label: string;
    icon: typeof Percent;
}[] = [
    { value: 'discount_percentage', label: 'Persentase', icon: Percent },
    {
        value: 'discount_fixed',
        label: 'Nominal Tetap',
        icon: BadgeDollarSign,
    },
];

const toNumber = (value: string): number | '' =>
    value === '' ? '' : Number(value);

export default ({
    promo,
    mode,
    locationOptions,
    customerCategoryOptions,
}: Props) => {
    const readOnly = mode === 'show';
    const title = titles[mode];
    const today = new Date().toISOString().slice(0, 10);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Promosi', href: promos.index().url },
        { title, href: '#' },
    ];

    const existingCategoryIds =
        promo?.promo_rule?.promo_rule_customer_categories?.map(
            (item) => item.customer_category_id,
        ) ?? [];
    const existingMinimum = promo?.promo_rule?.minimum_sales_purchase ?? null;

    const { data, setData, post, put, processing, errors, transform } = useForm(
        {
            name: promo?.name ?? '',
            description: promo?.description ?? '',
            owner_location_id: (promo?.owner_location_id ?? '') as number | '',
            start_at: promo?.start_at?.slice(0, 10) ?? today,
            end_at: promo?.end_at?.slice(0, 10) ?? '',
            template: (promo?.promo_reward?.template ??
                'discount_percentage') as PromoTemplate,
            reward_amount: (promo?.promo_reward?.reward_amount ?? '') as
                | number
                | '',
            reward_maximum_amount: (promo?.promo_reward
                ?.reward_maximum_amount ?? '') as number | '',
            use_minimum: existingMinimum !== null && existingMinimum > 0,
            minimum_sales_purchase: (existingMinimum ?? '') as number | '',
            use_customer_categories: existingCategoryIds.length > 0,
            customer_category_ids: existingCategoryIds,
        },
    );

    const fieldError = (key: string) => (errors as Record<string, string>)[key];

    const isPercentage = data.template === 'discount_percentage';
    const disabled = readOnly || processing;

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        transform((form) => ({
            name: form.name,
            description: form.description,
            owner_location_id: form.owner_location_id,
            start_at: form.start_at,
            end_at: form.end_at || null,
            promo_rule: {
                minimum_sales_purchase: form.use_minimum
                    ? form.minimum_sales_purchase || null
                    : null,
                customer_category_ids: form.use_customer_categories
                    ? form.customer_category_ids
                    : [],
            },
            promo_reward: {
                template: form.template,
                reward_amount: form.reward_amount,
                reward_maximum_amount:
                    isPercentage && form.reward_maximum_amount !== ''
                        ? form.reward_maximum_amount
                        : null,
            },
        }));

        const options = {
            onSuccess: () =>
                toast.success(
                    `Promo berhasil ${mode === 'edit' ? 'diperbarui' : 'ditambahkan'}`,
                ),
            onError: () => toast.error('Periksa kembali data promo'),
        };

        if (mode === 'edit' && promo) {
            put(promos.update(promo.id).url, options);
        } else {
            post(promos.store().url, options);
        }
    };

    const toggleCategory = (id: number, checked: boolean) =>
        setData(
            'customer_category_ids',
            checked
                ? [...data.customer_category_ids, id]
                : data.customer_category_ids.filter((item) => item !== id),
        );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <form onSubmit={submit} className="space-y-4">
                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-2">
                        <div className="space-y-1.5">
                            <CardTitle className="flex items-center gap-2">
                                {title}
                                {promo && (
                                    <Badge variant={promoPeriod(promo).variant}>
                                        {promoPeriod(promo).label}
                                    </Badge>
                                )}
                            </CardTitle>
                            <CardDescription>
                                {promo
                                    ? `Kode promo: ${promo.code}`
                                    : 'Promo otomatis diterapkan di kasir selama periode berlaku.'}
                            </CardDescription>
                        </div>
                        {readOnly && promo && (
                            <Button variant="outline" asChild>
                                <Link href={promos.edit(promo.id).url}>
                                    <Pencil /> Ubah
                                </Link>
                            </Button>
                        )}
                    </CardHeader>
                    <CardContent>
                        <FieldSet className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <Field className="lg:col-span-2">
                                <FieldLabel htmlFor="name">
                                    Nama Promo
                                </FieldLabel>
                                <Input
                                    id="name"
                                    placeholder="Masukkan nama promo"
                                    disabled={disabled}
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                />
                                <FieldError>{errors.name}</FieldError>
                            </Field>

                            <Field>
                                <FieldLabel>Tanggal Mulai</FieldLabel>
                                <DatePicker
                                    value={data.start_at}
                                    disabled={disabled}
                                    onChange={(value) =>
                                        setData('start_at', value)
                                    }
                                />
                                <FieldError>{errors.start_at}</FieldError>
                            </Field>

                            <Field>
                                <FieldLabel>Tanggal Selesai</FieldLabel>
                                <DatePicker
                                    value={data.end_at}
                                    placeholder="Tanpa batas waktu"
                                    clearable
                                    minDate={data.start_at}
                                    disabled={disabled}
                                    onChange={(value) =>
                                        setData('end_at', value)
                                    }
                                />
                                <FieldError>{errors.end_at}</FieldError>
                            </Field>

                            <Field className="lg:col-span-2">
                                <FieldLabel htmlFor="description">
                                    Deskripsi
                                </FieldLabel>
                                <Textarea
                                    id="description"
                                    placeholder="Masukkan deskripsi"
                                    disabled={disabled}
                                    value={data.description}
                                    onChange={(e) =>
                                        setData('description', e.target.value)
                                    }
                                />
                                <FieldError>{errors.description}</FieldError>
                            </Field>

                            <Field className="lg:col-span-2">
                                <FieldLabel>Lokasi Pemilik</FieldLabel>
                                <LocationDropdown
                                    full
                                    options={locationOptions}
                                    disabled={disabled}
                                    defaultId={
                                        data.owner_location_id || undefined
                                    }
                                    handleIdChange={(id) =>
                                        id &&
                                        id !== data.owner_location_id &&
                                        setData('owner_location_id', id)
                                    }
                                />
                                <FieldDescription>
                                    Promo berlaku di semua lokasi; lokasi ini
                                    dicatat sebagai pemilik promo.
                                </FieldDescription>
                                <FieldError>
                                    {errors.owner_location_id}
                                </FieldError>
                            </Field>
                        </FieldSet>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Diskon</CardTitle>
                        <CardDescription>
                            Diskon diterapkan ke total pesanan.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <FieldSet className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <Field className="lg:col-span-2">
                                <FieldLabel>Jenis Diskon</FieldLabel>
                                <div className="grid grid-cols-2 gap-2 lg:max-w-md">
                                    {templates.map((template) => (
                                        <Button
                                            key={template.value}
                                            type="button"
                                            disabled={disabled}
                                            variant={
                                                data.template === template.value
                                                    ? 'default'
                                                    : 'outline'
                                            }
                                            className="justify-start"
                                            onClick={() =>
                                                setData(
                                                    'template',
                                                    template.value,
                                                )
                                            }
                                        >
                                            <template.icon />
                                            {template.label}
                                        </Button>
                                    ))}
                                </div>
                                <FieldError>
                                    {fieldError('promo_reward.template')}
                                </FieldError>
                            </Field>

                            <Field>
                                <FieldLabel htmlFor="reward_amount">
                                    {isPercentage
                                        ? 'Besaran Diskon (%)'
                                        : 'Besaran Diskon (Rp)'}
                                </FieldLabel>
                                <Input
                                    id="reward_amount"
                                    type="number"
                                    min="1"
                                    max={isPercentage ? 100 : undefined}
                                    placeholder="0"
                                    disabled={disabled}
                                    value={data.reward_amount}
                                    onChange={(e) =>
                                        setData(
                                            'reward_amount',
                                            toNumber(e.target.value),
                                        )
                                    }
                                />
                                <FieldError>
                                    {fieldError('promo_reward.reward_amount')}
                                </FieldError>
                            </Field>

                            {isPercentage && (
                                <Field>
                                    <FieldLabel htmlFor="reward_maximum_amount">
                                        Maksimal Diskon (Rp)
                                    </FieldLabel>
                                    <Input
                                        id="reward_maximum_amount"
                                        type="number"
                                        min="0"
                                        placeholder="Tanpa batas"
                                        disabled={disabled}
                                        value={data.reward_maximum_amount}
                                        onChange={(e) =>
                                            setData(
                                                'reward_maximum_amount',
                                                toNumber(e.target.value),
                                            )
                                        }
                                    />
                                    <FieldError>
                                        {fieldError(
                                            'promo_reward.reward_maximum_amount',
                                        )}
                                    </FieldError>
                                </Field>
                            )}
                        </FieldSet>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Syarat Promo</CardTitle>
                        <CardDescription>
                            Kosongkan bila promo berlaku untuk semua transaksi.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <FieldSet className="grid grid-cols-1 gap-4">
                            <Field orientation="horizontal">
                                <Checkbox
                                    id="use_minimum"
                                    checked={data.use_minimum}
                                    disabled={disabled}
                                    onCheckedChange={(checked) =>
                                        setData('use_minimum', checked === true)
                                    }
                                />
                                <FieldLabel htmlFor="use_minimum">
                                    Minimal belanja
                                </FieldLabel>
                            </Field>
                            {data.use_minimum && (
                                <Field className="lg:max-w-md">
                                    <Input
                                        type="number"
                                        min="0"
                                        placeholder="Minimal belanja (Rp)"
                                        disabled={disabled}
                                        value={data.minimum_sales_purchase}
                                        onChange={(e) =>
                                            setData(
                                                'minimum_sales_purchase',
                                                toNumber(e.target.value),
                                            )
                                        }
                                    />
                                    <FieldError>
                                        {fieldError(
                                            'promo_rule.minimum_sales_purchase',
                                        )}
                                    </FieldError>
                                </Field>
                            )}

                            <Field orientation="horizontal">
                                <Checkbox
                                    id="use_customer_categories"
                                    checked={data.use_customer_categories}
                                    disabled={disabled}
                                    onCheckedChange={(checked) =>
                                        setData(
                                            'use_customer_categories',
                                            checked === true,
                                        )
                                    }
                                />
                                <FieldLabel htmlFor="use_customer_categories">
                                    Khusus kategori pelanggan tertentu
                                </FieldLabel>
                            </Field>
                            {data.use_customer_categories && (
                                <div>
                                    <div
                                        className={cn(
                                            'grid gap-2 rounded-md border p-3 sm:grid-cols-2 lg:grid-cols-3',
                                            !customerCategoryOptions.length &&
                                                'text-sm text-muted-foreground',
                                        )}
                                    >
                                        {customerCategoryOptions.length
                                            ? customerCategoryOptions.map(
                                                  (category) => (
                                                      <Field
                                                          key={category.id}
                                                          orientation="horizontal"
                                                      >
                                                          <Checkbox
                                                              id={`category-${category.id}`}
                                                              checked={data.customer_category_ids.includes(
                                                                  category.id,
                                                              )}
                                                              disabled={
                                                                  disabled
                                                              }
                                                              onCheckedChange={(
                                                                  checked,
                                                              ) =>
                                                                  toggleCategory(
                                                                      category.id,
                                                                      checked ===
                                                                          true,
                                                                  )
                                                              }
                                                          />
                                                          <FieldLabel
                                                              htmlFor={`category-${category.id}`}
                                                          >
                                                              {category.name}
                                                          </FieldLabel>
                                                      </Field>
                                                  ),
                                              )
                                            : 'Belum ada kategori pelanggan aktif.'}
                                    </div>
                                    <FieldError className="mt-2">
                                        {fieldError(
                                            'promo_rule.customer_category_ids',
                                        )}
                                    </FieldError>
                                </div>
                            )}
                        </FieldSet>
                    </CardContent>
                </Card>

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" asChild>
                        <Link href={promos.index().url}>
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
