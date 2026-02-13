import { Form, Head } from '@inertiajs/react';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Pencil,
    Plus,
    Search,
    X
} from 'lucide-react';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from '@/components/ui/dialog';
import { useState } from 'react';
import {
    Field,
    FieldLabel,
    FieldSet
} from '@/components/ui/field';
import { useQuery } from '@/hooks/use-query';

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
    const [isModalOpen, setIsModalOpen] = useState(false);
    const startIndex = (pagination.current_page - 1) * pagination.per_page
    const search = useQuery().search || ''

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <Dialog open={isModalOpen} onOpenChange={val => setIsModalOpen(val)}>
                <Form>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Kategori Baru</DialogTitle>
                        </DialogHeader>
                        <FieldSet>
                            <Field>
                                <FieldLabel htmlFor='name'>Nama</FieldLabel>
                                <Input name='name' id='name' />
                            </Field>
                        </FieldSet>
                        <DialogFooter>
                            <DialogClose asChild>
                                <Button variant={'outline'}>Batal</Button>
                            </DialogClose>
                            <Button type='submit'>Simpan</Button>
                        </DialogFooter>
                    </DialogContent>
                </Form>
            </Dialog>
            <div className="mb-4">
                <Button className="size-9 lg:size-auto" onClick={() => setIsModalOpen(true)}>
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
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>No.</TableHead>
                                <TableHead>Nama</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {pagination.data.map((category: Category, index: number) => (
                                <TableRow key={category.id ?? index}>
                                    <TableCell>{startIndex + index + 1}.</TableCell>
                                    <TableCell>{category.name}</TableCell>
                                    <TableCell><Badge variant={'secondary'}>{category.status}</Badge></TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            <Button size="icon" variant="outline">
                                                <Pencil />
                                            </Button>
                                            <Button size="icon" variant="destructive">
                                                <X />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}

                            {pagination.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center py-2 text-muted-foreground">
                                        Tidak ada data Kategori
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                    <TablePagination
                        pagination={pagination}
                    />
                </CardContent>
            </Card>
        </AppLayout>
    );
}