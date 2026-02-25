import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';
import Modal from '@/components/product-units/modal';
import type { ModalState } from '@/components/product-units/modal';
import Alert from '@/components/product-units/alert';
import type { AlertState } from '@/components/product-units/alert';
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
import type { Pagination, Unit } from '@/lib/model';
import type { BreadcrumbItem } from '@/types';
import Table from '@/components/product-units/table';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import productUnits from '@/routes/product-units';

const title = 'Produk Unit';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: productUnits.index().url,
    },
];

type Props = {
    pagination: Pagination<Unit>
    onlyTrashed?: boolean
}

export default ({ pagination }: Props) => {
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

    const onEdit = (id: unknown) =>
        setModal({
            ...modal,
            dataId: id,
            isOpen: true
        });
    const onAlertClose = () =>
        setAlert({
            isOpen: false,
            proccessing: false,
            dataId: undefined,
            delete: true
        });

    const onAlertProccessing = () =>
        setAlert({ ...alert, proccessing: true });

    const onDeleteOrRestore = (id: unknown, action: boolean) => setAlert({
        ...alert,
        dataId: id,
        delete: action,
        isOpen: true
    })
    const { url } = usePage()
    const isDeletedRoute = url.includes('deleted')
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
                onAlertProccessing={onAlertProccessing}
            />

            <div className="mb-4">
                <Button
                    className="size-9 lg:size-auto"
                    onClick={() =>
                        setModal({
                            isOpen: true,
                            dataId: undefined
                        })
                    }
                >
                    <Plus /> <span className="hidden lg:inline">Unit Baru</span>
                </Button>
            </div>

            <Card className="bg-background lg:bg-card p-0 lg:py-6 border-0 lg:border">
                <CardHeader className='p-0 lg:px-6'>
                    <Form method='GET'>
                        <div className="grid lg:flex gap-2">
                            <input type="hidden" name="page" value={1} />
                            <Input defaultValue={search} name='search' placeholder='Cari...' />
                            <Button variant={'secondary'}>
                                <Search /> Cari
                            </Button>
                        </div>
                    </Form>
                </CardHeader>

                <CardContent className="p-0 lg:px-6 border-t lg:border-0">
                    <Tabs value={isDeletedRoute ? 'deleted' : 'available'} className="mb-4">
                        <TabsList>
                            <TabsTrigger value="available" asChild>
                                <Link href={productUnits.index().url}>
                                    Tersedia
                                </Link>
                            </TabsTrigger>
                            <TabsTrigger value="deleted" asChild>
                                <Link href={productUnits.deleted().url}>
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