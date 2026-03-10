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
import { SubmitEventHandler, useEffect } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { toast } from 'sonner';
import { SharedData } from '@/types';
import ordertypes from '@/routes/order-types';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '../ui/select';
import { Role } from '@/lib/model';

export type ModalState = {
    isOpen: boolean;
    dataId: any;
};

type Props = {
    modalState: ModalState;
    tableData: any[];
    roles: Role[];
    onModalSuccess: () => void;
    onModalClose: () => void;
};

export default ({
    modalState,
    tableData,
    onModalSuccess,
    onModalClose,
    roles,
}: Props) => {
    const { auth } = usePage<SharedData>().props;

    const {
        processing,
        patch,
        post,
        reset,
        errors,
        data,
        setData,
        clearErrors,
    } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        role_id: '',
        password: '',
        password_confirmation: '',
    });

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
            <DialogContent asChild>
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
                        {/* Nama Depan */}
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

                        {/* Nama Belakang */}
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

                        {/* Email */}
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

                        {/* Role */}
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

                        {/* Password */}
                        <Field>
                            <FieldLabel>Password</FieldLabel>
                            <Input
                                type="password"
                                placeholder="Masukkan password"
                                readOnly={processing}
                                value={data.password}
                                onChange={(e) =>
                                    setData('password', e.target.value)
                                }
                            />
                            <FieldError>{errors.password}</FieldError>
                        </Field>

                        {/* Confirm Password */}
                        <Field>
                            <FieldLabel>Konfirmasi Password</FieldLabel>
                            <Input
                                type="password"
                                placeholder="Masukkan konfirmasi password"
                                readOnly={processing}
                                value={data.password_confirmation}
                                onChange={(e) =>
                                    setData(
                                        'password_confirmation',
                                        e.target.value,
                                    )
                                }
                            />
                            <FieldError>
                                {errors.password_confirmation}
                            </FieldError>
                        </Field>
                    </FieldSet>

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
