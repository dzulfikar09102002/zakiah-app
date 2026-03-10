import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArchiveRestore, Pencil, Plus, Search, X } from 'lucide-react';
import { useEffect, useState } from 'react';

import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';

import { Card, CardContent, CardHeader } from '@/components/ui/card';

import { Input } from '@/components/ui/input';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';

import type { Pagination, Employee } from '@/lib/model';
import employees from '@/routes/employees';
import type { BreadcrumbItem } from '@/types';

import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';

import DataTable from '@/components/data-table';
import ColumnVisibilityDropdown from '@/components/column-visibility-dropdown';

import {
    getCoreRowModel,
    useReactTable,
    type ColumnDef,
    type VisibilityState,
} from '@tanstack/react-table';
import Alert, { AlertState } from '@/components/employees/alert';
import Modal, { ModalState } from '@/components/employees/modal';

const title = 'Karyawan';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: employees.index().url,
    },
];

type Props = {
    pagination: Pagination<Employee>;
    onlyTrashed?: boolean;
};

export default ({ pagination }: Props) => {
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

    const search = useQuery().search || '';

    const onModalSuccess = () =>
        setModal({ ...modal, isOpen: false, dataId: undefined });

    const onModalClose = () =>
        setModal({ ...modal, dataId: undefined, isOpen: false });

    const onAlertClose = () =>
        setAlert({
            isOpen: false,
            proccessing: false,
            dataId: undefined,
            delete: true,
        });

    const onAlertProcessing = () => setAlert({ ...alert, proccessing: true });

    const onEdit = (id: unknown) =>
        setModal({
            ...modal,
            dataId: id,
            isOpen: true,
        });

    const onDeleteOrRestore = (id: unknown, action: boolean) =>
        setAlert({
            ...alert,
            dataId: id,
            delete: action,
            isOpen: true,
        });

    const columns: ColumnDef<Employee>[] = [
        {
            header: 'No',
            cell: ({ row }) =>
                (pagination.current_page - 1) * pagination.per_page +
                row.index +
                1,
        },
        {
            accessorFn: (row) => `${row.first_name} ${row.last_name}`,
            header: 'Nama',
        },
        {
            accessorFn: (row) => row.role?.name ?? '-',
            header: 'Role',
        },
        {
            id: 'action',
            header: () => <div className="text-center">Aksi</div>,
            cell: ({ row }) => {
                const employee = row.original;

                return (
                    <div className="flex justify-center gap-2">
                        {!employee.deleted_at && (
                            <Button
                                size="icon"
                                variant="outline"
                                onClick={() => onEdit(employee.id)}
                            >
                                <Pencil />
                            </Button>
                        )}

                        <Button
                            size="icon"
                            variant={isDeletedRoute ? 'outline' : 'destructive'}
                            onClick={() =>
                                onDeleteOrRestore(employee.id, !isDeletedRoute)
                            }
                        >
                            {isDeletedRoute ? <ArchiveRestore /> : <X />}
                        </Button>
                    </div>
                );
            },
        },
    ];

    const defaultColumn = {
        role: true,
    };

    const cachedColumnKey = 'employeeColumnVisibility';

    const cachedColumn = JSON.parse(
        localStorage.getItem(cachedColumnKey) || JSON.stringify(defaultColumn),
    );

    const [columnVisibility, setColumnVisibility] =
        useState<VisibilityState>(cachedColumn);

    const table = useReactTable({
        data: pagination.data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        onColumnVisibilityChange: setColumnVisibility,
        state: {
            columnVisibility,
        },
    });

    useEffect(() => {
        localStorage.setItem(cachedColumnKey, JSON.stringify(columnVisibility));
    }, [columnVisibility]);

    const { url } = usePage();
    const isDeletedRoute = url.includes('deleted');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <Modal
                modalState={modal}
                onModalClose={onModalClose}
                onModalSuccess={onModalSuccess}
                tableData={pagination.data}
            />

            <Alert
                alertState={alert}
                onAlertClose={onAlertClose}
                onAlertProccessing={onAlertProcessing}
            />

            <div className="mb-4 flex justify-between">
                <Button
                    className="size-9 lg:size-auto"
                    onClick={() => setModal({ ...modal, isOpen: true })}
                >
                    <Plus />
                    <span className="hidden lg:inline">Karyawan Baru</span>
                </Button>

                <ColumnVisibilityDropdown table={table} />
            </div>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="p-0 lg:px-6">
                    <Form method="GET">
                        <div className="grid gap-2 lg:flex">
                            <input type="hidden" name="page" value={1} />

                            <Input
                                defaultValue={search}
                                name="search"
                                placeholder="Cari..."
                            />

                            <Button variant={'secondary'}>
                                <Search /> Cari
                            </Button>
                        </div>
                    </Form>
                </CardHeader>

                <CardContent className="border-t p-0 lg:border-0 lg:px-6">
                    <Tabs
                        value={isDeletedRoute ? 'deleted' : 'available'}
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

                    <DataTable columns={columns} table={table} />

                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
};
