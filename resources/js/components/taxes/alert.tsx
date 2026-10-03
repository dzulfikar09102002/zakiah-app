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
import type { Tax } from '@/lib/model';
import taxes from '@/routes/taxes';

export type AlertState = {
    isOpen: boolean;
    tax?: Tax;
    proccessing: boolean;
};

type Props = {
    alertState: AlertState;
    onAlertClose: () => void;
    onAlertProccessing: () => void;
};

export default ({ alertState, onAlertClose, onAlertProccessing }: Props) => {
    const archiving = alertState.tax?.status === 'active';
    const label = archiving ? 'Arsipkan' : 'Aktifkan';

    const submit = () => {
        if (!alertState.tax) return;

        router.patch(
            taxes.toggleStatus(alertState.tax.id).url,
            {},
            {
                only: ['pagination'],
                preserveState: true,
                preserveScroll: true,
                onBefore: onAlertProccessing,
                onSuccess: () =>
                    toast.success(
                        `Pajak berhasil ${archiving ? 'diarsipkan' : 'diaktifkan'}`,
                    ),
                onError: () =>
                    toast.error(
                        `Gagal ${archiving ? 'mengarsipkan' : 'mengaktifkan'} pajak`,
                    ),
                onFinish: onAlertClose,
            },
        );
    };

    return (
        <AlertDialog
            open={alertState.isOpen}
            onOpenChange={() => alertState.proccessing || onAlertClose()}
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{label} Pajak</AlertDialogTitle>
                    <AlertDialogDescription>
                        Apakah anda yakin ingin {label.toLowerCase()} pajak{' '}
                        <b>{alertState.tax?.name}</b>?
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel disabled={alertState.proccessing}>
                        Batal
                    </AlertDialogCancel>
                    <Button
                        variant={archiving ? 'destructive' : 'default'}
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
