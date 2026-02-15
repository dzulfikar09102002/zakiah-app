import { Form, Head } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';
import EmployeesTable from '@/components/partials/employees-table';
import type { AlertState } from '@/components/product-categories/alert';
import type { ModalState } from '@/components/product-categories/modal';
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
import type { Employee, Pagination } from '@/lib/model';
import employees from '@/routes/employees';
import type { BreadcrumbItem } from '@/types';

const title = 'Karyawan';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: employees.index().url,
    },
];

type Props = {
    pagination: Pagination<Employee>
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
    const search = useQuery().search || ''

    // const onModalSuccess = () => setModal({ ...modal, isOpen: false, dataId: undefined })
    // const onModalClose = () => setModal({ ...modal, dataId: undefined, isOpen: false })

    // const onAlertlClose = () => setAlert({ isOpen: false, proccessing: false, dataId: undefined, delete: true })
    // const onAlertProccessing = () => setAlert({ ...alert, proccessing: true })

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
            <div className="mb-4">
                <Button className="size-9 lg:size-auto" onClick={() => setModal({ ...modal, isOpen: true })}>
                    <Plus /> <span className="hidden lg:inline">Karyawan Baru</span>
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
                    <EmployeesTable pagination={pagination}
                        onDeleteOrRestore={onDeleteOrRestore}
                        onEdit={onEdit} />
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
}
