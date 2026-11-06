import { useForm } from '@inertiajs/react';
import type { SubmitEventHandler } from 'react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogCancel,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import accessRoles from '@/routes/access-roles';
import type { AccessRoleRow } from './types';

export type RoleFormState =
    | { mode: 'closed' }
    | { mode: 'create' }
    | { mode: 'edit'; role: AccessRoleRow };

type Props = {
    state: RoleFormState;
    onClose: () => void;
};

/** Tambah atau ubah role: cukup nama. Menu yang boleh dibuka diatur lewat tombol Hak Akses. */
export default function RoleFormDialog({ state, onClose }: Props) {
    const editing = state.mode === 'edit' ? state.role : null;

    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm({
            name: '',
            note: '',
            is_active: true,
        });

    useEffect(() => {
        if (state.mode === 'closed') {
            return;
        }

        clearErrors();

        if (editing) {
            setData({
                name: editing.name,
                note: editing.note ?? '',
                is_active: editing.is_active,
            });
        } else {
            reset();
        }
        // Hanya saat dialog dibuka.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [state]);

    const submit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(editing ? 'Role diperbarui' : 'Role ditambahkan');
                onClose();
            },
            onError: () => toast.error('Periksa kembali isian role'),
        };

        if (editing) {
            put(accessRoles.update(editing.id).url, options);
        } else {
            post(accessRoles.store().url, options);
        }
    };

    return (
        <Dialog
            open={state.mode !== 'closed'}
            onOpenChange={(open) => !open && !processing && onClose()}
        >
            <DialogContent asChild>
                <form onSubmit={submit}>
                    <DialogCancel />
                    <DialogHeader>
                        <DialogTitle>
                            {editing ? 'Edit Role' : 'Role Baru'}
                        </DialogTitle>
                        <DialogDescription>
                            {editing
                                ? 'Menu yang boleh dibuka diatur lewat tombol Hak Akses.'
                                : 'Setelah disimpan, atur menu yang boleh dibuka lewat tombol Hak Akses.'}
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <Field>
                            <FieldLabel htmlFor="role-name">Nama</FieldLabel>
                            <Input
                                id="role-name"
                                value={data.name}
                                readOnly={processing}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                className="h-11 text-base md:h-9 md:text-sm"
                            />
                            <FieldError>{errors.name}</FieldError>
                        </Field>
                        <Field>
                            <FieldLabel htmlFor="role-note">
                                Keterangan
                            </FieldLabel>
                            <Input
                                id="role-note"
                                value={data.note}
                                readOnly={processing}
                                placeholder="Opsional"
                                onChange={(event) =>
                                    setData('note', event.target.value)
                                }
                                className="h-11 text-base md:h-9 md:text-sm"
                            />
                            <FieldError>{errors.note}</FieldError>
                        </Field>
                        {!editing?.is_system && (
                            <Field orientation="horizontal">
                                <Switch
                                    id="role-active"
                                    checked={data.is_active}
                                    disabled={processing}
                                    onCheckedChange={(checked) =>
                                        setData('is_active', checked)
                                    }
                                />
                                <FieldLabel htmlFor="role-active">
                                    Aktif
                                </FieldLabel>
                            </Field>
                        )}
                    </div>

                    <DialogFooter className="mt-4">
                        <DialogClose asChild disabled={processing}>
                            <Button
                                type="button"
                                variant="outline"
                                className="h-11 md:h-9"
                            >
                                Batal
                            </Button>
                        </DialogClose>
                        <Button
                            type="submit"
                            disabled={processing}
                            className="h-11 md:h-9"
                        >
                            {processing && <Spinner />}
                            Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
