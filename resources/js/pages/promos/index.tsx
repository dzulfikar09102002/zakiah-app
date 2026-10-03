import { Form, Head, Link } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import Table from '@/components/promos/table';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { Pagination, Promo } from '@/lib/model';
import promos from '@/routes/promos';
import type { BreadcrumbItem } from '@/types';

const title = 'Promosi';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: promos.index().url,
    },
];

const periods = [
    { value: 'all', label: 'Semua' },
    { value: 'running', label: 'Berjalan' },
    { value: 'scheduled', label: 'Terjadwal' },
    { value: 'ended', label: 'Selesai' },
];

type Props = {
    pagination: Pagination<Promo>;
};

export default ({ pagination }: Props) => {
    const query = useQuery();
    const search = query.search || '';
    const period = query.period || 'all';

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <div className="mb-4">
                <Button className="size-9 lg:size-auto" asChild>
                    <Link href={promos.create().url}>
                        <Plus />{' '}
                        <span className="hidden lg:inline">Promo Baru</span>
                    </Link>
                </Button>
            </div>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="p-0 lg:px-6">
                    <Form method="GET">
                        <div className="grid gap-2 lg:flex">
                            <input type="hidden" name="page" value={1} />
                            <input type="hidden" name="period" value={period} />
                            <Input
                                defaultValue={search}
                                name="search"
                                placeholder="Cari nama atau kode promo..."
                            />
                            <Button variant={'secondary'}>
                                <Search /> Cari
                            </Button>
                        </div>
                    </Form>
                </CardHeader>

                <CardContent className="border-t p-0 lg:border-0 lg:px-6">
                    <Tabs value={period} className="mb-4">
                        <TabsList>
                            {periods.map((item) => (
                                <TabsTrigger
                                    key={item.value}
                                    value={item.value}
                                    asChild
                                >
                                    <Link
                                        href={
                                            promos.index({
                                                query:
                                                    item.value === 'all'
                                                        ? {}
                                                        : {
                                                              period: item.value,
                                                          },
                                            }).url
                                        }
                                    >
                                        {item.label}
                                    </Link>
                                </TabsTrigger>
                            ))}
                        </TabsList>
                    </Tabs>

                    <Table pagination={pagination} />

                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    );
};
