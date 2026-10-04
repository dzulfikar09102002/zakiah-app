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
import legalEntities from '@/routes/legal-entities';

export type AlertState = {
    delete: boolean;
    isOpen: boolean;
    dataId?: number;
    proccessing: boolean;
};

type Props = {
    alertState: AlertState;
    onAlertClose: () => void;
    onAlertProccessing: () => void;
};

export default ({ alertState, onAlertClose, onAlertProccessing }: Props) => {
    const handleAction = () => {
        if (!alertState.dataId) return;

        const options = {
            only: ['pagination'],
            preserveState: true,
            onBefore: onAlertProccessing,
            onError: () => {
                toast.error(
                    alertState.delete
                        ? 'Gagal menghapus data'
                        : 'Gagal memulihkan data',
                );
            },
            onSuccess: () => {
                toast.success(
                    alertState.delete
                        ? 'Data berhasil dihapus'
                        : 'Data berhasil dipulihkan',
                );
            },
            onFinish: onAlertClose,
        };

        if (alertState.delete) {
            router.delete(
                legalEntities.destroy(alertState.dataId).url,
                options,
            );
        } else {
            router.post(
                legalEntities.restore(alertState.dataId).url,
                {},
                options,
            );
        }
    };

    return (
        <AlertDialog
            open={alertState.isOpen}
            onOpenChange={() => alertState.proccessing || onAlertClose()}
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {alertState.delete ? 'Hapus CV' : 'Pulihkan CV'}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        {alertState.delete
                            ? 'Apakah anda yakin ingin menghapus data ini? CV juga akan dilepas dari semua lokasi.'
                            : 'Apakah anda yakin ingin memulihkan data ini?'}
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel disabled={alertState.proccessing}>
                        Batal
                    </AlertDialogCancel>
                    <Button
                        variant={alertState.delete ? 'destructive' : 'default'}
                        disabled={alertState.proccessing}
                        onClick={handleAction}
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
