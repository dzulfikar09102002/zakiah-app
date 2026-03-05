import { Form, Head } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { Location, Pagination } from '@/lib/model';
import locations from '@/routes/locations';
import type { BreadcrumbItem } from '@/types';
import Table from '@/components/locations/table';
import Alert, { AlertState } from '@/components/locations/alert';
import Modal, { ModalState } from '@/components/locations/modal';

const title = 'Lokasi';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: locations.index().url,
    },
];

type Props = {
    pagination: Pagination<Location>;
    onlyTrashed?: boolean;
    phoneCountryCodes: {
        value: string;
        label: string;
    }[];
};

export default ({ pagination, phoneCountryCodes }: Props) => {
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
        setModal({ ...modal, isOpen: false, dataId: undefined });

    const onModalClose = () =>
        setModal({ ...modal, dataId: undefined, isOpen: false });

    const onAlertlClose = () =>
        setAlert({
            isOpen: false,
            proccessing: false,
            dataId: undefined,
            delete: true,
        });

    const onAlertProccessing = () => setAlert({ ...alert, proccessing: true });

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
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <Modal
                modalState={modal}
                onModalClose={onModalClose}
                onModalSuccess={onModalSuccess}
                tableData={pagination.data}
                phoneCountryCodes={phoneCountryCodes}
            />

            <Alert
                alertState={alert}
                onAlertClose={onAlertlClose}
                onAlertProccessing={onAlertProccessing}
            />

            <div className="mb-4">
                <Button
                    className="size-9 lg:size-auto"
                    onClick={() => setModal({ ...modal, isOpen: true })}
                >
                    <Plus />{' '}
                    <span className="hidden lg:inline">Lokasi Baru</span>
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
                                placeholder="Cari..."
                            />
                            <Button variant={'secondary'}>
                                <Search /> Cari
                            </Button>
                        </div>
                    </Form>
                </CardHeader>
                <CardContent className="border-t p-0 lg:border-0 lg:px-6">
                    <div className="relative w-full overflow-auto">
                        <Table
                            pagination={pagination}
                            onEdit={onEdit}
                            onDeleteOrRestore={onDeleteOrRestore}
                        ></Table>
                    </div>
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
};
