import { Form, Head, Link } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';
import type { AlertState } from '@/components/loyalties/alert';
import Alert from '@/components/loyalties/alert';
import Table from '@/components/loyalties/table';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { Loyalty, Pagination } from '@/lib/model';
import loyalties from '@/routes/loyalties';
import type { BreadcrumbItem } from '@/types';

const title = 'Loyalty';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: loyalties.index().url,
    },
];

type Props = {
    pagination: Pagination<Loyalty>;
};

export default ({ pagination }: Props) => {
    const search = useQuery().search || '';

    const [alert, setAlert] = useState<AlertState>({
        isOpen: false,
        proccessing: false,
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

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
                <Button className="size-9 lg:size-auto" asChild>
                    <Link href={loyalties.create().url}>
                        <Plus />{' '}
                        <span className="hidden lg:inline">Loyalty Baru</span>
                    </Link>
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
                    <p className="mb-4 text-sm text-muted-foreground">
                        Hanya satu loyalty yang dapat aktif dalam satu waktu.
                    </p>

                    <Table
                        pagination={pagination}
                        onAction={(loyalty, action) =>
                            setAlert({
                                isOpen: true,
                                proccessing: false,
                                loyalty,
                                action,
                            })
                        }
                    />

                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
};
