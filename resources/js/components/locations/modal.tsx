import {
    Dialog,
    DialogCancel,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';

import { Input } from '../ui/input';
import { Button } from '../ui/button';
import { Spinner } from '../ui/spinner';

import { SubmitEventHandler, useEffect } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { toast } from 'sonner';

import { Customer, CustomerCategory, Location } from '@/lib/model';
import { SharedData } from '@/types';
import customers from '@/routes/customers';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '../ui/select';

export type ModalState = {
    isOpen: boolean;
    dataId: any;
};

type Props = {
    modalState: ModalState;
    tableData: Location[];
    phoneCountryCodes: {
        value: string;
        label: string;
    }[];
    onModalSuccess: () => void;
    onModalClose: () => void;
};

export default ({
    modalState,
    tableData,
    onModalSuccess,
    onModalClose,
    phoneCountryCodes,
}: Props) => {
    const { auth } = usePage<SharedData>().props;

    const { processing, patch, post, reset, errors, data, setData } = useForm({
        name: '',
        contact_email: '',
        backoffice_email: '',
        contact_phone_number_country_code: '+62',
        contact_phone_number: '',
        kind: '',
        status: 'active',
        full_address: '',
        postal_code: '',
        city: '',
        province: '',
        country: '',
        footer: '',
    });
    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        const action = modalState.dataId ? patch : post;
        const url = modalState.dataId
            ? customers.update(modalState.dataId).url
            : customers.store().url;

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
                    `Gagal ${modalState.dataId ? 'memperbarui' : 'menambahkan'} data`,
                );
            },
        });
    };
    useEffect(() => {
        if (!modalState.dataId) {
            reset();
            return;
        }

        const selected = tableData.find((el) => el.id == modalState.dataId);

        if (selected) {
            const countryCode =
                selected.contact_phone_number_country_code ?? '+62';

            setData({
                name: selected.name ?? '',
                contact_email: selected.contact_email ?? '',
                backoffice_email: selected.backoffice_email ?? '',
                contact_phone_number_country_code: countryCode.startsWith('+')
                    ? countryCode
                    : `+${countryCode}`,
                contact_phone_number: selected.contact_phone_number ?? '',
                kind: selected.kind ?? '',
                status: selected.status ?? 'active',
                full_address: selected.full_address ?? '',
                postal_code: selected.postal_code ?? '',
                city: selected.city ?? '',
                province: selected.province ?? '',
                country: selected.country ?? '',
                footer: selected.footer ?? '',
            });
        }
    }, [modalState]);

    return (
        <Dialog
            open={modalState.isOpen}
            onOpenChange={() => {
                if (!processing) onModalClose();
            }}
        >
            <DialogContent
                asChild
                className="sm:max-w-4xl md:max-w-5xl lg:max-w-6xl"
            >
                <form onSubmit={submit}>
                    <DialogCancel />
                    <DialogHeader>
                        <DialogTitle>
                            {modalState.dataId ? 'Edit Lokasi' : 'Lokasi Baru'}
                        </DialogTitle>
                    </DialogHeader>
                    <FieldSet className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        {/* Nama */}
                        <Field>
                            <FieldLabel>Nama</FieldLabel>
                            <Input
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                            />
                            <FieldError>{errors.name}</FieldError>
                        </Field>

                        {/* Backoffice Email */}
                        <Field>
                            <FieldLabel>Backoffice Email</FieldLabel>
                            <Input
                                value={data.backoffice_email}
                                onChange={(e) =>
                                    setData('backoffice_email', e.target.value)
                                }
                            />
                            <FieldError>{errors.backoffice_email}</FieldError>
                        </Field>

                        {/* Contact Email */}
                        <Field>
                            <FieldLabel>Contact Email</FieldLabel>
                            <Input
                                value={data.contact_email}
                                onChange={(e) =>
                                    setData('contact_email', e.target.value)
                                }
                            />
                            <FieldError>{errors.contact_email}</FieldError>
                        </Field>

                        {/* Nomor Telepon */}
                        <Field>
                            <FieldLabel>Nomor Telepon</FieldLabel>

                            <div className="grid grid-cols-5 gap-2">
                                <div className="col-span-1">
                                    <Select
                                        value={
                                            data.contact_phone_number_country_code
                                        }
                                        onValueChange={(val) =>
                                            setData(
                                                'contact_phone_number_country_code',
                                                val,
                                            )
                                        }
                                    >
                                        <SelectTrigger className="h-9 w-full">
                                            <span>
                                                {
                                                    data.contact_phone_number_country_code
                                                }
                                            </span>
                                        </SelectTrigger>

                                        <SelectContent>
                                            {phoneCountryCodes.map((code) => (
                                                <SelectItem
                                                    key={code.value}
                                                    value={code.value}
                                                    className={
                                                        code.value === '+62'
                                                            ? 'font-semibold'
                                                            : ''
                                                    }
                                                >
                                                    {code.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="col-span-4">
                                    <Input
                                        type="number"
                                        placeholder="8123456789"
                                        value={data.contact_phone_number}
                                        onChange={(e) =>
                                            setData(
                                                'contact_phone_number',
                                                e.target.value,
                                            )
                                        }
                                    />
                                </div>
                            </div>

                            <FieldError>
                                {errors.contact_phone_number}
                            </FieldError>
                        </Field>

                        {/* Jenis Lokasi */}
                        <Field>
                            <FieldLabel>Jenis Lokasi</FieldLabel>
                            <Input
                                value={data.kind}
                                onChange={(e) =>
                                    setData('kind', e.target.value)
                                }
                            />
                        </Field>

                        {/* Status */}
                        <Field>
                            <FieldLabel>Status</FieldLabel>
                            <Select
                                value={data.status}
                                onValueChange={(val) =>
                                    setData(
                                        'status',
                                        val as 'active' | 'inactive',
                                    )
                                }
                            >
                                <SelectTrigger className="h-9 w-full">
                                    <SelectValue placeholder="Pilih Status" />
                                </SelectTrigger>

                                <SelectContent>
                                    <SelectItem value="active">
                                        Active
                                    </SelectItem>
                                    <SelectItem value="inactive">
                                        Inactive
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </Field>

                        {/* Alamat */}
                        <Field>
                            <FieldLabel>Alamat</FieldLabel>
                            <Input
                                value={data.full_address}
                                onChange={(e) =>
                                    setData('full_address', e.target.value)
                                }
                            />
                        </Field>

                        {/* Kode Pos */}
                        <Field>
                            <FieldLabel>Kode Pos</FieldLabel>
                            <Input
                                value={data.postal_code}
                                onChange={(e) =>
                                    setData('postal_code', e.target.value)
                                }
                            />
                        </Field>

                        {/* Kota */}
                        <Field>
                            <FieldLabel>Kota</FieldLabel>
                            <Input
                                value={data.city}
                                onChange={(e) =>
                                    setData('city', e.target.value)
                                }
                            />
                        </Field>

                        {/* Provinsi */}
                        <Field>
                            <FieldLabel>Provinsi</FieldLabel>
                            <Input
                                value={data.province}
                                onChange={(e) =>
                                    setData('province', e.target.value)
                                }
                            />
                        </Field>

                        {/* Negara */}
                        <Field>
                            <FieldLabel>Negara</FieldLabel>
                            <Input
                                value={data.country}
                                onChange={(e) =>
                                    setData('country', e.target.value)
                                }
                            />
                        </Field>

                        {/* Footer */}
                        <Field>
                            <FieldLabel>Footer</FieldLabel>
                            <Input
                                value={data.footer}
                                onChange={(e) =>
                                    setData('footer', e.target.value)
                                }
                            />
                        </Field>
                    </FieldSet>
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
