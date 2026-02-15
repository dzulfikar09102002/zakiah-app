import { Eye, EyeOff, Pencil } from 'lucide-react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import type { Category, Pagination } from '@/lib/model';
import { Badge } from '../ui/badge';
import { Button } from '../ui/button';

type Props = {
    pagination: Pagination<Category>
    onEdit: (id: unknown) => void
    onDeleteOrRestore: (id: unknown, action: boolean) => void
}

export default ({ pagination, onEdit, onDeleteOrRestore }: Props) => {
    const startIndex = (pagination.current_page - 1) * pagination.per_page

    return (
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
                        <TableCell>
                            <Badge variant={category.deleted_at ? 'destructive' : 'secondary'}>
                                {category.deleted_at ? 'diarsipkan' : 'aktif'}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <div className="flex gap-2">
                                <Button
                                    size="icon"
                                    variant="outline"
                                    onClick={() => onEdit(category.id)}>
                                    <Pencil />
                                </Button>
                                <Button size="icon" variant={'secondary'} onClick={() => onDeleteOrRestore(category.id, !category.deleted_at)}>
                                    {category.deleted_at ? <Eye /> : <EyeOff />}
                                </Button>
                            </div>
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
    )
}