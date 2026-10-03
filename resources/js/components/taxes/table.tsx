import { Archive, ArchiveRestore, Pencil } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { Pagination, Tax } from '@/lib/model';

type Props = {
    pagination: Pagination<Tax>;
    onEdit: (id: number) => void;
    onToggleStatus: (tax: Tax) => void;
};

export default ({ pagination, onEdit, onToggleStatus }: Props) => {
    const startIndex = (pagination.current_page - 1) * pagination.per_page;

    return (
        <Table className="stripped">
            <TableHeader>
                <TableRow>
                    <TableHead className="w-[8%]">No.</TableHead>
                    <TableHead className="w-[42%]">Nama</TableHead>
                    <TableHead className="w-[20%]">Besaran (%)</TableHead>
                    <TableHead className="w-[15%]">Status</TableHead>
                    <TableHead className="w-[15%] text-center">Aksi</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                {pagination.data.map((tax, index) => (
                    <TableRow key={tax.id}>
                        <TableCell>{startIndex + index + 1}.</TableCell>
                        <TableCell className="font-medium">
                            {tax.name}
                        </TableCell>
                        <TableCell>{tax.rate}%</TableCell>
                        <TableCell>
                            <Badge
                                variant={
                                    tax.status === 'active'
                                        ? 'default'
                                        : 'secondary'
                                }
                            >
                                {tax.status === 'active'
                                    ? 'Aktif'
                                    : 'Diarsipkan'}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <div className="flex justify-center gap-2">
                                <Button
                                    size="icon"
                                    variant="outline"
                                    onClick={() => onEdit(tax.id)}
                                >
                                    <Pencil />
                                </Button>
                                <Button
                                    size="icon"
                                    variant={
                                        tax.status === 'active'
                                            ? 'destructive'
                                            : 'outline'
                                    }
                                    onClick={() => onToggleStatus(tax)}
                                >
                                    {tax.status === 'active' ? (
                                        <Archive />
                                    ) : (
                                        <ArchiveRestore />
                                    )}
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
                {!pagination.data.length && (
                    <TableRow>
                        <TableCell
                            colSpan={5}
                            className="py-4 text-center text-muted-foreground"
                        >
                            Data tidak ditemukan
                        </TableCell>
                    </TableRow>
                )}
            </TableBody>
        </Table>
    );
};
