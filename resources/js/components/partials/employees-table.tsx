import { Pencil, Trash2 } from "lucide-react"
import { Button } from "@/components/ui/button"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import type { Employee } from "@/lib/model"

type Props = {
    employees: {
        data: Employee[]
        current_page: number
        per_page: number
    }
}

function EmployeesTable({ employees }: Props) {
    const startIndex = (employees.current_page - 1) * employees.per_page
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
                    {employees.data.map((employee: Employee, index: number) => (
                        <TableRow key={employee.id ?? index}>
                            <TableCell>{startIndex + index + 1}.</TableCell>
                            <TableCell className="min-w-[800px]">{employee.first_name + " " + employee.last_name}</TableCell>
                            <TableCell>{employee.role_name ?? "-"}</TableCell>
                            <TableCell className="space-x-2 text-center">
                                <Button
                                    size="icon"
                                    variant="outline"
                                >
                                    <Pencil className="size-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="destructive"
                                >
                                    <Trash2 className="size-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                    ))}

                    {employees.data.length === 0 && (
                        <TableRow>
                            <TableCell colSpan={6} className="text-center py-8 text-muted-foreground">
                                Tidak ada data Employee
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
        </div>
    )
}
export default EmployeesTable