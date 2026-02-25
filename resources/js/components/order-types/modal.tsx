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
import ordertypes from '@/routes/order-types';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '../ui/select';
import { PaymentMethod } from '@/lib/model';

export type ModalState = {
    isOpen: boolean;
    dataId: any;
};

type Props = {
    modalState: ModalState;
    tableData: any[];
    paymentMethods: PaymentMethod[];
    onModalSuccess: () => void;
    onModalClose: () => void;
};

export default ({
    modalState,
    tableData,
    paymentMethods,
    onModalSuccess,
    onModalClose
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
        clearErrors
    } = useForm({
        name: '',
        payment_method_id: '' as string | number,
        fixed_fee: '' as number | '',
        variable_fee: '' as number | '',
        require_customer_data: '1',
        entity_id: auth.user.entity.id,
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
                    `Data berhasil ${modalState.dataId ? 'diperbarui' : 'ditambahkan'}`
                );
                onModalSuccess();
                reset();
            },
            onError: () => {
                toast.error(
                    `Gagal ${modalState.dataId ? 'memperbarui' : 'menambahkan'} data`
                );
            }
        });
    };

    useEffect(() => {
        const existing = tableData.find(el => el.id === modalState.dataId);

        if (existing) {
            setData({
                name: existing.name ?? '',
                payment_method_id: existing.payment_method_id ?? '',
                fixed_fee: existing.fixed_fee ?? 0,
                variable_fee: existing.variable_fee ?? 0,
                require_customer_data: existing.require_customer_data ? '1' : '0',
                entity_id: existing.entity_id ?? auth.user.entity.id,
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
                            {modalState.dataId ? 'Edit Tipe Order' : 'Tipe Order Baru'}
                        </DialogTitle>
                    </DialogHeader>

                    <FieldSet className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <Field className="lg:col-span-1">
                            <FieldLabel htmlFor="name">Nama</FieldLabel>
                            <Input
                                id="name"
                                placeholder="Masukkan nama"
                                readOnly={processing}
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                            />
                            <FieldError>{errors.name}</FieldError>
                        </Field>

                        <Field className="lg:col-span-1">
                            <FieldLabel>Metode Pembayaran</FieldLabel>
                            <Select
                                value={String(data.payment_method_id)}
                                onValueChange={(val) =>
                                    setData('payment_method_id', val)
                                }
                                disabled={processing}
                            >
                                <SelectTrigger className="h-9 w-full">
                                    <SelectValue placeholder="Pilih Metode Pembayaran" />
                                </SelectTrigger>
                                <SelectContent>
                                    {paymentMethods.map((pm) => (
                                        <SelectItem key={pm.id} value={String(pm.id)}>
                                            {pm.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <FieldError>{errors.payment_method_id}</FieldError>
                        </Field>

                        <Field className="lg:col-span-1">
                            <FieldLabel>Biaya Tetap (Rp)</FieldLabel>
                            <Input
                                type="number"
                                min="0"
                                placeholder="0"
                                readOnly={processing}
                                value={data.fixed_fee}
                                onChange={e => {
                                    const value = e.target.value;
                                    setData('fixed_fee', value === '' ? '' : Number(value));
                                }}
                            />
                            <FieldError>{errors.fixed_fee}</FieldError>
                        </Field>

                        <Field className="lg:col-span-1">
                            <FieldLabel>Data Member</FieldLabel>
                            <Select
                                value={data.require_customer_data}
                                onValueChange={(val) =>
                                    setData('require_customer_data', val)
                                }
                                disabled={processing}
                            >
                                <SelectTrigger className="h-9 w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1">Iya</SelectItem>
                                    <SelectItem value="0">Tidak</SelectItem>
                                </SelectContent>
                            </Select>
                            <FieldError>{errors.require_customer_data}</FieldError>
                        </Field>

                        <Field className="lg:col-span-1">
                            <FieldLabel>Biaya (%)</FieldLabel>
                            <Input
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0"
                                readOnly={processing}
                                value={data.variable_fee}
                                onChange={e => {
                                    const value = e.target.value;
                                    setData('variable_fee', value === '' ? '' : Number(value));
                                }}
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
                            {modalState.dataId ? 'Simpan' : 'Tambah'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}