import { Form, Head } from '@inertiajs/react';
import { Pencil, Plus, Search, X } from 'lucide-react';
import { useState } from 'react';
import type { ModalState } from '@/components/product-categories/modal';
import TablePagination from '@/components/table-pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type { Pagination, Unit } from '@/lib/model';
import units from '@/routes/units';
import type { BreadcrumbItem } from '@/types';

const title = 'Produk Unit';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: units.index().url,
    },
];

type Props = {
    pagination: Pagination<Unit>
}

export default ({ pagination }: Props) => {
    const [modal, setModal] = useState<ModalState>({
        isOpen: false,
        dataId: undefined as unknown
    });
    const search = useQuery().search || ''
    const startIndex = (pagination.current_page - 1) * pagination.per_page
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <div className="mb-4">
                <Button className="size-9 lg:size-auto" onClick={() => setModal({ ...modal, isOpen: true })}>
                    <Plus /> <span className="hidden lg:inline">Unit Baru</span>
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
                    <div className="relative w-full overflow-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>No.</TableHead>
                                    <TableHead >Nama</TableHead>
                                    <TableHead >Status</TableHead>
                                    <TableHead className="text-center">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pagination.data.map((unit, idx) => (
                                    <TableRow key={unit.id ?? idx}>
                                        <TableCell>{startIndex + idx + 1}.</TableCell>
                                        <TableCell>
                                            {unit.name}
                                        </TableCell>
                                        <TableCell><Badge variant='secondary'>{unit.status}</Badge></TableCell>
                                        <TableCell className="space-x-2 text-center">
                                            <Button
                                                size="icon"
                                                variant="outline">
                                                <Pencil />
                                            </Button>
                                            <Button size="icon" variant='destructive'> <X />
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
