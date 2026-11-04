import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { FieldLabel } from '@/components/ui/field';
import { Spinner } from '@/components/ui/spinner';
import productOpnameSessions from '@/routes/product-opname-sessions';
import { postSessionAction } from './helpers';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    sessionId: number;
    sessionCode: string;
    picName: string;
    statement: string;
    children?: React.ReactNode;
};

/**
 * Persetujuan PJ sebelum menyimpan dan mengakhiri sesi. Server juga mewajibkan
 * pernyataan ini dicentang, jadi tidak bisa dilewati dari luar tampilan.
 */
export default function FinishDialog({
    open,
    onOpenChange,
    sessionId,
    sessionCode,
    picName,
    statement,
    children,
}: Props) {
    const [agreed, setAgreed] = useState(false);
    const [processing, setProcessing] = useState(false);

    const changeOpen = (next: boolean) => {
        if (processing) {
            return;
        }

        if (!next) {
            setAgreed(false);
        }

        onOpenChange(next);
    };

    const submit = () => {
        setProcessing(true);
        postSessionAction(
            productOpnameSessions.finish(sessionId).url,
            { agreement: agreed },
            {
                success: 'Sesi diakhiri, laporan sudah tersedia',
                onSuccess: () => {
                    setAgreed(false);
                    onOpenChange(false);
                },
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <Dialog open={open} onOpenChange={changeOpen}>
            <DialogContent className="max-h-[90dvh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Simpan dan Akhiri Sesi</DialogTitle>
                    <DialogDescription>
                        Sesi {sessionCode} akan ditutup permanen dan laporannya
                        ditandatangani secara elektronik atas nama {picName}.
                    </DialogDescription>
                </DialogHeader>

                {children}

                <div className="flex items-start gap-3 rounded-lg border p-3">
                    <Checkbox
                        id="session-agreement"
                        className="mt-0.5"
                        checked={agreed}
                        disabled={processing}
                        onCheckedChange={(checked) =>
                            setAgreed(checked === true)
                        }
                    />
                    <FieldLabel
                        htmlFor="session-agreement"
                        className="block text-sm leading-relaxed font-normal"
                    >
                        {statement}
                    </FieldLabel>
                </div>

                <DialogFooter className="gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        className="h-11 md:h-9"
                        disabled={processing}
                        onClick={() => changeOpen(false)}
                    >
                        Batal
                    </Button>
                    <Button
                        type="button"
                        className="h-11 md:h-9"
                        disabled={!agreed || processing}
                        onClick={submit}
                    >
                        {processing && <Spinner />}
                        Setuju, Simpan dan Akhiri
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
