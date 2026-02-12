import { Head, router } from '@inertiajs/react';
import { toast } from 'sonner';
// import ProductCategoryTable from '@/components/partials/product-categories-table';
import RoleInputModal from '@/components/partials/role-input-modal';
import TablePagination from '@/components/table-pagination';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { Category } from '@/lib/model';
import type { BreadcrumbItem } from '@/types';
import categories from '@/routes/categories';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Pencil, Trash, X } from 'lucide-react';

const category = () => ({
    url: categories.index().url,
    store: () => ({ url: categories.store().url }),
});
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kategori',
        href: categories.index().url
    },
];
type Props = {
    categories: {

        data: Category[]
        current_page: number
        total: number
        last_page: number
        per_page: number
        prev_page_url: string | null
        next_page_url: string | null
        links: { url: string | null; label: string; active: boolean }[]
    }
}
function Category({ categories }: Props) {

    const startIndex = (categories.current_page - 1) * categories.per_page

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kategori" />
            <RoleInputModal
                triggerText="Kategori Baru"
                submitUrl={category().store().url}
                onSuccess={() =>
                    toast.success("Kategori berhasil ditambahkan", { position: "top-right" })
                }
            />
            <Card>
                <CardContent>
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
                            {categories.data.map((category: Category, index: number) => (
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

                            {categories.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center py-8 text-muted-foreground">
                                        Tidak ada data Kategori
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                    <TablePagination
                        data={categories}
                        showing={categories.data.length}
                        onPerPageChange={(val) => {
                            router.get(category().url, { per_page: val, page: 1 }, { preserveScroll: true })
                        }}
                    />
                </CardContent>
            </Card>
        </AppLayout>
    );
}

export default Category;