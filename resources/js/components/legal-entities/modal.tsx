import { useForm } from '@inertiajs/react';
import type { ReactNode, SubmitEventHandler } from 'react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogCancel,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import type { LegalEntity, Option } from '@/lib/model';
import legalEntities from '@/routes/legal-entities';

export type ModalState = {
    isOpen: boolean;
    dataId?: number;
};

type Props = {
    modalState: ModalState;
    tableData: LegalEntity[];
    legalTypes: Option[];
    phoneCountryCodes: Option[];
    onModalSuccess: () => void;
    onModalClose: () => void;
};

const defaultData = {
    name: '',
    initial: '',
    legal_type: 'cv',
    npwp: '',
    is_pkp: false,
    email: '',
    phone_number_country_code: '+62',
    phone_number: '',
    full_address: '',
    postal_code: '',
    city: '',
    province: '',
    country: 'INDONESIA',
    bank_name: '',
    bank_account_no: '',
    bank_account_name: '',
};

type FormData = typeof defaultData;
type TextKey = Exclude<keyof FormData, 'is_pkp'>;

const validate = (data: FormData) => {
    const errors: Partial<Record<keyof FormData, string>> = {};

    if (!data.name.trim()) {
        errors.name = 'Nama wajib diisi.';
    } else if (data.name.length > 255) {
        errors.name = 'Nama maksimal 255 karakter.';
    }

    if (!data.legal_type) {
        errors.legal_type = 'Jenis badan usaha wajib dipilih.';
    }

    if (data.initial.length > 20) {
        errors.initial = 'Inisial maksimal 20 karakter.';
    }

    if (data.npwp.length > 30) {
        errors.npwp = 'NPWP maksimal 30 karakter.';
    }

    if (data.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
        errors.email = 'Format email tidak valid.';
    }

    if (data.phone_number && !/^\d{5,15}$/.test(data.phone_number)) {
        errors.phone_number = 'Nomor telepon harus 5-15 digit angka.';
    }

    if (
        data.postal_code &&
        (data.postal_code.length < 5 || data.postal_code.length > 10)
    ) {
        errors.postal_code = 'Kode pos harus 5-10 karakter.';
    }

    if (data.bank_account_no.length > 50) {
        errors.bank_account_no = 'Nomor rekening maksimal 50 karakter.';
    }

    return errors;
};

