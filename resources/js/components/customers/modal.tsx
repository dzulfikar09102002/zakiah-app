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
    tableData: Customer[];
    customerCategories: CustomerCategory[];
    phoneCountryCodes: {
        value: string;
        label: string;
    }[];
    locations: Location[];
    onModalSuccess: () => void;
    onModalClose: () => void;
};

export default ({
    modalState,
    tableData,
    customerCategories,
    onModalSuccess,
    onModalClose,
    phoneCountryCodes,
    locations,
}: Props) => {
    const { auth } = usePage<SharedData>().props;

    const { processing, patch, post, reset, errors, data, setData } = useForm({
        first_name: '',
        last_name: '',
        customer_category_id: '',
        location_id: '',
        phone_number_country_code: '+62',
        phone_number: '',
        entity_id: auth.user.entity.id,
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
            const countryCode = selected.phone_number_country_code ?? '62';
            setData({
                first_name: selected.first_name ?? '',
                last_name: selected.last_name ?? '',
                customer_category_id: String(
                    selected.customer_category_id ?? '',
                ),
                location_id: String(selected.location_id ?? ''),
                phone_number_country_code: countryCode.startsWith('+')
                    ? countryCode
                    : `+${countryCode}`,
                phone_number: selected.phone_number ?? '',
                entity_id: auth.user.entity.id,
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
            <DialogContent asChild>
                <form onSubmit={submit}>
                    <DialogCancel />
                    <DialogHeader>
                        <DialogTitle>
                            {modalState.dataId
                                ? 'Edit Pelanggan'
                                : 'Pelanggan Baru'}
                        </DialogTitle>
                    </DialogHeader>
                    <FieldSet className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <Field>
                            <FieldLabel htmlFor="first_name">
                                Nama Depan
                            </FieldLabel>
                            <Input
                                id="first_name"
                                name="first_name"
                                readOnly={processing}
                                value={data.first_name}
                                onChange={(e) =>
                                    setData('first_name', e.target.value)
                                }
                            />
                            <FieldError>{errors.first_name}</FieldError>
                        </Field>
                        <Field>
                            <FieldLabel htmlFor="last_name">
                                Nama Belakang
                            </FieldLabel>
                            <Input
                                id="last_name"
                                name="last_name"
                                readOnly={processing}
                                value={data.last_name}
                                onChange={(e) =>
                                    setData('last_name', e.target.value)
                                }
                            />
                            <FieldError>{errors.last_name}</FieldError>
                        </Field>
                        <Field>
                            <FieldLabel>Kategori Pelanggan</FieldLabel>
                            <Select
                                value={data.customer_category_id || undefined}
                                onValueChange={(val) =>
                                    setData('customer_category_id', val)
                                }
                                disabled={processing}
                            >
                                <SelectTrigger className="h-9 w-full">
                                    <SelectValue placeholder="Pilih Kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    {customerCategories.map((cat) => (
                                        <SelectItem
                                            key={cat.id}
                                            value={String(cat.id)}
                                        >
                                            {cat.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <FieldError>
                                {errors.customer_category_id}
                            </FieldError>
                        </Field>
                        <Field>
                            <FieldLabel>Asal Toko</FieldLabel>
                            <Select
                                value={data.location_id || undefined}
                                onValueChange={(val) =>
                                    setData('location_id', val)
                                }
                                disabled={processing}
                            >
                                <SelectTrigger className="h-9 w-full">
                                    <SelectValue placeholder="Pilih Toko" />
                                </SelectTrigger>

                                <SelectContent>
                                    {locations.map((loc) => (
                                        <SelectItem
                                            key={loc.id}
                                            value={String(loc.id)}
                                        >
                                            {loc.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <FieldError>{errors.location_id}</FieldError>
                        </Field>

                        <Field className="md:col-span-2">
                            <FieldLabel>Nomor Telepon</FieldLabel>

                            <div className="grid grid-cols-5 gap-2">
                                <div className="col-span-1">
                                    <Select
                                        value={data.phone_number_country_code}
                                        onValueChange={(val) =>
                                            setData(
                                                'phone_number_country_code',
                                                val,
                                            )
                                        }
                                        disabled={processing}
                                    >
                                        <SelectTrigger className="h-9 w-full">
                                            <span>
                                                {data.phone_number_country_code}
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
                                        className="h-9"
                                        placeholder="8123456789"
                                        type="number"
                                        value={data.phone_number}
                                        onChange={(e) =>
                                            setData(
                                                'phone_number',
                                                e.target.value,
                                            )
                                        }
                                        disabled={processing}
                                    />
                                </div>
                            </div>

                            <FieldError>{errors.phone_number}</FieldError>
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
