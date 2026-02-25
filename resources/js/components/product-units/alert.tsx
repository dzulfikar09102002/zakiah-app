import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle
} from '@/components/ui/alert-dialog';

import { Button } from '../ui/button';
import { toast } from 'sonner';
import { router } from '@inertiajs/react';
import { Spinner } from '../ui/spinner';
import productUnits from '@/routes/product-units';

export type AlertState = {
    delete: boolean
    isOpen: boolean
    dataId: any
    proccessing: boolean
}

type Props = {
    alertState: AlertState
    onAlertClose: () => void
    onAlertProccessing: () => void
}

export default ({ alertState, onAlertClose, onAlertProccessing }: Props) => {

    return (
        <AlertDialog
            open={alertState.isOpen}
            onOpenChange={() => alertState.proccessing || onAlertClose()}
        >
            <AlertDialogContent>

                <AlertDialogHeader>
                    <AlertDialogTitle>
                        Hapus Produk Unit
                    </AlertDialogTitle>

                    <AlertDialogDescription>
                        Apakah anda yakin ingin menghapus data ini?
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>

                    <AlertDialogCancel disabled={alertState.proccessing}>
                        Batal
                    </AlertDialogCancel>

                    <Button
                        variant="destructive"
                        disabled={alertState.proccessing}
                        onClick={() => {

                            router.delete(
                                productUnits.destroy(alertState.dataId).url,
                                {
                                    only: ['pagination'],
                                    preserveState: true,

                                    onBefore: onAlertProccessing,

                                    onError: (errors: any) => {
                                        toast.error('Gagal menghapus data')
                                        console.error(errors)
                                    },

                                    onSuccess: () => {
                                        toast.success('Data berhasil dihapus')
                                    },

                                    onFinish: onAlertClose,
                                }
                            )
                        }}
                    >
                        <Spinner className={alertState.proccessing ? '' : 'hidden'} />
                        Ya
                    </Button>

                </AlertDialogFooter>

            </AlertDialogContent>
        </AlertDialog>
    )
}