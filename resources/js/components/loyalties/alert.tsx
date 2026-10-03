import { router } from '@inertiajs/react';
import { toast } from 'sonner';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import type { Loyalty } from '@/lib/model';
import loyalties from '@/routes/loyalties';
import type { LoyaltyAction } from './table';

export type AlertState = {
    isOpen: boolean;
    loyalty?: Loyalty;
    action?: LoyaltyAction;
    proccessing: boolean;
};

type Props = {
    alertState: AlertState;
    onAlertClose: () => void;
    onAlertProccessing: () => void;
};

const copy: Record<
    LoyaltyAction,
    { title: string; verb: string; done: string; note?: string }
> = {
    activate: {
        title: 'Aktifkan Loyalty',
        verb: 'mengaktifkan',
        done: 'diaktifkan',
        note: 'Loyalty lain yang sedang aktif akan otomatis dinonaktifkan.',
    },
    deactivate: {
        title: 'Nonaktifkan Loyalty',
        verb: 'menonaktifkan',
        done: 'dinonaktifkan',
    },
    archive: {
        title: 'Arsipkan Loyalty',
        verb: 'mengarsipkan',
        done: 'diarsipkan',
    },
    destroy: {
        title: 'Hapus Loyalty',
        verb: 'menghapus',
        done: 'dihapus',
        note: 'Data yang dihapus tidak dapat dikembalikan.',
    },
};

export default ({ alertState, onAlertClose, onAlertProccessing }: Props) => {
    const action = alertState.action ?? 'activate';
    const text = copy[action];

    const submit = () => {
        const loyalty = alertState.loyalty;
        if (!loyalty) return;

        const options = {
            only: ['pagination'],
            preserveState: true,
            preserveScroll: true,
            onBefore: onAlertProccessing,
            onSuccess: () => toast.success(`Loyalty berhasil ${text.done}`),
            onError: (errors: Record<string, string>) =>
                toast.error(errors.loyalty ?? `Gagal ${text.verb} loyalty`),
            onFinish: onAlertClose,
        };

        if (action === 'destroy') {
            router.delete(loyalties.destroy(loyalty.id).url, options);
        } else {
            router.patch(loyalties[action](loyalty.id).url, {}, options);
        }
    };

    return (
        <AlertDialog
            open={alertState.isOpen}
            onOpenChange={() => alertState.proccessing || onAlertClose()}
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{text.title}</AlertDialogTitle>
                    <AlertDialogDescription>
                        Apakah anda yakin ingin {text.verb} loyalty{' '}
                        <b>{alertState.loyalty?.name}</b>? {text.note}
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel disabled={alertState.proccessing}>
                        Batal
                    </AlertDialogCancel>
                    <Button
                        variant={
                            action === 'destroy' || action === 'archive'
                                ? 'destructive'
                                : 'default'
                        }
                        disabled={alertState.proccessing}
                        onClick={submit}
                    >
                        <Spinner
                            className={alertState.proccessing ? '' : 'hidden'}
                        />
                        Ya
                    </Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
};
