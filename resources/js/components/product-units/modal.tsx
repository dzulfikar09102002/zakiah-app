import { useForm, usePage } from '@inertiajs/react';
import type { SubmitEventHandler} from 'react';
import { useEffect } from 'react';
import { toast } from 'sonner';
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
import type { Category } from '@/lib/model';
import productUnits from '@/routes/product-units';
import type { SharedData } from '@/types';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Spinner } from '../ui/spinner';


export type ModalState = {
    isOpen: boolean;
    dataId: unknown;
}

type Props = {
    modalState: ModalState,
    tableData: Category[],
    onModalSuccess: () => void
    onModalClose: () => void
}

export default ({ modalState, tableData, onModalSuccess, onModalClose }: Props) => {
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
        name: '',
        entity_id: auth.user.entity.id
    })

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        const action = modalState.dataId ? patch : post;
        const url = modalState.dataId ? productUnits.update(Number(modalState.dataId)).url : productUnits.store().url;

        action(url, {
            only: ['pagination'],
            preserveState: true,
            onSuccess: () => {
                toast.success(`Data berhasil ${modalState.dataId ? 'diperbarui' : 'ditambahkan'}`)
                onModalSuccess()
                reset();
            },
            onError: () => {
                toast.error(`Gagal ${modalState.dataId ? 'memperbarui' : 'menambahkan'} data`)
            }
        });
    };

    useEffect(() => {
        setData({ ...data, name: tableData.find(el => el.id == modalState.dataId)?.name || '' })
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
                        <DialogTitle>{modalState.dataId ? 'Edit Kategori' : 'Kategori Baru'}</DialogTitle>
                    </DialogHeader>
                    <FieldSet>
                        <Field>
                            <FieldLabel htmlFor='name'>Nama</FieldLabel>
                            <Input
                                readOnly={processing}
                                name='name'
                                id='name'
                                value={data.name}
                                onChange={evt => setData({ ...data, name: evt.target.value })}
                            />
                            <FieldError>{errors.name}</FieldError>
                        </Field>
                    </FieldSet>
                    <DialogFooter>
                        <DialogClose asChild disabled={processing}>
                            <Button variant={'outline'}>Batal</Button>
                        </DialogClose>
                        <Button disabled={processing} type='submit'>
                            <Spinner className={processing ? '' : 'hidden'} /> Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    )
}