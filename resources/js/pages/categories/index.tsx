import { Form, Head, router, useForm } from '@inertiajs/react';
import TablePagination from '@/components/table-pagination';
import {
    Card,
    CardContent,
    CardHeader
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type {
    Category,
    Pagination
} from '@/lib/model';
import type { BreadcrumbItem } from '@/types';
import categories from '@/routes/categories';
import { Button } from '@/components/ui/button';
import {
    Plus,
    Search,
} from 'lucide-react';
import { Input } from '@/components/ui/input';
import { useQuery } from '@/hooks/use-query';

import { useState } from 'react';
import Modal, { ModalState } from '@/components/product-categories/modal';
import Alert, { AlertState } from '@/components/product-categories/alert';
import Table from '@/components/product-categories/table';

const title = 'Kategori'
const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: categories.index().url
    },
];

type Props = {
    pagination: Pagination<Category>
}

export default ({ pagination }: Props) => {
    const [modal, setModal] = useState<ModalState>({
        isOpen: false,
        dataId: undefined as any
    });
    const [alert, setAlert] = useState<AlertState>({
        delete: true,
        isOpen: false,
        dataId: undefined as any,
        proccessing: false
    });
    const search = useQuery().search || ''

    const onModalSuccess = () => setModal({ ...modal, isOpen: false, dataId: undefined })
    const onModalClose = () => setModal({ ...modal, dataId: undefined, isOpen: false })

    const onAlertlClose = () => setAlert({ isOpen: false, proccessing: false, dataId: undefined, delete: true })
    const onAlertProccessing = () => setAlert({ ...alert, proccessing: true })

    const onEdit = (id: any) => setModal({
        ...modal,
        dataId: id,
        isOpen: true
    })

    const onDeleteOrRestore = (id: any, action: boolean) => setAlert({
        ...alert,
        dataId: id,
        delete: action,
        isOpen: true
    })

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
                onAlertClose={onAlertlClose}
                onAlertProccessing={onAlertProccessing}
            />
            <div className="mb-4">
                <Button className="size-9 lg:size-auto" onClick={() => setModal({ ...modal, isOpen: true })}>
                    <Plus /> <span className="hidden lg:inline">Kategori Baru</span>
                </Button>
            </div>
            <Card className="bg-background lg:bg-card p-0 lg:py-6 border-0 lg:border">
                <CardHeader className='p-0 lg:px-6'>
                    <Form method='GET'>
                        <div className="grid lg:flex gap-2">
                            <input type="hidden" name="page" value={1} />
                            <Input defaultValue={search} name='search' placeholder='Cari...' />
                            <Button variant={'secondary'}><Search /> Cari</Button>
                        </div>
                    </Form>
                </CardHeader>
                <CardContent className="p-0 lg:px-6 border-t lg:border-0">
                    <Table
                        pagination={pagination}
                        onDeleteOrRestore={onDeleteOrRestore}
                        onEdit={onEdit}
                    />
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}