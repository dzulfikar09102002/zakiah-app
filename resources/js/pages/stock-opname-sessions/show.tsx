import { Head, Link, router } from '@inertiajs/react';
import { FileText, Pencil, Play, QrCode, Trash2, XCircle } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import {
    formatSessionDate,
    formatSessionTime,
    postSessionAction,
    SessionStatusBadge,
    sessionEmployeeName,
} from '@/components/stock-opname-session/helpers';
import ReviewPanel from '@/components/stock-opname-session/review-panel';
import ScanPanel from '@/components/stock-opname-session/scan-panel';
import type {
    OpnameSession,
    ReviewData,
    Viewer,
} from '@/components/stock-opname-session/types';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { capitalize } from '@/lib/utils';
import productOpnameSessions from '@/routes/product-opname-sessions';

type Props = {
    session: OpnameSession;
    viewer: Viewer;
    review: ReviewData | null;
};

export default function Show({ session, viewer, review }: Props) {
    const [confirmDelete, setConfirmDelete] = useState(false);
    const [confirmCancel, setConfirmCancel] = useState(false);
    const title = 'Sesi SO';

    return (
        <AppLayout
            breadcrumbs={[
                {
                    title: 'Stok Opname',
                    href: productOpnameSessions.index().url,
                },
                {
                    title: session.code,
                    href: productOpnameSessions.show(session.id).url,
                },
            ]}
        >
            <Head title={`${title} ${session.code}`} />

            <div className="space-y-4">
                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-2">
                        <div className="min-w-0">
                            <p className="text-sm text-muted-foreground">
                                {title}
                            </p>
                            <CardTitle className="break-all">
                                {session.code}
                            </CardTitle>
                        </div>
                        <SessionStatusBadge status={session.status} />
                    </CardHeader>
                    <CardContent>
                        <dl className="grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
                            <div>
                                <dt className="text-muted-foreground">Toko</dt>
                                <dd className="font-medium break-words">
                                    {capitalize(session.location?.name ?? '-')}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">
                                    Tanggal SO
                                </dt>
                                <dd className="font-medium">
                                    {formatSessionDate(session.opname_date)}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">PJ SO</dt>
                                <dd className="font-medium break-words">
                                    {sessionEmployeeName(session.pic)}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">
                                    {session.end_at ? 'Selesai' : 'Mulai'}
                                </dt>
                                <dd className="font-medium">
                                    {formatSessionTime(
                                        session.end_at ?? session.start_at,
                                    )}
                                </dd>
                            </div>
                            <div className="col-span-2 lg:col-span-4">
                                <dt className="text-muted-foreground">
                                    Karyawan
                                </dt>
                                <dd className="font-medium break-words">
                                    {session.employees
                                        ?.map(sessionEmployeeName)
                                        .join(', ') || '-'}
                                </dd>
                            </div>
                            {session.note && (
                                <div className="col-span-2 lg:col-span-4">
                                    <dt className="text-muted-foreground">
                                        Catatan
                                    </dt>
                                    <dd className="break-words">
                                        {session.note}
                                    </dd>
                                </div>
                            )}
                        </dl>
                    </CardContent>
                </Card>

                {session.status === 'scheduled' && (
                    <div className="flex flex-col gap-2 md:flex-row">
                        {viewer.is_pic ? (
                            <Button
                                type="button"
                                className="h-11 md:h-9"
                                onClick={() =>
                                    postSessionAction(
                                        productOpnameSessions.start(session.id)
                                            .url,
                                        {},
                                        { success: 'Sesi SO dimulai' },
                                    )
                                }
                            >
                                <Play /> Mulai Sesi
                            </Button>
                        ) : (
                            <p className="rounded-lg border p-3 text-sm text-muted-foreground">
                                Sesi belum dimulai. Tunggu PJ SO memulai sesi,
                                halaman ini perlu dimuat ulang setelahnya.
                            </p>
                        )}
                        {viewer.can_manage_schedule && (
                            <>
                                <Button
                                    variant="outline"
                                    className="h-11 md:h-9"
                                    asChild
                                >
                                    <Link
                                        href={
                                            productOpnameSessions.edit(
                                                session.id,
                                            ).url
                                        }
                                    >
                                        <Pencil /> Ubah
                                    </Link>
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    className="h-11 text-destructive md:h-9"
                                    onClick={() => setConfirmDelete(true)}
                                >
                                    <Trash2 /> Hapus
                                </Button>
                            </>
                        )}
                    </div>
                )}

                {viewer.is_pic &&
                    (session.status === 'scheduled' ||
                        session.status === 'started') && (
                        <Button
                            variant="secondary"
                            className="h-11 w-full md:h-9 md:w-auto"
                            asChild
                        >
                            <Link
                                href={
                                    productOpnameSessions.share(session.id).url
                                }
                            >
                                <QrCode /> Bagikan QR Sesi
                            </Link>
                        </Button>
                    )}

                {viewer.can_cancel && (
                    <Button
                        type="button"
                        variant="outline"
                        className="h-11 w-full text-destructive md:h-9 md:w-auto"
                        onClick={() => setConfirmCancel(true)}
                    >
                        <XCircle /> Batalkan Sesi
                    </Button>
                )}

                {session.status === 'started' && (
                    <ScanPanel session={session} viewer={viewer} />
                )}

                {session.status === 'review' && !viewer.is_pic && (
                    <p className="rounded-lg border p-4 text-center text-sm text-muted-foreground">
                        Scan sudah ditutup. PJ SO sedang mereview hasilnya.
                    </p>
                )}

                {session.status === 'finished' && (
                    <Button className="h-11 w-full md:h-9 md:w-auto" asChild>
                        <a
                            href={productOpnameSessions.report(session.id).url}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <FileText /> Lihat Laporan Hasil SO
                        </a>
                    </Button>
                )}

                {review && (
                    <ReviewPanel
                        session={session}
                        review={review}
                        canDecide={viewer.is_pic && session.status === 'review'}
                        canCorrect={viewer.can_correct}
                    />
                )}
            </div>

            <AlertDialog open={confirmCancel} onOpenChange={setConfirmCancel}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Batalkan sesi SO?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Sesi {session.code} akan dihapus dan toko ini bisa
                            dibuatkan sesi baru. Pastikan petugas belum mulai
                            scan, karena sesi yang sudah punya hasil scan tidak
                            bisa dibatalkan.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Tidak</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={() =>
                                postSessionAction(
                                    productOpnameSessions.cancel(session.id)
                                        .url,
                                    {},
                                    { success: 'Sesi SO dibatalkan' },
                                )
                            }
                        >
                            Ya, Batalkan
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <AlertDialog open={confirmDelete} onOpenChange={setConfirmDelete}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Hapus sesi SO?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Jadwal {session.code} akan dihapus.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Batal</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={() =>
                                router.delete(
                                    productOpnameSessions.destroy(session.id)
                                        .url,
                                    {
                                        onSuccess: () =>
                                            toast.success('Sesi SO dihapus'),
                                    },
                                )
                            }
                        >
                            Hapus
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}
