import {
    Dialog,
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
import { Role } from '@/lib/model';
import { SharedData } from '@/types';
import roles from '@/routes/roles';

export type ModalState = {
    isOpen: boolean;
    dataId: any;
};

type Props = {
    modalState: ModalState;
    tableData: Role[];
    onModalSuccess: () => void;
    onModalClose: () => void;
};

type RoleFormData = {
    name: string;
    entity_id: number;
};

export default ({
    modalState,
    tableData,
    onModalSuccess,
    onModalClose,
}: Props) => {
    const { auth } = usePage<SharedData>().props;
    const { processing, patch, post, reset, errors, data, setData } =
        useForm<RoleFormData>({
            name: '',
            entity_id: auth.user.entity.id,
        });

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        const action = modalState.dataId ? patch : post;
        const url = modalState.dataId
            ? roles.update(modalState.dataId).url
            : roles.store().url;

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
        if (modalState.dataId) {
            const role = tableData.find((r) => r.id === modalState.dataId);
            if (role) {
                setData({
                    name: role.name,
                    entity_id: auth.user.entity.id,
                });
            }
        } else {
            setData({
                name: '',
                entity_id: auth.user.entity.id,
            });
        }
    }, [modalState.dataId, tableData]);

    return (
        <Dialog
            open={modalState.isOpen}
            onOpenChange={(open) => {
                if (!processing && !open) onModalClose();
            }}
        >
            <DialogContent asChild className="top-[40%]">
                <form onSubmit={submit}>
                    <DialogHeader>
                        <DialogTitle>
                            {modalState.dataId ? 'Edit Role' : 'Role Baru'}
                        </DialogTitle>
                    </DialogHeader>
                    <FieldSet>
                        <Field>
                            <FieldLabel htmlFor="name">Nama</FieldLabel>
                            <Input
                                readOnly={processing}
                                name="name"
                                id="name"
                                value={data.name}
                                onChange={(evt) =>
                                    setData({ ...data, name: evt.target.value })
                                }
                            />
                            <FieldError>{errors.name}</FieldError>
                        </Field>
                    </FieldSet>
                    <DialogFooter>
                        <DialogClose asChild disabled={processing}>
                            <Button variant="outline">Batal</Button>
                        </DialogClose>
                        <Button disabled={processing} type="submit">
                            <Spinner className={processing ? '' : 'hidden'} />{' '}
                            Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
};
