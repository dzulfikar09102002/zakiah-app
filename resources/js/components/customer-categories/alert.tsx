import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '../ui/button';
import { toast } from 'sonner';
import { router } from '@inertiajs/react';
import { Spinner } from '../ui/spinner';
import customerCategories from '@/routes/customer-categories';

export type AlertState = {
    delete: boolean;
    isOpen: boolean;
    dataId: any;
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
                            ? 'Hapus Kategori Pelanggan'
                            : 'Pulihkan Kategori Pelanggan'}
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
                                onError: (errors: any) => {
                                    toast.error(
                                        alertState.delete
                                            ? 'Gagal menghapus Kategori Pelanggan'
                                            : 'Gagal memulihkan Kategori Pelanggan',
                                    );
                                    console.error(errors);
                                },
                                onSuccess: () => {
                                    toast.success(
                                        alertState.delete
                                            ? 'Kategori Pelanggan berhasil dihapus'
                                            : 'Kategori Pelanggan berhasil dipulihkan',
                                    );
                                },
                                onFinish: onAlertClose,
                            };

                            alertState.delete
                                ? router.delete(
                                    customerCategories.destroy(alertState.dataId).url,
                                    options,
                                )
                                : router.post(
                                    customerCategories.restore(alertState.dataId).url,
                                    {},
                                    options,
                                );
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
