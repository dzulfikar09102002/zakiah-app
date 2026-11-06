import { useForm } from '@inertiajs/react';
import type { SubmitEventHandler } from 'react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import type { UserAccountRow } from '@/components/employees/types';
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
import {
    Field,
    FieldDescription,
    FieldError,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import userAccounts from '@/routes/user-accounts';

type Props = {
    account: UserAccountRow | null;
    onClose: () => void;
};

/**
 * Pulihkan akun: email lama dikembalikan dan password lama berlaku lagi. Bila email lama
 * sudah dipakai akun lain, isi email baru.
 */
export default function RestoreDialog({ account, onClose }: Props) {
    const { data, setData, post, processing, errors, clearErrors } = useForm({
        email: '',
    });

    useEffect(() => {
        if (account) {
            clearErrors();
            setData('email', account.deleted_email ?? '');
        }
        // Hanya saat akun yang dibuka berganti.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [account?.id]);

    const submit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        if (!account) {
            return;
        }

        post(userAccounts.restore(account.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Akun dipulihkan');
                onClose();
            },
            onError: () => toast.error('Akun gagal dipulihkan'),
        });
    };

    return (
        <Dialog
            open={account !== null}
            onOpenChange={(open) => !open && !processing && onClose()}
        >
            <DialogContent asChild>
                <form onSubmit={submit}>
                    <DialogCancel />
                    <DialogHeader>
                        <DialogTitle>Pulihkan Akun</DialogTitle>
                        <DialogDescription className="break-words">
                            {account?.name} bisa login lagi dengan password
                            lamanya.
                        </DialogDescription>
                    </DialogHeader>

                    <Field>
                        <FieldLabel htmlFor="restore-email">Email</FieldLabel>
                        <Input
                            id="restore-email"
                            type="email"
                            value={data.email}
                            readOnly={processing}
                            onChange={(event) =>
                                setData('email', event.target.value)
                            }
                            className="h-11 text-base md:h-9 md:text-sm"
                        />
                        <FieldDescription>
                            Ganti bila email lama sudah dipakai akun lain.
                        </FieldDescription>
                        <FieldError>{errors.email}</FieldError>
                    </Field>

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
                            Pulihkan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
