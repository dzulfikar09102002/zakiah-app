import { Head, Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { getCoreRowModel, useReactTable } from '@tanstack/react-table';
import {
    ArchiveRestore,
    Pencil,
    Plus,
    ShieldCheck,
    X,
} from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import PermissionDialog from '@/components/access-roles/permission-dialog';
import type { RoleFormState } from '@/components/access-roles/role-form-dialog';
import RoleFormDialog from '@/components/access-roles/role-form-dialog';
import type {
    AccessRoleRow,
    MatrixHeader,
} from '@/components/access-roles/types';
import DataTable from '@/components/data-table';
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
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import accessRolesRoutes from '@/routes/access-roles';
import type { BreadcrumbItem } from '@/types';

const title = 'Role';

const breadcrumbs: BreadcrumbItem[] = [
    { title, href: accessRolesRoutes.index().url },
];

type Props = {
    accessRoles: AccessRoleRow[];
    matrix: MatrixHeader[];
    onlyTrashed: boolean;
};

type Confirm = { action: 'delete' | 'restore'; role: AccessRoleRow } | null;

/**
 * Role backoffice: CRUD nama seperti biasa, lalu tombol Hak Akses untuk mencentang menu
 * dan aksi yang boleh dibuka. Terpisah dari role jabatan di toko (Kasir, Sales, ...)
 * yang dipakai POS.
 */
export default function AccessRoleIndex({
    accessRoles,
    matrix,
    onlyTrashed,
}: Props) {
    const [form, setForm] = useState<RoleFormState>({ mode: 'closed' });
    const [permissionRole, setPermissionRole] = useState<AccessRoleRow | null>(
        null,
    );
    const [confirm, setConfirm] = useState<Confirm>(null);
    const [processing, setProcessing] = useState(false);

    const runConfirm = () => {
        if (!confirm) {
            return;
        }

        const options = {
            preserveScroll: true,
            onBefore: () => setProcessing(true),
            onSuccess: () =>
                toast.success(
                    confirm.action === 'delete'
                        ? 'Role dihapus'
                        : 'Role dipulihkan',
                ),
            onError: (errors: Record<string, string>) =>
                toast.error(errors.access_role ?? 'Gagal menyimpan'),
            onFinish: () => {
                setProcessing(false);
                setConfirm(null);
            },
        };

        if (confirm.action === 'delete') {
            router.delete(
                accessRolesRoutes.destroy(confirm.role.id).url,
                options,
            );
        } else {
            router.post(
                accessRolesRoutes.restore(confirm.role.id).url,
                {},
                options,
            );
        }
    };

    const columns: ColumnDef<AccessRoleRow>[] = [
        {
            header: 'No',
            cell: ({ row }) => row.index + 1,
        },
        {
            id: 'name',
            header: 'Nama',
            cell: ({ row }) => (
                <div className="min-w-40 space-y-1">
                    <p className="font-medium">
                        {row.original.name}
                        {!row.original.is_active && (
                            <Badge variant="outline" className="ml-2">
                                Nonaktif
                            </Badge>
                        )}
                    </p>
                    {row.original.default_for && (
                        <p className="text-xs text-muted-foreground">
                            Bawaan untuk role {row.original.default_for}
                        </p>
                    )}
                </div>
            ),
        },
        {
            id: 'note',
            header: 'Keterangan',
            cell: ({ row }) => row.original.note || '-',
        },
        {
            id: 'employees',
            header: 'Karyawan',
            cell: ({ row }) => row.original.employees_count,
        },
        {
            id: 'actions',
            header: 'Hak Akses',
            cell: ({ row }) =>
                row.original.actions_count ? (
                    `${row.original.actions_count} aksi`
                ) : (
                    <Badge variant="outline">Belum diatur</Badge>
                ),
        },
        {
            id: 'action',
            header: () => <div className="text-center">Aksi</div>,
            cell: ({ row }) =>
                onlyTrashed ? (
                    <div className="flex justify-center">
                        <Button
                            size="icon"
                            variant="outline"
                            className="size-11 md:size-9"
                            aria-label={`Pulihkan ${row.original.name}`}
                            onClick={() =>
                                setConfirm({
                                    action: 'restore',
                                    role: row.original,
                                })
                            }
                        >
                            <ArchiveRestore />
                        </Button>
                    </div>
                ) : (
                    <div className="flex justify-center gap-2">
                        <Button
                            variant="secondary"
                            className="h-11 md:h-9"
                            onClick={() => setPermissionRole(row.original)}
                        >
                            <ShieldCheck /> Hak Akses
                        </Button>
                        <Button
                            size="icon"
                            variant="outline"
                            className="size-11 md:size-9"
                            aria-label={`Edit ${row.original.name}`}
                            onClick={() =>
                                setForm({ mode: 'edit', role: row.original })
                            }
                        >
                            <Pencil />
                        </Button>
                        {!row.original.is_system && (
                            <Button
                                size="icon"
                                variant="destructive"
                                className="size-11 md:size-9"
                                aria-label={`Hapus ${row.original.name}`}
                                onClick={() =>
                                    setConfirm({
                                        action: 'delete',
                                        role: row.original,
                                    })
                                }
                            >
                                <X />
                            </Button>
                        )}
                    </div>
                ),
        },
    ];

    const table = useReactTable({
        data: accessRoles,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <RoleFormDialog
                state={form}
                onClose={() => setForm({ mode: 'closed' })}
            />
            <PermissionDialog
                role={permissionRole}
                matrix={matrix}
                onClose={() => setPermissionRole(null)}
            />

            <AlertDialog
                open={confirm !== null}
                onOpenChange={(open) =>
                    !open && !processing && setConfirm(null)
                }
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {confirm?.action === 'delete'
                                ? 'Hapus role?'
                                : 'Pulihkan role?'}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {confirm?.action === 'delete'
                                ? `${confirm.role.name} akan dihapus. Role yang masih dipakai karyawan tidak bisa dihapus.`
                                : `${confirm?.role.name} bisa dipakai lagi beserta Hak Aksesnya.`}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel disabled={processing}>
                            Batal
                        </AlertDialogCancel>
                        <Button
                            variant={
                                confirm?.action === 'delete'
                                    ? 'destructive'
                                    : 'default'
                            }
                            disabled={processing}
                            onClick={runConfirm}
                        >
                            {processing && <Spinner />}
                            {confirm?.action === 'delete'
                                ? 'Hapus'
                                : 'Pulihkan'}
                        </Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <div className="mb-4">
                <Button
                    className="h-11 md:h-9"
                    onClick={() => setForm({ mode: 'create' })}
                >
                    <Plus /> Role Baru
                </Button>
            </div>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardContent className="p-0 lg:px-6">
                    <Tabs
                        value={onlyTrashed ? 'deleted' : 'available'}
                        className="mb-4"
                    >
                        <TabsList>
                            <TabsTrigger value="available" asChild>
                                <Link href={accessRolesRoutes.index().url}>
                                    Tersedia
                                </Link>
                            </TabsTrigger>
                            <TabsTrigger value="deleted" asChild>
                                <Link
                                    href={
                                        accessRolesRoutes.index({
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
                </CardContent>
            </Card>
        </AppLayout>
    );
}
