import { Form, Head, Link } from '@inertiajs/react';
import type { ColumnDef, ExpandedState } from '@tanstack/react-table';
import { getCoreRowModel, useReactTable } from '@tanstack/react-table';
import {
    ArchiveRestore,
    ChevronRight,
    KeyRound,
    Pencil,
    Plus,
    Search,
    X,
} from 'lucide-react';
import { useState } from 'react';
import DataTable from '@/components/data-table';
import type { AlertState } from '@/components/employees/alert';
import Alert from '@/components/employees/alert';
import type { ModalState } from '@/components/employees/modal';
import Modal from '@/components/employees/modal';
import type {
    AccessRoleOption,
    EmployeeRow,
    LocationOption,
    RoleOption,
} from '@/components/employees/types';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { Pagination } from '@/lib/model';
import { cn } from '@/lib/utils';
import employees from '@/routes/employees';
import userAccounts from '@/routes/user-accounts';
import type { BreadcrumbItem } from '@/types';

const title = 'Karyawan';

const breadcrumbs: BreadcrumbItem[] = [{ title, href: employees.index().url }];

type Props = {
    pagination: Pagination<EmployeeRow>;
    roleOptions: RoleOption[];
    accessRoleOptions: AccessRoleOption[] | null;
    locations: LocationOption[];
    onlyTrashed: boolean;
};

/** Baris detail: toko dan role tempat karyawan bertugas. */
function AssignmentDetail({ employee }: { employee: EmployeeRow }) {
    return (
        <div className="space-y-2">
            {employee.shares_account && (
                <p className="text-sm text-amber-700 dark:text-amber-400">
                    Data lama: akun login karyawan ini juga dipakai karyawan
                    lain.
                </p>
            )}
            {employee.access_role_label && (
                <p className="text-sm text-muted-foreground">
                    Hak Akses: {employee.access_role_label}
                </p>
            )}
            {employee.assignments.length ? (
                <table className="w-full max-w-xl text-sm">
                    <thead>
                        <tr className="text-left text-muted-foreground">
                            <th className="py-1 pr-4 font-medium">Toko</th>
                            <th className="py-1 font-medium">Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        {employee.assignments.map((assignment) => (
                            <tr
                                key={assignment.location_id}
                                className="border-t align-top"
                            >
                                <td className="py-1.5 pr-4">
                                    {assignment.location}
                                </td>
                                <td className="py-1.5">
                                    {assignment.roles
                                        .map((role) => role.label)
                                        .join(', ')}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            ) : (
                <p className="text-sm text-muted-foreground">
                    Belum ditugaskan di toko mana pun.
                </p>
            )}
        </div>
    );
}

export default function EmployeeIndex({
    pagination,
    roleOptions,
    accessRoleOptions,
    locations,
    onlyTrashed,
}: Props) {
    const [modal, setModal] = useState<ModalState>({
        isOpen: false,
        dataId: undefined,
    });
    const [alert, setAlert] = useState<AlertState>({
        delete: true,
        isOpen: false,
        dataId: undefined,
        proccessing: false,
    });
    const [expanded, setExpanded] = useState<ExpandedState>({});

    const search = useQuery().search || '';

    const closeModal = () => setModal({ isOpen: false, dataId: undefined });

    const columns: ColumnDef<EmployeeRow>[] = [
        {
            id: 'expand',
            header: () => <span className="sr-only">Detail</span>,
            cell: ({ row }) => (
                <Button
                    size="icon"
                    variant="ghost"
                    className="size-11 md:size-8"
                    aria-expanded={row.getIsExpanded()}
                    aria-label={`${row.getIsExpanded() ? 'Tutup' : 'Lihat'} toko dan role ${row.original.name}`}
                    onClick={row.getToggleExpandedHandler()}
                >
                    <ChevronRight
                        className={cn(
                            'transition-transform',
                            row.getIsExpanded() && 'rotate-90',
                        )}
                    />
                </Button>
            ),
        },
        {
            header: 'No',
            cell: ({ row }) =>
                (pagination.current_page - 1) * pagination.per_page +
                row.index +
                1,
        },
        {
            id: 'name',
            header: 'Nama',
            cell: ({ row }) => (
                <span className="font-medium">{row.original.name}</span>
            ),
        },
        {
            id: 'account',
            header: 'Akun',
            cell: ({ row }) => (
                <span className="break-all">{row.original.email ?? '-'}</span>
            ),
        },
        {
            id: 'action',
            header: () => <div className="text-center">Aksi</div>,
            cell: ({ row }) => (
                <div className="flex justify-center gap-2">
                    {!onlyTrashed && (
                        <Button
                            size="icon"
                            variant="outline"
                            className="size-11 md:size-9"
                            aria-label={`Edit ${row.original.name}`}
                            onClick={() =>
                                setModal({
                                    isOpen: true,
                                    dataId: row.original.id,
                                })
                            }
                        >
                            <Pencil />
                        </Button>
                    )}
                    <Button
                        size="icon"
                        variant={onlyTrashed ? 'outline' : 'destructive'}
                        className="size-11 md:size-9"
                        aria-label={`${onlyTrashed ? 'Pulihkan' : 'Hapus'} ${row.original.name}`}
                        onClick={() =>
                            setAlert({
                                ...alert,
                                isOpen: true,
                                dataId: row.original.id,
                                delete: !onlyTrashed,
                            })
                        }
                    >
                        {onlyTrashed ? <ArchiveRestore /> : <X />}
                    </Button>
                </div>
            ),
        },
    ];

    const table = useReactTable({
        data: pagination.data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        getRowCanExpand: () => true,
        getRowId: (row) => String(row.id),
        onExpandedChange: setExpanded,
        state: { expanded },
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <Modal
                modalState={modal}
                tableData={pagination.data}
                roleOptions={roleOptions}
                accessRoleOptions={accessRoleOptions}
                locations={locations}
                onModalSuccess={closeModal}
                onModalClose={closeModal}
            />

            <Alert
                alertState={alert}
                onAlertClose={() =>
                    setAlert({
                        isOpen: false,
                        proccessing: false,
                        dataId: undefined,
                        delete: true,
                    })
                }
                onAlertProccessing={() =>
                    setAlert({ ...alert, proccessing: true })
                }
            />

            <div className="mb-4 flex flex-col gap-2 md:flex-row md:justify-between">
                <Button
                    className="h-11 md:h-9"
                    onClick={() =>
                        setModal({ isOpen: true, dataId: undefined })
                    }
                >
                    <Plus /> Karyawan Baru
                </Button>
                <Button variant="outline" className="h-11 md:h-9" asChild>
                    <Link href={userAccounts.index().url}>
                        <KeyRound /> Akun Login
                    </Link>
                </Button>
            </div>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="p-0 lg:px-6">
                    <Form method="GET">
                        <div className="grid gap-2 lg:flex">
                            <input type="hidden" name="page" value={1} />
                            <Input
                                defaultValue={search}
                                name="search"
                                placeholder="Cari nama karyawan"
                                aria-label="Cari nama karyawan"
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
                                <Link href={employees.index().url}>
                                    Tersedia
                                </Link>
                            </TabsTrigger>
                            <TabsTrigger value="deleted" asChild>
                                <Link href={employees.deleted().url}>
                                    Terhapus
                                </Link>
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>

                    <DataTable
                        columns={columns}
                        table={table}
                        renderDetail={(employee) => (
                            <AssignmentDetail employee={employee} />
                        )}
                    />
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}
