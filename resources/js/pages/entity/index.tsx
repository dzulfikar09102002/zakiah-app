import { Head, useForm } from '@inertiajs/react';
import { Pencil, Save, X } from 'lucide-react';
import type { SubmitEventHandler } from 'react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import entityRoutes from '@/routes/entity';
import type { BreadcrumbItem } from '@/types';
import type { Entity } from '@/types/auth';

const title = 'Entity';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: entityRoutes.edit().url,
    },
];

type Props = {
    entity: Entity;
    logoUrl: string | null;
    timezoneOptions: { value: string; label: string }[];
};

type TextField = {
    name:
        | 'email'
        | 'website'
        | 'full_address'
        | 'city'
        | 'province'
        | 'postal_code';
    label: string;
    placeholder: string;
    wide?: boolean;
};

const textFields: TextField[] = [
    { name: 'email', label: 'Email', placeholder: 'nama@domain.com' },
    {
        name: 'website',
        label: 'Website',
        placeholder: 'https://www.domain.com',
    },
    {
        name: 'full_address',
        label: 'Alamat Lengkap',
        placeholder: 'Masukkan alamat lengkap',
        wide: true,
    },
    { name: 'city', label: 'Kota', placeholder: 'Masukkan kota' },
    { name: 'province', label: 'Provinsi', placeholder: 'Masukkan provinsi' },
    {
        name: 'postal_code',
        label: 'Kode Pos',
        placeholder: 'Masukkan kode pos',
    },
];

export default ({ entity, logoUrl, timezoneOptions }: Props) => {
    const [isEdit, setIsEdit] = useState(false);

    const initialData = {
        name: entity.name ?? '',
        email: entity.email ?? '',
        website: entity.website ?? '',
        phone_number_country_code: entity.phone_number_country_code ?? '',
        phone_number: entity.phone_number ?? '',
        full_address: entity.full_address ?? '',
        city: entity.city ?? '',
        province: entity.province ?? '',
        postal_code: entity.postal_code ?? '',
        timezone: entity.timezone ?? '',
    };

    const {
        data,
        setData,
        put,
        processing,
        errors,
        clearErrors,
        setDefaults,
        reset,
    } = useForm(initialData);

    const disabled = !isEdit || processing;

    const cancelEdit = () => {
        reset();
        clearErrors();
        setIsEdit(false);
    };

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        put(entityRoutes.update().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Entity berhasil diperbarui');
                setDefaults();
                setIsEdit(false);
            },
            onError: () => toast.error('Gagal memperbarui entity'),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <form onSubmit={submit} className="grid gap-4 lg:grid-cols-3">
                <Card className="h-fit">
                    <CardHeader>
                        <CardTitle>Profil Entity</CardTitle>
                        <CardDescription>
                            Logo dipakai untuk icon tab browser dan kop laporan
                            PDF.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-col items-center gap-3 text-center">
                        <div className="flex size-32 items-center justify-center rounded-xl border bg-white p-3">
                            {logoUrl ? (
                                <img
                                    src={logoUrl}
                                    alt={`Logo ${entity.name}`}
                                    className="max-h-full max-w-full object-contain"
                                />
                            ) : (
                                <span className="text-sm text-muted-foreground">
                                    Belum ada logo
                                </span>
                            )}
                        </div>
                        <div>
                            <p className="font-semibold">{entity.name}</p>
                            {entity.code && (
                                <p className="text-sm text-muted-foreground">
                                    Kode: {entity.code}
                                </p>
                            )}
                        </div>
                    </CardContent>
                </Card>

                <Card className="lg:col-span-2">
                    <CardHeader className="flex flex-row items-start justify-between gap-2">
                        <div className="space-y-1.5">
                            <CardTitle>Pengaturan Entity</CardTitle>
                            <CardDescription>
                                Informasi entity yang tampil di laporan dan
                                struk.
                            </CardDescription>
                        </div>
                        {!isEdit && (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setIsEdit(true)}
                            >
                                <Pencil /> Ubah
                            </Button>
                        )}
                    </CardHeader>

                    <CardContent>
                        <FieldSet className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <Field className="lg:col-span-2">
                                <FieldLabel htmlFor="name">
                                    Nama Entity
                                </FieldLabel>
                                <Input
                                    id="name"
                                    placeholder="Masukkan nama entity"
                                    disabled={disabled}
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                />
                                <FieldError>{errors.name}</FieldError>
                            </Field>

                            <Field>
                                <FieldLabel htmlFor="phone_number">
                                    Nomor Telepon
                                </FieldLabel>
                                <div className="flex gap-2">
                                    <Input
                                        id="phone_number_country_code"
                                        className="w-20"
                                        inputMode="numeric"
                                        placeholder="62"
                                        disabled={disabled}
                                        value={data.phone_number_country_code}
                                        onChange={(e) =>
                                            setData(
                                                'phone_number_country_code',
                                                e.target.value.replace(
                                                    /\D/g,
                                                    '',
                                                ),
                                            )
                                        }
                                    />
                                    <Input
                                        id="phone_number"
                                        inputMode="numeric"
                                        placeholder="81234567890"
                                        disabled={disabled}
                                        value={data.phone_number}
                                        onChange={(e) =>
                                            setData(
                                                'phone_number',
                                                e.target.value.replace(
                                                    /\D/g,
                                                    '',
                                                ),
                                            )
                                        }
                                    />
                                </div>
                                <FieldError>
                                    {errors.phone_number_country_code ??
                                        errors.phone_number}
                                </FieldError>
                            </Field>

                            <Field>
                                <FieldLabel>Zona Waktu</FieldLabel>
                                <Select
                                    value={data.timezone}
                                    onValueChange={(val) =>
                                        setData('timezone', val)
                                    }
                                    disabled={disabled}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Pilih zona waktu" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {timezoneOptions.map((tz) => (
                                            <SelectItem
                                                key={tz.value}
                                                value={tz.value}
                                            >
                                                {tz.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError>{errors.timezone}</FieldError>
                            </Field>

                            {textFields.map((field) => (
                                <Field
                                    key={field.name}
                                    className={
                                        field.wide ? 'lg:col-span-2' : ''
                                    }
                                >
                                    <FieldLabel htmlFor={field.name}>
                                        {field.label}
                                    </FieldLabel>
                                    <Input
                                        id={field.name}
                                        placeholder={field.placeholder}
                                        disabled={disabled}
                                        value={data[field.name]}
                                        onChange={(e) =>
                                            setData(field.name, e.target.value)
                                        }
                                    />
                                    <FieldError>
                                        {errors[field.name]}
                                    </FieldError>
                                </Field>
                            ))}
                        </FieldSet>

                        {isEdit && (
                            <div className="mt-6 flex justify-end gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    disabled={processing}
                                    onClick={cancelEdit}
                                >
                                    <X /> Batal
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing ? <Spinner /> : <Save />}
                                    Simpan
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
};
