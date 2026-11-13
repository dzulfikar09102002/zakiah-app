import { useForm } from '@inertiajs/react';
import type { SubmitEventHandler} from 'react';
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
import type { CustomerCategory } from '@/lib/model';
import customerCategories from '@/routes/customer-categories';

// Relasi dikirim Laravel dalam snake_case (customer_category_rule).
type CustomerCategoryRow = CustomerCategory & {
    customer_category_rule?: { minimal_spend?: number } | null;
};

export type ModalState = {
    isOpen: boolean;
    dataId: unknown;
};

type Props = {
    modalState: ModalState;
    tableData: CustomerCategoryRow[];
    onModalSuccess: () => void;
    onModalClose: () => void;
};

export default ({
    modalState,
    tableData,
    onModalSuccess,
    onModalClose,
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
    } = useForm({
        name: '',
        customer_category_rule: {
            minimal_spend: '' as number | '',
        },
    });
    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        const action = modalState.dataId ? patch : post;
        const url = modalState.dataId
            ? customerCategories.update(Number(modalState.dataId)).url
            : customerCategories.store().url;

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
                name: existing.name ?? '',
                customer_category_rule: {
                    minimal_spend:
                        existing.customer_category_rule?.minimal_spend ?? 0,
                },
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
                                ? 'Edit Kategori Pelanggan'
                                : 'Kategori Pelanggan Baru'}
                        </DialogTitle>
                    </DialogHeader>

                    <FieldSet>
                        <Field>
                            <FieldLabel htmlFor="name">Nama</FieldLabel>
                            <Input
                                id="name"
                                placeholder="Masukkan nama kategori"
                                readOnly={processing}
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                            />
                            <FieldError>{errors.name}</FieldError>
                        </Field>

                        <Field>
                            <FieldLabel>Minimal Pembelian (Rp)</FieldLabel>
                            <Input
                                type="text"
                                inputMode="numeric"
                                placeholder="0"
                                readOnly={processing}
                                value={
                                    data.customer_category_rule.minimal_spend
                                        ? Number(
                                            data.customer_category_rule.minimal_spend
                                        ).toLocaleString('id-ID')
                                        : ''
                                }
                                onChange={(e) => {
                                    const raw = e.target.value.replace(/\./g, '');

                                    if (!/^\d*$/.test(raw)) return;

                                    setData('customer_category_rule', {
                                        ...data.customer_category_rule,
                                        minimal_spend: raw === '' ? '' : Number(raw),
                                    });
                                }}
                            />
                            <FieldError>
                                {errors['customer_category_rule.minimal_spend']}
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
