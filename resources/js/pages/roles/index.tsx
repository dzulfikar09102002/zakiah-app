import { Form, Head } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';

import Alert from '@/components/roles/alert';
import type { AlertState } from '@/components/roles/alert';

import Modal from '@/components/roles/modal';
import type { ModalState } from '@/components/roles/modal';

import Table from '@/components/roles/table';
import TablePagination from '@/components/table-pagination';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';

import type { Role, Pagination } from '@/lib/model';
import roles from '@/routes/roles';
import type { BreadcrumbItem } from '@/types';

import { Card, CardContent, CardHeader } from '@/components/ui/card';

const title = 'Role';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: roles.index().url,
    },
];

type Props = {
    pagination: Pagination<Role>;
    parents: Role[];
};

export default ({ pagination, parents }: Props) => {

    const [modal, setModal] = useState<ModalState>({
        isOpen: false,
        dataId: undefined as unknown,
    });

    const [alert, setAlert] = useState<AlertState>({
        delete: true,
        isOpen: false,
        dataId: undefined as unknown,
        proccessing: false,
    });

    const search = useQuery().search || '';

    const onModalSuccess = () =>
        setModal({ isOpen: false, dataId: undefined });

    const onModalClose = () =>
        setModal({ isOpen: false, dataId: undefined });

    const onEdit = (id: unknown) =>
        setModal({
            isOpen: true,
            dataId: id,
        });

    const onAlertClose = () =>
        setAlert({
            isOpen: false,
            proccessing: false,
            dataId: undefined,
            delete: true,
        });

    const onAlertProccessing = () =>
        setAlert({ ...alert, proccessing: true });

    const onDelete = (id: unknown) =>
        setAlert({
            isOpen: true,
            dataId: id,
            delete: true,
            proccessing: false,
        });
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <Modal
                modalState={modal}
                onModalClose={onModalClose}
                onModalSuccess={onModalSuccess}
                tableData={pagination.data}
                parents={parents}
            />

            <Alert
                alertState={alert}
                onAlertClose={onAlertClose}
                onAlertProccessing={onAlertProccessing}
            />

            <div className="mb-4">
                <Button
                    className="size-9 lg:size-auto"
                    onClick={() => setModal({ isOpen: true, dataId: undefined })}
                >
                    <Plus />
                    <span className="hidden lg:inline">
                        Role Baru
                    </span>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <Form method="GET" className="grid lg:flex gap-2">
                        <input type="hidden" name="page" value={1} />
                        <Input
                            defaultValue={search}
                            name="search"
                            placeholder="Cari..."
                        />
                        <Button variant="secondary">
                            <Search /> Cari
                        </Button>
                    </Form>
                </CardHeader>

                <CardContent>
                    <Table
                        pagination={pagination}
                        onEdit={onEdit}
                        onDelete={onDelete}
                    />

                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}