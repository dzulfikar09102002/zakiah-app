import {
    Dialog,
    DialogCancel,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from '@/components/ui/dialog';

import {
    Field,
    FieldError,
    FieldLabel,
    FieldSet
} from '@/components/ui/field';

import { Input } from '../ui/input';
import { Button } from '../ui/button';
import { Spinner } from '../ui/spinner';

import { SubmitEventHandler, useEffect } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { toast } from 'sonner';

import { Customer } from '@/lib/model';
import { SharedData } from '@/types';
import customers from '@/routes/customers';


export type ModalState = {
    isOpen: boolean;
    dataId: any;
}

type Props = {
    modalState: ModalState,
    tableData: Customer[],
    onModalSuccess: () => void
    onModalClose: () => void
}

export default function CustomerModal({
    modalState,
    tableData,
    onModalSuccess,
    onModalClose
}: Props) {

    const { auth } = usePage<SharedData>().props

    const {
        processing,
        patch,
        post,
        reset,
        errors,
        data,
        setData
    } = useForm({
        first_name: '',
        last_name: '',
        entity_id: auth.user.entity.id
    })
    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault()

        const action = modalState.dataId ? patch : post
        const url = modalState.dataId
            ? customers.update(modalState.dataId).url
            : customers.store().url

        action(url, {
            only: ['pagination'],
            preserveState: true,
            onSuccess: () => {
                toast.success(
                    `Data berhasil ${modalState.dataId ? 'diperbarui' : 'ditambahkan'}`
                )
                onModalSuccess()
                reset()
            },
            onError: () => {
                toast.error(
                    `Gagal ${modalState.dataId ? 'memperbarui' : 'menambahkan'} data`
                )
            }
        })
    }
    useEffect(() => {
        if (!modalState.dataId) {
            reset()
            return
        }

        const selected = tableData.find(
            el => el.id == modalState.dataId
        )

        if (selected) {
            setData({
                first_name: selected.first_name,
                last_name: selected.last_name,
                entity_id: auth.user.entity.id
            })
        }
    }, [modalState])

    return (
        <Dialog
            open={modalState.isOpen}
            onOpenChange={() => {
                if (!processing) onModalClose()
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
                    <FieldSet className="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                    </FieldSet>
                    <DialogFooter>
                        <DialogClose asChild disabled={processing}>
                            <Button variant="outline">
                                Batal
                            </Button>
                        </DialogClose>

                        <Button disabled={processing} type="submit">
                            <Spinner className={processing ? '' : 'hidden'} />
                            Simpan
                        </Button>
                    </DialogFooter>

                </form>
            </DialogContent>
        </Dialog>
    )
}