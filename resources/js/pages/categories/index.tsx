import { Form, Head, Link, router, usePage } from '@inertiajs/react';
import {
    Plus,
    Search,
} from 'lucide-react';
import { useState } from 'react';
import Alert from '@/components/product-categories/alert';
import type { AlertState } from '@/components/product-categories/alert';
import Modal from '@/components/product-categories/modal';
import type { ModalState } from '@/components/product-categories/modal';
import Table from '@/components/product-categories/table';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import {
    MainCard,
    MainCardContent,
    MainCardHeader
} from '@/components/main-card';
import { Input } from '@/components/ui/input';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type {
    Category,
    Pagination
} from '@/lib/model';
import categories from '@/routes/categories';
import type { BreadcrumbItem } from '@/types';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const title = 'Produk Kategori'
const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: categories.index().url
    },
];

type Props = {
    pagination: Pagination<Category>
    onlyTrashed?: boolean
}

export default ({ pagination, onlyTrashed }: Props) => {
    const { url } = usePage()
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
    const search = useQuery().search || ''

    const onModalSuccess = () => setModal({ ...modal, isOpen: false, dataId: undefined })
    const onModalClose = () => setModal({ ...modal, dataId: undefined, isOpen: false })

    const onAlertlClose = () => setAlert({ isOpen: false, proccessing: false, dataId: undefined, delete: true })
    const onAlertProccessing = () => setAlert({ ...alert, proccessing: true })

    const onEdit = (id: unknown) => setModal({
        ...modal,
        dataId: id,
        isOpen: true
    })

    const onDeleteOrRestore = (id: unknown, action: boolean) => setAlert({
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
            <MainCard>
                <MainCardHeader>
                    <Form method='GET' className='grid lg:flex gap-2'>
                        <input type="hidden" name="page" value={1} />
                        <Input defaultValue={search} name='search' placeholder='Cari...' />
                        <Button variant={'secondary'}><Search /> Cari</Button>
                    </Form>
                </MainCardHeader>
                <MainCardContent>
                    <Tabs value={onlyTrashed ? 'deleted' : 'available'} className='mb-4'>
                        <TabsList>
                            <TabsTrigger value='available' asChild>
                                <Link href={categories.index().url}>
                                    Tersedia
                                </Link>
                            </TabsTrigger>
                            <TabsTrigger value="deleted" asChild>
                                <Link href={categories.deleted().url}>
                                    Terhapus
                                </Link>
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                    <Table
                        pagination={pagination}
                        onDeleteOrRestore={onDeleteOrRestore}
                        onEdit={onEdit}
                    />
                    <TablePagination pagination={pagination} />
                </MainCardContent>
            </MainCard>
        </AppLayout>
    );
}