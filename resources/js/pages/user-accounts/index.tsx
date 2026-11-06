import { Form, Head, Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { getCoreRowModel, useReactTable } from '@tanstack/react-table';
import {
    ArchiveRestore,
    Pencil,
    Plus,
    Search,
    UsersRound,
    X,
} from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import DataTable from '@/components/data-table';
import type {
    EmployeeOption,
    UserAccountRow,
} from '@/components/employees/types';
import TablePagination from '@/components/table-pagination';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { AccountDialogState } from '@/components/user-accounts/account-dialog';
import AccountDialog from '@/components/user-accounts/account-dialog';
import RestoreDialog from '@/components/user-accounts/restore-dialog';
import AppLayout from '@/layouts/app-layout';
import type { Pagination } from '@/lib/model';
import employees from '@/routes/employees';
import userAccounts from '@/routes/user-accounts';
import type { BreadcrumbItem } from '@/types';

const title = 'Akun Login';

const breadcrumbs: BreadcrumbItem[] = [
    { title, href: userAccounts.index().url },
];

type Props = {
    pagination: Pagination<UserAccountRow>;
    employeeOptions: EmployeeOption[];
    onlyTrashed: boolean;
    search: string;
};

/**
 * Akun login (email dan password) milik karyawan. Data kerjanya diatur di menu Karyawan.
 * Akun yang dihapus tidak bisa login lagi di backoffice maupun kasir.
 */
export default function UserAccountIndex({
    pagination,
    employeeOptions,
    onlyTrashed,
    search,
}: Props) {
    const [dialog, setDialog] = useState<AccountDialogState>({
        mode: 'closed',
    });
    const [deleting, setDeleting] = useState<UserAccountRow | null>(null);
    const [deleteProcessing, setDeleteProcessing] = useState(false);
    const [restoring, setRestoring] = useState<UserAccountRow | null>(null);

    const destroy = () => {
        if (!deleting) {
            return;
        }

        router.delete(userAccounts.destroy(deleting.id).url, {
            preserveScroll: true,
            onBefore: () => setDeleteProcessing(true),
            onSuccess: () => toast.success('Akun dihapus'),
            onError: (errors) =>
                toast.error(errors.account ?? 'Akun gagal dihapus'),
            onFinish: () => {
                setDeleteProcessing(false);
                setDeleting(null);
            },
        });
    };

    const columns: ColumnDef<UserAccountRow>[] = [
        {
            header: 'No',
            cell: ({ row }) =>
                (pagination.current_page - 1) * pagination.per_page +
                row.index +
                1,
        },
        {
            id: 'name',
            header: 'Nama Akun',
            cell: ({ row }) => (
                <span className="font-medium">{row.original.name}</span>
            ),
        },
        {
            id: 'email',
            header: 'Email',
            cell: ({ row }) => (
                <span className="break-all">
                    {(onlyTrashed
                        ? row.original.deleted_email
                        : row.original.email) ?? '-'}
                </span>
            ),
        },
        {
            id: 'employees',
            header: 'Karyawan',
            cell: ({ row }) => (
                <div className="flex min-w-40 flex-wrap gap-1">
                    {row.original.employees.map((employee) => (
                        <Badge key={employee.id} variant="secondary">
                            {employee.name}
                        </Badge>
                    ))}
                </div>
            ),
        },
        {
            id: 'action',
            header: () => <div className="text-center">Aksi</div>,
            cell: ({ row }) => (
                <div className="flex justify-center gap-2">
                    {onlyTrashed ? (
                        <Button
                            size="icon"
                            variant="outline"
                            className="size-11 md:size-9"
                            aria-label={`Pulihkan akun ${row.original.name}`}
                            onClick={() => setRestoring(row.original)}
                        >
                            <ArchiveRestore />
                        </Button>
                    ) : (
                        <>
                            <Button
                                size="icon"
                                variant="outline"
                                className="size-11 md:size-9"
                                aria-label={`Edit akun ${row.original.name}`}
                                onClick={() =>
                                    setDialog({
                                        mode: 'edit',
                                        account: row.original,
                                    })
                                }
                            >
                                <Pencil />
                            </Button>
                            <Button
                                size="icon"
                                variant="destructive"
                                className="size-11 md:size-9"
                                aria-label={`Hapus akun ${row.original.name}`}
                                disabled={row.original.is_self}
                                title={
                                    row.original.is_self
                                        ? 'Akun Anda sendiri tidak bisa dihapus'
                                        : undefined
                                }
                                onClick={() => setDeleting(row.original)}
                            >
                                <X />
                            </Button>
                        </>
                    )}
                </div>
            ),
        },
    ];

    const table = useReactTable({
        data: pagination.data,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <AccountDialog
                state={dialog}
                employeeOptions={employeeOptions}
                onClose={() => setDialog({ mode: 'closed' })}
            />
            <RestoreDialog
                account={restoring}
                onClose={() => setRestoring(null)}
            />

            <AlertDialog
                open={deleting !== null}
                onOpenChange={(open) =>
                    !open && !deleteProcessing && setDeleting(null)
                }
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Hapus akun login?</AlertDialogTitle>
                        <AlertDialogDescription>
                            {deleting?.name} tidak bisa login lagi di backoffice
                            maupun kasir. Data karyawan tetap ada, dan akun bisa
                            dipulihkan dari tab Terhapus.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel disabled={deleteProcessing}>
                            Batal
                        </AlertDialogCancel>
                        <Button
                            variant="destructive"
                            disabled={deleteProcessing}
                            onClick={destroy}
                        >
                            {deleteProcessing && <Spinner />}
                            Hapus
                        </Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <div className="mb-4 flex flex-col gap-2 md:flex-row md:justify-between">
                <Button
                    className="h-11 md:h-9"
                    onClick={() => setDialog({ mode: 'create' })}
                >
                    <Plus /> Akun Baru
                </Button>
                <Button variant="outline" className="h-11 md:h-9" asChild>
                    <Link href={employees.index().url}>
                        <UsersRound /> Data Karyawan
                    </Link>
                </Button>
            </div>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="p-0 lg:px-6">
                    <Form method="GET">
                        <div className="grid gap-2 lg:flex">
                            <input type="hidden" name="page" value={1} />
                            {onlyTrashed && (
                                <input
                                    type="hidden"
                                    name="status"
                                    value="deleted"
                                />
                            )}
                            <Input
                                defaultValue={search}
                                name="search"
                                placeholder="Cari nama, email, atau karyawan"
                                aria-label="Cari akun"
                                className="h-11 text-base md:h-9 md:text-sm"
                            />
                            <Button variant="secondary" className="h-11 md:h-9">
                                <Search /> Cari
                            </Button>
                        </div>
                    </Form>
                </CardHeader>

                <CardContent className="border-t p-0 lg:border-0 lg:px-6">
                    <Tabs
                        value={onlyTrashed ? 'deleted' : 'available'}
                        className="mb-4"
                    >
                        <TabsList>
                            <TabsTrigger value="available" asChild>
                                <Link href={userAccounts.index().url}>
                                    Tersedia
                                </Link>
                            </TabsTrigger>
                            <TabsTrigger value="deleted" asChild>
                                <Link
                                    href={
                                        userAccounts.index({
                                            query: { status: 'deleted' },
                                        }).url
                                    }
                                >
                                    Terhapus
                                </Link>
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>

                    <DataTable columns={columns} table={table} />
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}
