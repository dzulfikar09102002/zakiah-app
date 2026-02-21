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

import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { SubmitEventHandler, useEffect } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { toast } from 'sonner';
import { SharedData } from '@/types';
import paymentMethods from '@/routes/payment-methods';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';

export type ModalState = {
    isOpen: boolean;
    dataId: number | null;
};

type Props = {
    modalState: ModalState;
    tableData: any[];
    onModalSuccess: () => void;
    onModalClose: () => void;
};

export default function PaymentMethodModal({
    modalState,
    tableData,
    onModalSuccess,
    onModalClose
}: Props) {

    const { auth } = usePage<SharedData>().props;

    const {
        processing,
        patch,
        post,
        reset,
        errors,
        data,
        setData,
        clearErrors
    } = useForm({
        name: '',
        kind: 'cash',
        fixed_fee: 0,
        variable_fee: 0,
        entity_id: auth.user.entity.id,
    });

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        const action = modalState.dataId ? patch : post;
        const url = modalState.dataId
            ? paymentMethods.update(modalState.dataId).url
            : paymentMethods.store().url;

        action(url, {
            preserveState: true,
            onSuccess: () => {
                toast.success(`Data berhasil ${modalState.dataId ? 'diperbarui' : 'ditambahkan'}`);
                onModalSuccess();
                reset();
            },
            onError: () => {
                toast.error(`Gagal ${modalState.dataId ? 'memperbarui' : 'menambahkan'} data`);
            }
        });
    };

    useEffect(() => {
        const existing = tableData.find(el => el.id === modalState.dataId);

        if (existing) {
            setData({
                name: existing.name,
                kind: existing.kind,
                fixed_fee: existing.fixed_fee,
                variable_fee: existing.variable_fee,
                entity_id: existing.entity_id
            });
        } else {
            reset();
            setData('entity_id', auth.user.entity.id);
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
                            {modalState.dataId ? 'Edit Metode Pembayaran' : 'Metode Pembayaran Baru'}
                        </DialogTitle>
                    </DialogHeader>
                    <FieldSet>
                        <Field>
                            <FieldLabel htmlFor="name">Nama</FieldLabel>
                            <Input
                                id="name"
                                name="name"
                                readOnly={processing}
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                            />
                            <FieldError>{errors.name}</FieldError>
                        </Field>
                        <Field>
                            <FieldLabel htmlFor="kind">Jenis Metode Pembayaran</FieldLabel>
                            <Select
                                value={data.kind}
                                onValueChange={(val) => setData('kind', val)}
                                disabled={processing}
                            >
                                <SelectTrigger className="h-9 w-full">
                                    <SelectValue placeholder="Pilih jenis metode" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="cash">Cash</SelectItem>
                                    <SelectItem value="debit">Debit</SelectItem>
                                    <SelectItem value="credit_card">Credit Card</SelectItem>
                                    <SelectItem value="qris">QRIS</SelectItem>
                                    <SelectItem value="online_payment">Online Payment</SelectItem>
                                    <SelectItem value="va">Virtual Account</SelectItem>
                                </SelectContent>
                            </Select>
                            <FieldError>{errors.kind}</FieldError>
                        </Field>
                        <Field>
                            <FieldLabel htmlFor="fixed_fee">Biaya Tetap (Rp)</FieldLabel>
                            <Input
                                type="number"
                                min="0"
                                step="0.01"
                                readOnly={processing}
                                value={data.fixed_fee}
                                onChange={e => setData('fixed_fee', Number(e.target.value))}
                            />
                            <FieldError>{errors.fixed_fee}</FieldError>
                        </Field>

                        {/* Biaya Persen */}
                        <Field>
                            <FieldLabel htmlFor="variable_fee">Biaya (%)</FieldLabel>
                            <Input
                                type="number"
                                min="0"
                                step="0.01"
                                readOnly={processing}
                                value={data.variable_fee}
                                onChange={e => setData('variable_fee', Number(e.target.value))}
                            />
                            <FieldError>{errors.variable_fee}</FieldError>
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
}
