import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';
import Alert from '@/components/legal-entities/alert';
import type { AlertState } from '@/components/legal-entities/alert';
import Modal from '@/components/legal-entities/modal';
import type { ModalState } from '@/components/legal-entities/modal';
import Table from '@/components/legal-entities/table';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { LegalEntity, Option, Pagination } from '@/lib/model';
import legalEntities from '@/routes/legal-entities';
import type { BreadcrumbItem } from '@/types';

const title = 'CV / Badan Usaha';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: legalEntities.index().url,
    },
];

type Props = {
    pagination: Pagination<LegalEntity>;
    onlyTrashed?: boolean;
    legalTypes: Option[];
    phoneCountryCodes: Option[];
};

const closedAlert: AlertState = {
    delete: true,
    isOpen: false,
    dataId: undefined,
    proccessing: false,
};

export default ({ pagination, legalTypes, phoneCountryCodes }: Props) => {
    const [modal, setModal] = useState<ModalState>({ isOpen: false });
    const [alert, setAlert] = useState<AlertState>(closedAlert);

    const search = useQuery().search || '';
    const { url } = usePage();
    const isDeletedRoute = url.includes('deleted');

    const closeModal = () => setModal({ isOpen: false, dataId: undefined });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <Modal
                modalState={modal}
                onModalClose={closeModal}
                onModalSuccess={closeModal}
                tableData={pagination.data}
                legalTypes={legalTypes}
                phoneCountryCodes={phoneCountryCodes}
            />

            <Alert
                alertState={alert}
                onAlertClose={() => setAlert(closedAlert)}
                onAlertProccessing={() =>
                    setAlert({ ...alert, proccessing: true })
                }
            />

            <div className="mb-4">
                <Button
                    className="size-9 lg:size-auto"
                    onClick={() => setModal({ isOpen: true })}
                >
                    <Plus /> <span className="hidden lg:inline">CV Baru</span>
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
                                placeholder="Cari nama, inisial, atau NPWP..."
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
                                <Link href={legalEntities.index().url}>
                                    Tersedia
                                </Link>
                            </TabsTrigger>
                            <TabsTrigger value="deleted" asChild>
                                <Link href={legalEntities.deleted().url}>
                                    Terhapus
                                </Link>
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                    <Table
                        pagination={pagination}
                        legalTypes={legalTypes}
                        onEdit={(id) => setModal({ isOpen: true, dataId: id })}
                        onDeleteOrRestore={(id, action) =>
                            setAlert({
                                ...closedAlert,
                                dataId: id,
                                delete: action,
                                isOpen: true,
                            })
                        }
                    />

                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
};
