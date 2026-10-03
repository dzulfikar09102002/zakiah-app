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
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import type { Tax } from '@/lib/model';
import taxes from '@/routes/taxes';

export type ModalState = {
    isOpen: boolean;
    dataId?: number;
};

type Props = {
    modalState: ModalState;
    tableData: Tax[];
    onModalSuccess: () => void;
    onModalClose: () => void;
};

export default ({
    modalState,
    tableData,
    onModalSuccess,
    onModalClose,
}: Props) => {
    const { processing, put, post, reset, errors, data, setData, clearErrors } =
        useForm({
            name: '',
            rate: 0 as number | '',
        });

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        const action = modalState.dataId ? put : post;
        const url = modalState.dataId
            ? taxes.update(modalState.dataId).url
            : taxes.store().url;

        action(url, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    `Pajak berhasil ${modalState.dataId ? 'diperbarui' : 'ditambahkan'}`,
                );
                onModalSuccess();
                reset();
            },
            onError: () => {
                toast.error(
                    `Gagal ${modalState.dataId ? 'memperbarui' : 'menambahkan'} pajak`,
                );
            },
        });
    };

    useEffect(() => {
        const existing = tableData.find((el) => el.id === modalState.dataId);

        if (existing) {
            setData({ name: existing.name, rate: existing.rate });
        } else {
            reset();
        }
        clearErrors();
    }, [modalState.dataId, modalState.isOpen]);

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
                            {modalState.dataId ? 'Edit Pajak' : 'Pajak Baru'}
                        </DialogTitle>
                    </DialogHeader>

                    <FieldSet className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <Field>
                            <FieldLabel htmlFor="name">Nama</FieldLabel>
                            <Input
                                id="name"
                                placeholder="Masukkan nama"
                                readOnly={processing}
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                            />
                            <FieldError>{errors.name}</FieldError>
                        </Field>

                        <Field>
                            <FieldLabel htmlFor="rate">Besaran (%)</FieldLabel>
                            <Input
                                id="rate"
                                type="number"
                                min="0"
                                max="100"
                                placeholder="0"
                                readOnly={processing}
                                value={data.rate}
                                onChange={(e) =>
                                    setData(
                                        'rate',
                                        e.target.value === ''
                                            ? ''
                                            : Number(e.target.value),
                                    )
                                }
                            />
                            <FieldError>{errors.rate}</FieldError>
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
