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

import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { SubmitEventHandler, useEffect, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import ordertypes from '@/routes/order-types';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '../ui/select';
import { Role, Location } from '@/lib/model';
import { Eye, EyeOff, Plus, Save, X } from 'lucide-react';

export type ModalState = {
    isOpen: boolean;
    dataId: any;
};

type LocationRoleRow = {
    location: string;
    role: string;
    saved: boolean;
};

type Props = {
    modalState: ModalState;
    tableData: any[];
    roles: Role[];
    locations: Location[];
    onModalSuccess: () => void;
    onModalClose: () => void;
};

export default ({
    modalState,
    tableData,
    onModalSuccess,
    onModalClose,
    roles,
    locations,
}: Props) => {
    const {
        processing,
        patch,
        post,
        reset,
        errors,
        data,
        setData,
        clearErrors,
        setError,
    } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        role_id: '',
        password: '',
        password_confirmation: '',
    });

    const [locationRoles, setLocationRoles] = useState<LocationRoleRow[]>([
        { location: '', role: '', saved: false },
    ]);

    const addLocation = () => {
        setLocationRoles((prev) => [
            ...prev,
            { location: '', role: '', saved: false },
        ]);
    };
    const validatePassword = (password: string, confirmation: string) => {
        if (confirmation && password !== confirmation) {
            setError('password_confirmation', 'Konfirmasi password tidak sama');
        } else {
            clearErrors('password_confirmation');
        }
    };
    const removeLocation = (index: number) => {
        setLocationRoles((prev) => prev.filter((_, i) => i !== index));
    };

    const saveRow = (index: number) => {
        setLocationRoles((prev) => {
            const updated = [...prev];
            updated[index].saved = true;
            return updated;
        });
    };

    const updateLocation = (
        index: number,
        key: 'location' | 'role',
        value: string,
    ) => {
        setLocationRoles((prev) => {
            const updated = [...prev];
            updated[index] = { ...updated[index], [key]: value };
            return updated;
        });
    };

    const getLocationName = (id: string) =>
        locations.find((l) => String(l.id) === id)?.name ?? '-';

    const [showPassword, setShowPassword] = useState(false);

    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const getRoleName = (id: string) =>
        roles.find((r) => String(r.id) === id)?.name ?? '-';

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        const action = modalState.dataId ? patch : post;
        const url = modalState.dataId
            ? ordertypes.update(modalState.dataId).url
            : ordertypes.store().url;

        action(url, {
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
        const existing = tableData.find((el) => el.id === modalState.dataId);

        if (existing) {
            setData({
                first_name: existing.first_name ?? '',
                last_name: existing.last_name ?? '',
                email: existing.email ?? '',
                role_id: String(existing.role_id ?? ''),
                password: '',
                password_confirmation: '',
            });
        } else {
            reset();
        }
    }, [modalState.dataId]);

    return (
        <Dialog
            open={modalState.isOpen}
            onOpenChange={(open) => {
                if (!open && !processing) {
                    clearErrors();
                    onModalClose();
                }
            }}
        >
            <DialogContent
                asChild
                className="sm:max-w-2xl md:max-w-3xl lg:max-w-4xl"
            >
                <form onSubmit={submit}>
                    <DialogCancel />
                    <DialogHeader>
                        <DialogTitle>
                            {modalState.dataId
                                ? 'Edit Karyawan'
                                : 'Karyawan Baru'}
                        </DialogTitle>
                    </DialogHeader>
                    <FieldSet className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <Field>
                            <FieldLabel>Nama Depan</FieldLabel>
                            <Input
                                placeholder="Masukkan nama depan"
                                readOnly={processing}
                                value={data.first_name}
                                onChange={(e) =>
                                    setData('first_name', e.target.value)
                                }
                            />
                            <FieldError>{errors.first_name}</FieldError>
                        </Field>

                        <Field>
                            <FieldLabel>Nama Belakang</FieldLabel>
                            <Input
                                placeholder="Masukkan nama belakang"
                                readOnly={processing}
                                value={data.last_name}
                                onChange={(e) =>
                                    setData('last_name', e.target.value)
                                }
                            />
                            <FieldError>{errors.last_name}</FieldError>
                        </Field>

                        <Field>
                            <FieldLabel>Email</FieldLabel>
                            <Input
                                type="email"
                                placeholder="Masukkan email"
                                readOnly={processing}
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                            />
                            <FieldError>{errors.email}</FieldError>
                        </Field>

                        <Field>
                            <FieldLabel>Role</FieldLabel>
                            <Select
                                value={data.role_id}
                                onValueChange={(val) => setData('role_id', val)}
                                disabled={processing}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Role" />
                                </SelectTrigger>
                                <SelectContent>
                                    {roles.map((r) => (
                                        <SelectItem
                                            key={r.id}
                                            value={String(r.id)}
                                        >
                                            {r.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <FieldError>{errors.role_id}</FieldError>
                        </Field>

                        <Field>
                            <FieldLabel>Password</FieldLabel>
                            <div className="relative">
                                <Input
                                    type={showPassword ? 'text' : 'password'}
                                    placeholder="Masukkan password"
                                    readOnly={processing}
                                    value={data.password}
                                    onChange={(e) => {
                                        const val = e.target.value;
                                        setData('password', val);
                                        validatePassword(
                                            val,
                                            data.password_confirmation,
                                        );
                                    }}
                                    className="pr-10"
                                />
                                <button
                                    type="button"
                                    onClick={() =>
                                        setShowPassword(!showPassword)
                                    }
                                    className="absolute top-1/2 right-2 -translate-y-1/2 text-muted-foreground"
                                >
                                    {showPassword ? (
                                        <EyeOff className="h-4 w-4" />
                                    ) : (
                                        <Eye className="h-4 w-4" />
                                    )}
                                </button>
                            </div>
                            <FieldError>{errors.password}</FieldError>
                        </Field>
                        <Field>
                            <FieldLabel>Konfirmasi Password</FieldLabel>
                            <div className="relative">
                                <Input
                                    type={
                                        showConfirmPassword
                                            ? 'text'
                                            : 'password'
                                    }
                                    placeholder="Masukkan konfirmasi password"
                                    readOnly={processing}
                                    value={data.password_confirmation}
                                    onChange={(e) => {
                                        const val = e.target.value;
                                        setData('password_confirmation', val);
                                        validatePassword(data.password, val);
                                    }}
                                    className="pr-10"
                                />
                                <button
                                    type="button"
                                    onClick={() =>
                                        setShowConfirmPassword(
                                            !showConfirmPassword,
                                        )
                                    }
                                    className="absolute top-1/2 right-2 -translate-y-1/2 text-muted-foreground"
                                >
                                    {showConfirmPassword ? (
                                        <EyeOff className="h-4 w-4" />
                                    ) : (
                                        <Eye className="h-4 w-4" />
                                    )}
                                </button>
                            </div>
                            <FieldError>
                                {errors.password_confirmation}
                            </FieldError>
                        </Field>
                    </FieldSet>
                    <div className="mt-3 space-y-3">
                        <Button
                            type="button"
                            variant="secondary"
                            size="sm"
                            onClick={addLocation}
                        >
                            <Plus />
                            Tambah Lokasi & Role
                        </Button>

                        <div className="overflow-hidden rounded-lg border">
                            {/* Header */}
                            <div className="grid grid-cols-12 gap-3 border-b bg-muted/30 px-3 py-2 text-sm font-medium text-muted-foreground">
                                <div className="col-span-5">Lokasi</div>
                                <div className="col-span-5">Role</div>
                                <div className="col-span-2 text-center">
                                    Aksi
                                </div>
                            </div>

                            {/* Rows */}
                            {locationRoles.map((item, index) => (
                                <div
                                    key={index}
                                    className="grid grid-cols-12 items-center gap-3 border-b px-3 py-2 last:border-b-0"
                                >
                                    {/* Lokasi */}
                                    <div className="col-span-5">
                                        {item.saved ? (
                                            <div className="text-sm">
                                                {getLocationName(item.location)}
                                            </div>
                                        ) : (
                                            <Select
                                                value={item.location}
                                                onValueChange={(val) =>
                                                    updateLocation(
                                                        index,
                                                        'location',
                                                        val,
                                                    )
                                                }
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Pilih Lokasi" />
                                                </SelectTrigger>
                                                <SelectContent className="max-h-60 overflow-y-auto">
                                                    {locations.map((r) => (
                                                        <SelectItem
                                                            key={r.id}
                                                            value={String(r.id)}
                                                        >
                                                            {r.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        )}
                                    </div>

                                    {/* Role */}
                                    <div className="col-span-5">
                                        {item.saved ? (
                                            <div className="text-sm">
                                                {getRoleName(item.role)}
                                            </div>
                                        ) : (
                                            <Select
                                                value={item.role}
                                                onValueChange={(val) =>
                                                    updateLocation(
                                                        index,
                                                        'role',
                                                        val,
                                                    )
                                                }
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Pilih Role" />
                                                </SelectTrigger>
                                                <SelectContent className="max-h-60 overflow-y-auto">
                                                    {roles.map((r) => (
                                                        <SelectItem
                                                            key={r.id}
                                                            value={String(r.id)}
                                                        >
                                                            {r.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        )}
                                    </div>

                                    {/* Aksi */}
                                    <div className="col-span-2 flex justify-center gap-2">
                                        {!item.saved ? (
                                            <>
                                                <Button
                                                    type="button"
                                                    size="icon"
                                                    variant="default"
                                                    disabled={
                                                        !item.location ||
                                                        !item.role
                                                    }
                                                    onClick={() =>
                                                        saveRow(index)
                                                    }
                                                >
                                                    <Save className="h-4 w-4" />
                                                </Button>

                                                <Button
                                                    type="button"
                                                    size="icon"
                                                    variant="destructive"
                                                    onClick={() =>
                                                        removeLocation(index)
                                                    }
                                                >
                                                    <X className="h-4 w-4" />
                                                </Button>
                                            </>
                                        ) : (
                                            <Button
                                                type="button"
                                                size="icon"
                                                variant="outline"
                                                onClick={() =>
                                                    removeLocation(index)
                                                }
                                            >
                                                <X className="h-4 w-4 text-red-500" />
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <DialogFooter>
                        <DialogClose asChild disabled={processing}>
                            <Button variant="outline">Batal</Button>
                        </DialogClose>

                        <Button disabled={processing} type="submit">
                            <Spinner className={processing ? '' : 'hidden'} />
                            {modalState.dataId ? 'Simpan' : 'Tambah'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
};
