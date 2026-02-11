import { Head, router } from '@inertiajs/react';
import { toast } from 'sonner';
import RoleInputModal from '@/components/partials/role-input-modal';
import RolesTable from '@/components/roles-table';
import TablePagination from '@/components/table-pagination';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { Role } from '@/lib/model';
import roles from '@/routes/roles';
import type { BreadcrumbItem } from '@/types';

const role = () => ({
    url: roles.index().url,
    store: () => ({ url: roles.store().url }),
});
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Role',
        href: roles.index().url,
    },
];
type Props = {
    roles: {

        data: Role[]
        current_page: number
        total: number
        last_page: number
        per_page: number
        prev_page_url: string | null
        next_page_url: string | null
        links: { url: string | null; label: string; active: boolean }[]
    }
}
function RoleIndex({ roles }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Role" />
            <Card>
                <CardContent>
                    <RoleInputModal
                        triggerText="Role Baru"
                        submitUrl={role().store().url}
                        onSuccess={() =>
                            toast.success("Role berhasil ditambahkan", { position: "top-right" })
                        }
                    />
                    <RolesTable roles={roles} />
                    <TablePagination
                        data={roles}
                        showing={roles.data.length}
                        onPerPageChange={(val) => {
                            router.get(role().url, { per_page: val, page: 1 }, { preserveScroll: true })
                        }}
                    />
                </CardContent>
            </Card>
        </AppLayout>
    );
}

export default RoleIndex;