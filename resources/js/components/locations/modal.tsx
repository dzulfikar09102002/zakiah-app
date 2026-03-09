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

import { SubmitEventHandler, useEffect, useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { toast } from 'sonner';

import { Location } from '@/lib/model';
import { SharedData } from '@/types';


import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '../ui/select';
import locations from '@/routes/locations';

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
        backoffice_phone_number_country_code: '+62',
        backoffice_phone_number: '',
        kind: '',
        status: 'active',
        full_address: '',
        postal_code: '',
        district: '',
        city: '',
        province: '',
        country: '',
        footer: '',
    });
    useEffect(() => {
        if (!modalState.dataId) return;

        const selected = tableData.find(
            (item) => item.id === modalState.dataId,
        );

        if (!selected) return;

        setData({
            name: selected.name ?? '',
            contact_email: selected.contact_email ?? '',
            backoffice_email: selected.backoffice_email ?? '',
            backoffice_phone_number_country_code:
                selected.backoffice_phone_number_country_code ?? '+62',
            backoffice_phone_number: selected.backoffice_phone_number ?? '',
            kind: selected.kind ?? '',
            status: selected.status ?? 'active',
            full_address: selected.full_address ?? '',
            postal_code: selected.postal_code ?? '',
            district:selected.district ?? '',
            city: selected.city ?? '',
            province: selected.province ?? '',
            country: selected.country ?? '',
            footer: selected.footer ?? '',
        });
    }, [modalState.dataId]);

    const [provinces, setProvinces] = useState<any[]>([]);
    const [regencies, setRegencies] = useState<any[]>([]);
    const [districts, setDistricts] = useState<any[]>([]);

    const [selectedProvinceId, setSelectedProvinceId] = useState('');
    const [selectedRegencyId, setSelectedRegencyId] = useState('');
    const [selectedRegencyName, setSelectedRegencyName] = useState('');

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();
        console.log(data);
        const action = modalState.dataId ? patch : post;
        const url = modalState.dataId
            ? locations.update(modalState.dataId).url
            : locations.store().url;

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

    useEffect(() => {
        fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
            .then((res) => res.json())
            .then((data) => setProvinces(data));
    }, []);

    const fetchRegencies = (provinceId: string) => {
        fetch(
            `https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`,
        )
            .then((res) => res.json())
            .then((data) => setRegencies(data));
    };

    const fetchDistricts = (regencyId: string) => {
        fetch(
            `https://www.emsifa.com/api-wilayah-indonesia/api/districts/${regencyId}.json`,
        )
            .then((res) => res.json())
            .then((data) => setDistricts(data));
    };

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
                                placeholder="Store"
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
                                type="email"
                                placeholder="email@example.com"
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
                            type="email"
                                placeholder="email@example.com"
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
                                            data.backoffice_phone_number_country_code
                                        }
                                        onValueChange={(val) =>
                                            setData(
                                                'backoffice_phone_number_country_code',
                                                val,
                                            )
                                        }
                                    >
                                        <SelectTrigger className="h-9 w-full">
                                            <span>
                                                {
                                                    data.backoffice_phone_number_country_code
                                                }
                                            </span>
                                        </SelectTrigger>

                                        <SelectContent>
                                            {phoneCountryCodes.map((code) => (
                                                <SelectItem
                                                    key={code.value}
                                                    value={code.value}
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
                                        value={data.backoffice_phone_number}
                                        onChange={(e) =>
                                            setData(
                                                'backoffice_phone_number',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <FieldError>{errors.backoffice_phone_number}</FieldError>
                                </div>
                            </div>
                        </Field>

                        {/* Jenis Lokasi */}
                        <Field>
                            <FieldLabel>Jenis Lokasi</FieldLabel>
                            <Select
                                value={data.kind}
                                onValueChange={(val) =>
                                    setData(
                                        'kind',
                                        val as
                                            | 'main_office'
                                            | 'outlet'
                                            | 'warehouse',
                                    )
                                }
                            >
                                <SelectTrigger className="h-9 w-full">
                                    <SelectValue placeholder="Pilih Jenis" />
                                </SelectTrigger>

                                <SelectContent>
                                    <SelectItem value="main_office">
                                        Office
                                    </SelectItem>
                                    <SelectItem value="outlet">
                                        Outlet
                                    </SelectItem>
                                    <SelectItem value="warehouse">
                                        Gudang
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <FieldError>{errors.kind}</FieldError>
                        </Field>

                        {/* Alamat */}
                        <Field>
                            <FieldLabel>Alamat</FieldLabel>
                            <Input
                                placeholder="Alamat lengkap (Jalan/Gang/No. Rumah/RT/RW)"
                                value={data.full_address}
                                onChange={(e) =>
                                    setData('full_address', e.target.value)
                                }
                            />
                            <FieldError>{errors.full_address}</FieldError>
                        </Field>

                        {/* Kode Pos */}
                        <Field>
                            <FieldLabel>Kode Pos</FieldLabel>
                            <Input
                                placeholder="00000"
                                value={data.postal_code}
                                onChange={(e) =>
                                    setData('postal_code', e.target.value)
                                }
                            />
                            <FieldError>{errors.postal_code}</FieldError>
                        </Field>

                        {/* PROVINSI */}
                        <Field>
                            <FieldLabel>Provinsi</FieldLabel>

                            <Select
                                onValueChange={(val) => {
                                    setSelectedProvinceId(val);

                                    const province = provinces.find(
                                        (p) => p.id === val,
                                    );

                                    setData('province', province?.name || '');
                                    setData('country', 'INDONESIA');

                                    fetchRegencies(val);
                                }}
                            >
                                <SelectTrigger className="h-9 w-full">
                                    <SelectValue placeholder="Pilih Provinsi" />
                                </SelectTrigger>

                                <SelectContent>
                                    {provinces.map((prov) => (
                                        <SelectItem
                                            key={prov.id}
                                            value={prov.id}
                                        >
                                            {prov.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <FieldError>{errors.province}</FieldError>
                        </Field>

                        {/* KABUPATEN */}
                        <Field>
                            <FieldLabel>Kabupaten / Kota</FieldLabel>

                            <Select
                                value={selectedRegencyId}
                                disabled={!selectedProvinceId}
                                onValueChange={(val) => {
                                    setSelectedRegencyId(val);

                                    const regency = regencies.find(
                                        (r) => r.id === val,
                                    );

                                    setSelectedRegencyName(regency?.name || '');

                                    setData('city', regency?.name || ''); // isi city

                                    fetchDistricts(val);
                                }}
                            >
                                <SelectTrigger className="h-9 w-full">
                                    <SelectValue
                                        placeholder={
                                            !selectedProvinceId
                                                ? 'Pilih provinsi dulu'
                                                : 'Pilih Kabupaten / Kota'
                                        }
                                    />
                                </SelectTrigger>

                                <SelectContent>
                                    {regencies.map((reg) => (
                                        <SelectItem key={reg.id} value={reg.id}>
                                            {reg.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <FieldError>{errors.city}</FieldError>
                        </Field>

                        {/* KECAMATAN */}
                        <Field>
                            <FieldLabel>Kecamatan</FieldLabel>

                            <Select
                                disabled={!selectedRegencyId}
                                onValueChange={(val) => {
                                    const district = districts.find(
                                        (d) => d.id === val,
                                    );

                                    setData('district', district?.name || ''); // isi district
                                }}
                            >
                                <SelectTrigger className="h-9 w-full">
                                    <SelectValue
                                        placeholder={
                                            !selectedRegencyId
                                                ? 'Pilih kabupaten dulu'
                                                : 'Pilih Kecamatan'
                                        }
                                    />
                                </SelectTrigger>

                                <SelectContent>
                                    {districts.map((dist) => (
                                        <SelectItem key={dist.id} value={dist.id}>
                                            {dist.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <FieldError>{errors.district}</FieldError>
                        </Field>
                        {/* Country */}
                        <Field>
                            <FieldLabel>Negara</FieldLabel>
                            <Input
                                placeholder="Indonesia"
                                value={data.country}
                                readOnly
                            />
                            <FieldError>{errors.country}</FieldError>
                        </Field>

                        {/* Footer */}
                        <Field>
                            <FieldLabel>Footer</FieldLabel>
                            <Input
                                placeholder="IG: secaca.idsedangcantikcantiknya Phone: 08224444890"
                                value={data.footer}
                                onChange={(e) =>
                                    setData('footer', e.target.value)
                                }
                            />
                            <FieldError>{errors.footer}</FieldError>
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
