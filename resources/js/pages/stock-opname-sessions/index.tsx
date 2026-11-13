import { Head, Link } from '@inertiajs/react';
import { Lock, LogIn, Plus, TriangleAlert } from 'lucide-react';
import DocumentFilters from '@/components/stock/document-filters';
import {
    formatSessionDate,
    SessionStatusBadge,
    sessionEmployeeName,
    sessionStatusTabs,
} from '@/components/stock-opname-session/helpers';
import type { OpnameSession } from '@/components/stock-opname-session/types';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { TableRowsSkeleton } from '@/components/ui/table-rows-skeleton';
import AppLayout from '@/layouts/app-layout';
import type { Pagination } from '@/lib/model';
import { capitalize } from '@/lib/utils';
import productOpnameServices from '@/routes/product-opname-services';
import productOpnameSessions from '@/routes/product-opname-sessions';
import type { Option } from '@/types';

type Props = {
    pagination?: Pagination<OpnameSession>;
    locationOptions: Option[];
    legacyDocuments: { id: number; code: string }[];
};

function EnterButton({ session }: { session: OpnameSession }) {
    if (!session.can_enter) {
        return (
            <span className="inline-flex min-h-9 items-center gap-1 text-xs text-muted-foreground">
                <Lock className="size-3.5" /> Tidak terdaftar
            </span>
        );
    }

    return (
        <Button
            variant="outline"
            className="h-11 w-full md:h-9 md:w-auto"
            asChild
        >
            <Link href={productOpnameSessions.show(session.id).url}>
                <LogIn /> Masuk
            </Link>
        </Button>
    );
}

export default function Index({
    pagination,
    locationOptions,
    legacyDocuments,
}: Props) {
    const title = 'Stok Opname';

    return (
        <AppLayout
            breadcrumbs={[{ title, href: productOpnameSessions.index().url }]}
        >
            <Head title={title} />

            <div className="mb-4 space-y-3">
                {legacyDocuments.length > 0 && (
                    <div className="rounded-lg border border-amber-500/40 bg-amber-500/10 p-3 text-sm">
                        <p className="flex items-center gap-2 font-medium">
                            <TriangleAlert className="size-4 shrink-0 text-amber-600" />
                            {legacyDocuments.length} dokumen opname lama
                            menunggu persetujuan
                        </p>
                        <ul className="mt-2 space-y-1">
                            {legacyDocuments.map((document) => (
                                <li key={document.id}>
                                    <Link
                                        className="block py-2 break-all text-primary underline md:py-0.5"
                                        href={
                                            productOpnameServices.show(
                                                document.id,
                                            ).url
                                        }
                                    >
                                        {document.code}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                <Button className="h-11 w-full md:h-9 md:w-auto" asChild>
                    <Link href={productOpnameSessions.create().url}>
                        <Plus /> Sesi SO Baru
                    </Link>
                </Button>
            </div>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="p-0 lg:px-6">
                    <DocumentFilters
                        indexUrl={productOpnameSessions.index().url}
                        locationOptions={locationOptions}
                        statusTabs={sessionStatusTabs}
                    />
                </CardHeader>

                <CardContent className="border-t p-0 pt-3 lg:border-0 lg:px-6">
                    {/* HP: kartu supaya tidak perlu geser tabel. */}


                    <ul className="space-y-2 md:hidden">
                        {(pagination?.data ?? []).map((session) => (
                            <li
                                key={session.id}
                                className="space-y-2 rounded-xl border bg-card p-3"
                            >
                                <div className="flex items-start justify-between gap-2">
                                    <div className="min-w-0">
                                        <p className="font-medium break-all">
                                            {session.code}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {session.type === 2
                                                ? 'Revisi SO • '
                                                : ''}
                                            {formatSessionDate(
                                                session.opname_date,
                                            )}
                                        </p>
                                    </div>
                                    <SessionStatusBadge
                                        status={session.status}
                                    />
                                </div>
                                <p className="text-sm break-words">
                                    {capitalize(session.location?.name ?? '-')}
                                </p>
                                <p className="text-xs break-words text-muted-foreground">
                                    PJ: {sessionEmployeeName(session.pic)} •{' '}
                                    {session.employees_count ?? 0} karyawan
                                </p>
                                <EnterButton session={session} />
                            </li>
                        ))}
                        {!pagination &&
                            Array.from({ length: 3 }).map((_, i) => (
                                <li
                                    key={`skeleton-${i}`}
                                    aria-hidden="true"
                                    className="space-y-2 rounded-xl border bg-card p-3"
                                >
                                    <Skeleton className="h-4 w-2/3" />
                                    <Skeleton className="h-3 w-1/3" />
                                    <Skeleton className="h-3 w-1/2" />
                                </li>
                            ))}
                        {pagination && !pagination.data.length && (
                            <li className="p-6 text-center text-sm text-muted-foreground">
                                Belum ada sesi SO
                            </li>
                        )}
                    </ul>
                    <div className="hidden md:block">
                        <Table className="stripped">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Kode</TableHead>
                                    <TableHead>Jenis</TableHead>
                                    <TableHead>Tanggal SO</TableHead>
                                    <TableHead>Toko</TableHead>
                                    <TableHead>PJ SO</TableHead>
                                    <TableHead className="text-right">
                                        Karyawan
                                    </TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-center">
                                        Aksi
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {(pagination?.data ?? []).map((session) => (
                                    <TableRow key={session.id}>
                                        <TableCell className="font-medium">
                                            {session.code}
                                        </TableCell>
                                        <TableCell>
                                            {session.type === 2
                                                ? 'Revisi SO'
                                                : 'SO'}
                                        </TableCell>
                                        <TableCell>
                                            {formatSessionDate(
                                                session.opname_date,
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {capitalize(
                                                session.location?.name ?? '-',
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {sessionEmployeeName(session.pic)}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {session.employees_count ?? 0}
                                        </TableCell>
                                        <TableCell>
                                            <SessionStatusBadge
                                                status={session.status}
                                            />
                                        </TableCell>
                                        <TableCell className="text-center">
                                            <EnterButton session={session} />
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {!pagination && <TableRowsSkeleton columns={8} />}
                                {pagination && !pagination.data.length && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={8}
                                            className="py-4 text-center text-muted-foreground"
                                        >
                                            Belum ada sesi SO
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}
