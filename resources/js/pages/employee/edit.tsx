import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { roles } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Karyawan Baru',
        href: roles().url,
    },
];
function EditEmployee() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Karyawan" />
        </AppLayout>
    );
}

export default EditEmployee;