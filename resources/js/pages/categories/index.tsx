import { Head, router } from '@inertiajs/react';
import { toast } from 'sonner';
import ProductCategoryTable from '@/components/partials/product-categories-table';
import RoleInputModal from '@/components/partials/role-input-modal';
import TablePagination from '@/components/table-pagination';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { Category } from '@/lib/model';
import type { BreadcrumbItem } from '@/types';
import categories from '@/routes/categories';

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
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kategori" />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardContent>
                        <RoleInputModal
                            triggerText="Kategori Baru"
                            submitUrl={category().store().url}
                            onSuccess={() =>
                                toast.success("Kategori berhasil ditambahkan", { position: "top-right" })
                            }
                        />
                        <ProductCategoryTable categories={categories} />
                        <TablePagination
                            data={categories}
                            showing={categories.data.length}
                            onPerPageChange={(val) => {
                                router.get(category().url, { per_page: val, page: 1 }, { preserveScroll: true })
                            }}
                        />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

export default Category;