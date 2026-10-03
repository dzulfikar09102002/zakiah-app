import { Form, Head, Link } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';
import TablePagination from '@/components/table-pagination';
import type { AlertState } from '@/components/taxes/alert';
import Alert from '@/components/taxes/alert';
import type { ModalState } from '@/components/taxes/modal';
import Modal from '@/components/taxes/modal';
import Table from '@/components/taxes/table';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { Pagination, Tax } from '@/lib/model';
import taxes from '@/routes/taxes';
import type { BreadcrumbItem } from '@/types';

const title = 'Pajak';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: taxes.index().url,
    },
];

type Props = {
    pagination: Pagination<Tax>;
};

export default ({ pagination }: Props) => {
    const query = useQuery();
    const search = query.search || '';
    const status = query.status === 'archived' ? 'archived' : 'active';

    const [modal, setModal] = useState<ModalState>({ isOpen: false });
    const [alert, setAlert] = useState<AlertState>({
        isOpen: false,
        proccessing: false,
    });

    const closeModal = () => setModal({ isOpen: false, dataId: undefined });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <Modal
                modalState={modal}
                tableData={pagination.data}
                onModalSuccess={closeModal}
                onModalClose={closeModal}
            />

            <Alert
                alertState={alert}
                onAlertClose={() =>
                    setAlert({ isOpen: false, proccessing: false })
                }
                onAlertProccessing={() =>
                    setAlert({ ...alert, proccessing: true })
                }
            />

            <div className="mb-4">
                <Button
                    className="size-9 lg:size-auto"
                    onClick={() => setModal({ isOpen: true })}
                >
                    <Plus />{' '}
                    <span className="hidden lg:inline">Pajak Baru</span>
                </Button>
            </div>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="p-0 lg:px-6">
                    <Form method="GET">
                        <div className="grid gap-2 lg:flex">
                            <input type="hidden" name="page" value={1} />
                            <input type="hidden" name="status" value={status} />
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
                    <Tabs value={status} className="mb-4">
                        <TabsList>
                            <TabsTrigger value="active" asChild>
                                <Link href={taxes.index().url}>Aktif</Link>
                            </TabsTrigger>
                            <TabsTrigger value="archived" asChild>
                                <Link
                                    href={
                                        taxes.index({
                                            query: { status: 'archived' },
                                        }).url
                                    }
                                >
                                    Diarsipkan
                                </Link>
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>

                    <Table
                        pagination={pagination}
                        onEdit={(id) => setModal({ isOpen: true, dataId: id })}
                        onToggleStatus={(tax) =>
                            setAlert({ isOpen: true, proccessing: false, tax })
                        }
                    />

                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
};
