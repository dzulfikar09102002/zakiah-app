import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle
} from '@/components/ui/alert-dialog'

import { Button } from '../ui/button'
import { toast } from 'sonner'
import { router } from '@inertiajs/react'
import { Spinner } from '../ui/spinner'
import locations from '@/routes/locations'

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

export default ({
    alertState,
    onAlertClose,
    onAlertProccessing
}: Props) => {

    const handleAction = () => {

        const options = {
            only: ['pagination'],
            preserveState: true,

            onBefore: onAlertProccessing,

            onError: (errors: any) => {
                toast.error(
                    alertState.delete
                        ? 'Gagal menghapus data'
                        : 'Gagal memulihkan data'
                )

                console.error(errors)
            },

            onSuccess: () => {
                toast.success(
                    alertState.delete
                        ? 'Data berhasil dihapus'
                        : 'Data berhasil dipulihkan'
                )
            },

            onFinish: onAlertClose,
        }

        if (alertState.delete) {
            router.delete(
                locations.destroy(alertState.dataId).url,
                options
            )
        } else {
            router.post(
                locations.restore(alertState.dataId).url,
                {},
                options
            )
        }
    }

    return (
        <AlertDialog
            open={alertState.isOpen}
            onOpenChange={() => alertState.proccessing || onAlertClose()}
        >
            <AlertDialogContent>

                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {alertState.delete
                            ? 'Hapus Lokasi'
                            : 'Pulihkan Lokasi'}
                    </AlertDialogTitle>

                    <AlertDialogDescription>
                        {alertState.delete
                            ? 'Apakah anda yakin ingin menghapus data ini?'
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
                        <Spinner className={alertState.proccessing ? '' : 'hidden'} />
                        Ya
                    </Button>

                </AlertDialogFooter>

            </AlertDialogContent>
        </AlertDialog>
    )
}