import { ArchiveRestore, Pencil, X } from 'lucide-react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Pagination, Unit } from '@/lib/model';
import { usePage } from '@inertiajs/react';

type Props = {
    pagination: Pagination<Unit>
    onEdit: (id: unknown) => void
    onDeleteOrRestore: (id: unknown, action: boolean) => void
}

export default ({ pagination, onEdit, onDeleteOrRestore }: Props) => {
    const startIndex = (pagination.current_page - 1) * pagination.per_page
    const { url } = usePage()
    const isDeletedRoute = url.includes('deleted')
    return (
        <Table className='stripped'>
            <TableHeader>
                <TableRow>
                    <TableHead>No.</TableHead>
                    <TableHead>Nama</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-center">Aksi</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                {pagination.data.map((unit: Unit, index: number) => (
                    <TableRow key={unit.id ?? index}>
                        <TableCell>
                            {startIndex + index + 1}.
                        </TableCell>

                        <TableCell>{unit.name}</TableCell>

                        <TableCell>
                            <Badge variant="secondary">
                                {unit.status}
                            </Badge>
                        </TableCell>

                        <TableCell>
                            <div className="flex justify-center gap-2">
                                <Button
                                    size="icon"
                                    variant="outline"
                                    onClick={() => onEdit(unit.id)}
                                >
                                    <Pencil />
                                </Button>
                                <Button
                                    size="icon"
                                    variant={isDeletedRoute ? "outline" : "destructive"}
                                    onClick={() => onDeleteOrRestore(unit.id, !isDeletedRoute)}>
                                    {isDeletedRoute ? <ArchiveRestore /> : <X />}
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
                {!pagination.data.length && (
                    <TableRow>
                        <TableCell
                            colSpan={4}
                            className="text-center py-4 text-muted-foreground"
                        >
                            Data tidak ditemukan
                        </TableCell>
                    </TableRow>
                )}
            </TableBody>
        </Table>
    );
}