import { Eye, EyeOff, Pencil } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";

import type { Employee, Pagination } from "@/lib/model";

type Props = {
    pagination: Pagination<Employee>
    onEdit: (id: unknown) => void
    onDeleteOrRestore: (id: unknown, action: boolean) => void
}

export default ({ pagination, onEdit, onDeleteOrRestore }: Props) => {
    const startIndex = (pagination.current_page - 1) * pagination.per_page

    return (
        <div className="relative w-full overflow-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>No.</TableHead>
                        <TableHead className="w-[800px]">Nama</TableHead>
                        <TableHead>Role</TableHead>
                        <TableHead className="text-center">Aksi</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    {pagination.data.map((employee, index) => (
                        <TableRow key={employee.id ?? index}>
                            <TableCell>{startIndex + index + 1}.</TableCell>
                            <TableCell className="min-w-[800px]">
                                {employee.first_name} {employee.last_name}
                            </TableCell>
                            <TableCell>{employee.role_name ?? "-"}</TableCell>
                            <TableCell className="space-x-2 text-center">
                                <Button
                                    size="icon"
                                    variant="outline"
                                    onClick={() => onEdit(employee.id)}>
                                    <Pencil />
                                </Button>
                                <Button size="icon" variant={'secondary'} onClick={() => onDeleteOrRestore(employee.id, !employee.deleted_at)}>
                                    {employee.deleted_at ? <Eye /> : <EyeOff />}
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
    );
}