const Section = ({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) => (
    <section className="space-y-4">
        <h3 className="border-b pb-2 text-sm font-semibold text-muted-foreground">
            {title}
        </h3>
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">{children}</div>
    </section>
);

const Label = ({
    children,
    required,
}: {
    children: ReactNode;
    required?: boolean;
}) => (
    <FieldLabel>
        {children}
        {required && <span className="text-destructive">*</span>}
    </FieldLabel>
);

export default ({
    modalState,
    tableData,
    legalTypes,
    phoneCountryCodes,
    onModalSuccess,
    onModalClose,
}: Props) => {
    const {
        processing,
        patch,
        post,
        reset,
        errors,
        data,
        setData,
        setError,
        clearErrors,
    } = useForm(defaultData);

    useEffect(() => {
        clearErrors();

        if (!modalState.dataId) {
            reset();
            return;
        }

        const selected = tableData.find(
            (item) => item.id === modalState.dataId,
        );

        if (!selected) return;

        setData({
            name: selected.name ?? '',
            initial: selected.initial ?? '',
            legal_type: selected.legal_type ?? 'cv',
            npwp: selected.npwp ?? '',
            is_pkp: selected.is_pkp,
            email: selected.email ?? '',
            phone_number_country_code: selected.phone_number_country_code
                ? `+${selected.phone_number_country_code.replace('+', '')}`
                : '+62',
            phone_number: selected.phone_number ?? '',
            full_address: selected.full_address ?? '',
            postal_code: selected.postal_code ?? '',
            city: selected.city ?? '',
            province: selected.province ?? '',
            country: selected.country ?? '',
            bank_name: selected.bank_name ?? '',
            bank_account_no: selected.bank_account_no ?? '',
            bank_account_name: selected.bank_account_name ?? '',
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [modalState.dataId, modalState.isOpen]);

    const change = <K extends keyof FormData>(key: K, value: FormData[K]) => {
        setData((prev) => ({ ...prev, [key]: value }));

        if (errors[key]) clearErrors(key);
    };

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        const clientErrors = validate(data);

        if (Object.keys(clientErrors).length) {
            clearErrors();
            setError(clientErrors);
            toast.error('Periksa kembali isian yang belum valid');
            return;
        }

        const action = modalState.dataId ? patch : post;
        const url = modalState.dataId
            ? legalEntities.update(modalState.dataId).url
            : legalEntities.store().url;

        action(url, {
            only: ['pagination'],
            preserveState: true,
            onSuccess: () => {
                toast.success(
                    `Data berhasil ${modalState.dataId ? 'diperbarui' : 'ditambahkan'}`,
                );
                onModalSuccess();
                reset();
            },
            onError: () => {
                toast.error(
                    `Gagal ${modalState.dataId ? 'memperbarui' : 'menambahkan'}`,
                );
            },
        });
    };

    const textField = (
        key: TextKey,
        label: string,
        options: {
            placeholder?: string;
            required?: boolean;
            className?: string;
            inputMode?: 'numeric' | 'email';
        } = {},
    ) => (
        <Field className={options.className}>
            <Label required={options.required}>{label}</Label>
            <Input
                placeholder={options.placeholder}
                inputMode={options.inputMode}
                aria-invalid={!!errors[key]}
                value={data[key]}
                onChange={(e) => change(key, e.target.value)}
            />
            <FieldError>{errors[key]}</FieldError>
        </Field>
    );

    return (
        <Dialog
            open={modalState.isOpen}
            onOpenChange={() => {
                if (!processing) onModalClose();
            }}
        >
            <DialogContent
                asChild
                className="max-h-[90vh] overflow-y-auto sm:max-w-3xl"
            >
                <form onSubmit={submit} noValidate>
                    <DialogCancel />

                    <DialogHeader>
                        <DialogTitle>
                            {modalState.dataId ? 'Edit CV' : 'CV Baru'}
                        </DialogTitle>
                    </DialogHeader>

                    <div className="space-y-6">
                        <Section title="Informasi Umum">
                            {textField('name', 'Nama', {
                                required: true,
                                placeholder: 'Masukkan nama CV',
                            })}

                            <Field>
                                <Label required>Jenis Badan Usaha</Label>
                                <Select
                                    value={data.legal_type}
                                    onValueChange={(val) =>
                                        change('legal_type', val)
                                    }
                                >
                                    <SelectTrigger
                                        className="h-9 w-full"
                                        aria-invalid={!!errors.legal_type}
                                    >
                                        <SelectValue placeholder="Pilih Jenis" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {legalTypes.map((type) => (
                                            <SelectItem
                                                key={type.value}
                                                value={type.value}
                                                className="cursor-pointer"
                                            >
                                                {type.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError>{errors.legal_type}</FieldError>
                            </Field>

                            {textField('initial', 'Inisial', {
                                placeholder: 'Kosongkan untuk dibuat otomatis',
                            })}
                            {textField('npwp', 'NPWP', {
                                placeholder: '00.000.000.0-000.000',
                            })}

                            <Field className="md:col-span-2">
                                <label className="flex w-fit cursor-pointer items-center gap-2 text-sm">
                                    <Checkbox
                                        checked={data.is_pkp}
                                        onCheckedChange={(checked) =>
                                            change('is_pkp', checked === true)
                                        }
                                    />
                                    Pengusaha Kena Pajak (PKP)
                                </label>
                                <FieldError>{errors.is_pkp}</FieldError>
                            </Field>
                        </Section>

                        <Section title="Kontak & Alamat">
                            {textField('email', 'Email', {
                                placeholder: 'email@example.com',
                                inputMode: 'email',
                            })}

                            <Field>
                                <Label>Nomor Telepon</Label>
                                <div className="flex gap-2">
                                    <Select
                                        value={data.phone_number_country_code}
                                        onValueChange={(val) =>
                                            change(
                                                'phone_number_country_code',
                                                val,
                                            )
                                        }
                                    >
                                        <SelectTrigger className="h-9 w-24 shrink-0">
                                            <span>
                                                {data.phone_number_country_code}
                                            </span>
                                        </SelectTrigger>
                                        <SelectContent>
                                            {phoneCountryCodes.map((code) => (
                                                <SelectItem
                                                    key={code.value}
                                                    value={code.value}
                                                    className="cursor-pointer"
                                                >
                                                    {code.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <Input
                                        inputMode="numeric"
                                        placeholder="8123456789"
                                        aria-invalid={!!errors.phone_number}
                                        value={data.phone_number}
                                        onChange={(e) =>
                                            change(
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
                                    {errors.phone_number ??
                                        errors.phone_number_country_code}
                                </FieldError>
                            </Field>

                            <Field className="md:col-span-2">
                                <Label>Alamat</Label>
                                <Textarea
                                    placeholder="Alamat lengkap sesuai dokumen legal"
                                    aria-invalid={!!errors.full_address}
                                    value={data.full_address}
                                    onChange={(e) =>
                                        change('full_address', e.target.value)
                                    }
                                />
                                <FieldError>{errors.full_address}</FieldError>
                            </Field>

                            {textField('city', 'Kota')}
                            {textField('province', 'Provinsi')}
                            {textField('postal_code', 'Kode Pos', {
                                placeholder: '00000',
                                inputMode: 'numeric',
                            })}
                            {textField('country', 'Negara', {
                                placeholder: 'INDONESIA',
                            })}
                        </Section>

                        <Section title="Rekening Bank">
                            {textField('bank_name', 'Nama Bank', {
                                placeholder: 'BCA',
                            })}
                            {textField('bank_account_no', 'Nomor Rekening', {
                                inputMode: 'numeric',
                            })}
                            {textField(
                                'bank_account_name',
                                'Nama Pemilik Rekening',
                                { className: 'md:col-span-2' },
                            )}
                        </Section>
                    </div>

                    <DialogFooter>
                        <DialogClose asChild disabled={processing}>
                            <Button variant="outline">Batal</Button>
                        </DialogClose>
                        <Button disabled={processing} type="submit">
                            <Spinner className={processing ? '' : 'hidden'} />
                            Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
};
