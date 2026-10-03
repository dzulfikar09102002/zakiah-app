import { router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Field, FieldLabel } from '@/components/ui/field';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';

export type ApprovalAction = {
    title: string;
    description: string;
    confirmLabel: string;
    successMessage: string;
    url: string;
    destructive?: boolean;
};

type Props = {
    action: ApprovalAction | null;
    onClose: () => void;
};

/**
 * Konfirmasi aksi dokumen stok (setujui / tolak / batalkan) dengan catatan opsional.
 */
export default function ApprovalDialog({ action, onClose }: Props) {
    const [note, setNote] = useState('');
    const [processing, setProcessing] = useState(false);

    const close = () => {
        setNote('');
        onClose();
    };

    const submit = () => {
        if (!action) return;

        router.post(
            action.url,
            { note: note || null },
            {
                preserveScroll: true,
                onBefore: () => setProcessing(true),
                onSuccess: () => {
                    toast.success(action.successMessage);
                    close();
                },
                onError: (errors) =>
                    toast.error(
                        Object.values(errors)[0] ?? 'Aksi gagal diproses',
                    ),
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <Dialog
            open={action !== null}
            onOpenChange={(open) => !open && !processing && close()}
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{action?.title}</DialogTitle>
                    <DialogDescription>{action?.description}</DialogDescription>
                </DialogHeader>

                <Field>
                    <FieldLabel htmlFor="approval-note">
                        Catatan (opsional)
                    </FieldLabel>
                    <Textarea
                        id="approval-note"
                        placeholder="Tambahkan catatan"
                        value={note}
                        disabled={processing}
                        onChange={(e) => setNote(e.target.value)}
                    />
                </Field>

                <DialogFooter>
                    <Button
                        variant="outline"
                        disabled={processing}
                        onClick={close}
                    >
                        Batal
                    </Button>
                    <Button
                        variant={
                            action?.destructive ? 'destructive' : 'default'
                        }
                        disabled={processing}
                        onClick={submit}
                    >
                        <Spinner className={processing ? '' : 'hidden'} />
                        {action?.confirmLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
