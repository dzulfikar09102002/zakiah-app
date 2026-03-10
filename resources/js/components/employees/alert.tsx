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
import employees from '@/routes/employees';

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
                            ? 'Hapus Karyawan'
                            : 'Pulihkan Karyawan'}
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
                                            ? 'Gagal menghapus Karyawan'
                                            : 'Gagal memulihkan Karyawan',
                                    );
                                    console.error(errors);
                                },
                                onSuccess: () => {
                                    toast.success(
                                        alertState.delete
                                            ? 'Karyawan berhasil dihapus'
                                            : 'Karyawan berhasil dipulihkan',
                                    );
                                },
                                onFinish: onAlertClose,
                            };

                            alertState.delete
                                ? router.delete(
                                      employees.destroy(alertState.dataId).url,
                                      options,
                                  )
                                : router.post(
                                      employees.restore(alertState.dataId).url,
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
