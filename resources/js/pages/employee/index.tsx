import { Head, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import EmployeesTable from '@/components/partials/employees-table';
import TablePagination from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { Employee } from '@/lib/model';
import employees from '@/routes/employees';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Karyawan',
        href: employees.index().url,
    },
];

type Props = {
    employees: {
        data: Employee[]
        current_page: number
        total: number
        last_page: number
        per_page: number
        prev_page_url: string | null
        next_page_url: string | null
        links: { url: string | null; label: string; active: boolean }[]
    }
}

function EmployeeIndex({ employees: employeesData }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Karyawan" />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardContent>
                        <Button
                            className="mb-4"
                            onClick={() => router.get(employees.create().url)}
                        >
                            <Plus /> Karyawan Baru
                        </Button>

                        <EmployeesTable employees={employeesData} />

                        <TablePagination
                            data={employeesData}
                            showing={employeesData.data.length}
                            onPerPageChange={(val) => {
                                router.get(
                                    employees.index().url,
                                    { per_page: val, page: 1 },
                                    { preserveScroll: true }
                                )
                            }}
                        />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

export default EmployeeIndex;
