import { Pencil, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";

import type { Pagination, Role } from "@/lib/model";

type Props = {
    pagination: Pagination<Role>;
    onEdit: (id: unknown) => void;
    onDelete: (id: unknown) => void;
};

export default ({ pagination, onEdit, onDelete }: Props) => {

    const startIndex =
        (pagination.current_page - 1) * pagination.per_page;

    return (
        <div className="relative w-full overflow-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>No.</TableHead>
                        <TableHead className="w-[800px]">Nama</TableHead>
                        <TableHead className="text-center">Aksi</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    {pagination.data.map((role, index) => (
                        <TableRow key={role.id ?? index}>
                            <TableCell>
                                {startIndex + index + 1}.
                            </TableCell>

                            <TableCell className="min-w-[800px]">
                                {role.name}
                            </TableCell>

                            <TableCell className="space-x-2 text-center">

                                {/* EDIT */}
                                <Button
                                    size="icon"
                                    variant="outline"
                                    onClick={() => onEdit(role.id)}
                                >
                                    <Pencil />
                                </Button>

                                {/* DELETE */}
                                <Button
                                    size="icon"
                                    variant="destructive"
                                    onClick={() => onDelete(role.id)}
                                >
                                    <X />
                                </Button>

                            </TableCell>
                        </TableRow>
                    ))}

                    {!pagination.data.length && (
                        <TableRow>
                            <TableCell
                                colSpan={3}
                                className="text-center py-2 text-muted-foreground"
                            >
                                Data tidak ditemukan
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
        </div>
    );
}