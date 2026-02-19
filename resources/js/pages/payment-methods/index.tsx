import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArchiveRestore, Pencil, Plus, Search, X } from 'lucide-react';
import { useState } from 'react';
import type { ModalState } from '@/components/product-categories/modal';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import Modal from '@/components/payment-methods/modal';
import Alert from '@/components/payment-methods/alert';
import {
    Card,
    CardContent,
    CardHeader,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { Pagination, PaymentMethod } from '@/lib/model';
import paymentmethods from '@/routes/paymentmethods';
import type { BreadcrumbItem } from '@/types';
import { AlertState } from '@/components/payment-methods/alert';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';

const title = 'Metode Pembayaran';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: paymentmethods.index().url,
    },
];

type Props = {
    pagination: Pagination<PaymentMethod>
    onlyTrashed?: boolean
}

export default ({ pagination, onlyTrashed }: Props) => {
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
    const startIndex = (pagination.current_page - 1) * pagination.per_page
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
                onAlertClose={onAlertlClose}
                onAlertProccessing={onAlertProccessing}
            />
            <div className="mb-4">
                <Button className="size-9 lg:size-auto" onClick={() => setModal({ ...modal, isOpen: true })}>
                    <Plus /> <span className="hidden lg:inline">Metode Baru</span>
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
                    <Tabs value={isDeletedRoute ? 'deleted' : 'available'} className="mb-4">
                        <TabsList>
                            <TabsTrigger value="available" asChild>
                                <Link href={paymentmethods.index().url}>
                                    Tersedia
                                </Link>
                            </TabsTrigger>
                            <TabsTrigger value="deleted" asChild>
                                <Link href={paymentmethods.deleted().url}>
                                    Terhapus
                                </Link>
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                    <div className="relative w-full overflow-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>No.</TableHead>
                                    <TableHead className="w-[800px]">Nama</TableHead>
                                    <TableHead className="text-center">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pagination.data.map((pmethod, idx) => (
                                    <TableRow key={pmethod.id ?? idx}>
                                        <TableCell>{startIndex + idx + 1}.</TableCell>
                                        <TableCell className="min-w-[800px]">
                                            {pmethod.name}
                                        </TableCell>
                                        <TableCell className="space-x-2 text-center">
                                            <Button
                                                size="icon"
                                                variant="outline"
                                                onClick={() => onEdit(pmethod.id)}>
                                                <Pencil />
                                            </Button>
                                            <Button
                                                size="icon"
                                                variant={isDeletedRoute ? "outline" : "destructive"}
                                                onClick={() => onDeleteOrRestore(pmethod.id, !isDeletedRoute)}>
                                                {isDeletedRoute ? <ArchiveRestore /> : <X />}
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {!pagination.data.length && (
                                    <TableRow>
                                        <TableCell colSpan={4} className="text-center py-2 text-muted-foreground">
                                            Data tidak ditemukan
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}
