import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';

import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';

import {
    Card,
    CardContent,
    CardHeader,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { Pagination, OrderType, PaymentMethod } from '@/lib/model';
import ordertypes from '@/routes/order-types';
import type { BreadcrumbItem } from '@/types';

import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import Table from '@/components/order-types/table';
import Modal, { ModalState } from '@/components/order-types/modal';
import Alert, { AlertState } from '@/components/order-types/alert';

const title = 'Jenis Pesanan';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: ordertypes.index().url,
    },
];

type Props = {
    pagination: Pagination<OrderType>
    onlyTrashed?: boolean
    paymentMethods: PaymentMethod[]
}

export default ({ pagination, onlyTrashed, paymentMethods }: Props) => {
    const [modal, setModal] = useState<ModalState>({
        isOpen: false,
        dataId: undefined as unknown
    });

    const [alert, setAlert] = useState<AlertState>({
        delete: true,
        isOpen: false,
        dataId: undefined as unknown,
        proccessing: false
    });

    const search = useQuery().search || '';

    const onModalSuccess = () =>
        setModal({ ...modal, isOpen: false, dataId: undefined });

    const onModalClose = () =>
        setModal({ ...modal, dataId: undefined, isOpen: false });

    const onAlertlClose = () =>
        setAlert({ isOpen: false, proccessing: false, dataId: undefined, delete: true });

    const onAlertProccessing = () =>
        setAlert({ ...alert, proccessing: true });

    const onEdit = (id: unknown) =>
        setModal({
            ...modal,
            dataId: id,
            isOpen: true
        });

    const onDeleteOrRestore = (id: unknown, action: boolean) =>
        setAlert({
            ...alert,
            dataId: id,
            delete: action,
            isOpen: true
        });

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
                paymentMethods={paymentMethods}
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
                    <Plus /> <span className="hidden lg:inline">Jenis Pesanan Baru</span>
                </Button>
            </div>

            <Card className="bg-background lg:bg-card p-0 lg:py-6 border-0 lg:border">
                <CardHeader className="p-0 lg:px-6">
                    <Form method="GET">
                        <div className="grid lg:flex gap-2">
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

                <CardContent className="p-0 lg:px-6 border-t lg:border-0">
                    <Tabs
                        value={isDeletedRoute ? 'deleted' : 'available'}
                        className="mb-4"
                    >
                        <TabsList>
                            <TabsTrigger value="available" asChild>
                                <Link href={ordertypes.index().url}>
                                    Tersedia
                                </Link>
                            </TabsTrigger>
                            <TabsTrigger value="deleted" asChild>
                                <Link href={ordertypes.deleted().url}>
                                    Terhapus
                                </Link>
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>

                    <Table
                        pagination={pagination}
                        onEdit={onEdit}
                        onDeleteOrRestore={onDeleteOrRestore}
                    />

                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}