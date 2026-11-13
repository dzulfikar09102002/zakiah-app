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
import ordertypes from '@/routes/order-types';
import { Button } from '../ui/button';
import { Spinner } from '../ui/spinner';

export type AlertState = {
    delete: boolean;
    isOpen: boolean;
    dataId: unknown;
    proccessing: boolean;
};

type Props = {
    alertState: AlertState;
    onAlertClose: () => void;
    onAlertProccessing: () => void;
};

export default ({ alertState, onAlertClose, onAlertProccessing }: Props) => {
    return (
        <AlertDialog
            open={alertState.isOpen}
            onOpenChange={() => alertState.proccessing || onAlertClose()}
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {alertState.delete
                            ? 'Hapus Jenis Pesanan'
                            : 'Pulihkan Jenis Pesanan'}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        Apakah anda yakin ingin{' '}
                        {alertState.delete ? 'menghapus' : 'memulihkan'} data
                        ini?
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel disabled={alertState.proccessing}>
                        Batal
                    </AlertDialogCancel>

                    <Button
                        variant={alertState.delete ? 'destructive' : 'default'}
                        disabled={alertState.proccessing}
                        onClick={() => {
                            const options = {
                                only: ['pagination'],
                                preserveState: true,
                                onBefore: onAlertProccessing,
                                onError: (errors: unknown) => {
                                    toast.error(
                                        alertState.delete
                                            ? 'Gagal menghapus Jenis Pesanan'
                                            : 'Gagal memulihkan Jenis Pesanan',
                                    );
                                    console.error(errors);
                                },
                                onSuccess: () => {
                                    toast.success(
                                        alertState.delete
                                            ? 'Jenis Pesanan berhasil dihapus'
                                            : 'Jenis Pesanan berhasil dipulihkan',
                                    );
                                },
                                onFinish: onAlertClose,
                            };

                            if (alertState.delete) {
                                router.delete(
                                    ordertypes.destroy(
                                        Number(alertState.dataId),
                                    ).url,
                                    options,
                                );
                            } else {
                                router.post(
                                    ordertypes.restore(
                                        Number(alertState.dataId),
                                    ).url,
                                    {},
                                    options,
                                );
                            }
                        }}
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
