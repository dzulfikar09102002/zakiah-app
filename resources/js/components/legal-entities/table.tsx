import { usePage } from '@inertiajs/react';
import { ArchiveRestore, Pencil, X } from 'lucide-react';
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
import type { LegalEntity, Option, Pagination } from '@/lib/model';

type Props = {
    pagination: Pagination<LegalEntity>;
    legalTypes: Option[];
    onEdit: (id: number) => void;
    onDeleteOrRestore: (id: number, action: boolean) => void;
};

export default ({
    pagination,
    legalTypes,
    onEdit,
    onDeleteOrRestore,
}: Props) => {
    const startIndex = (pagination.current_page - 1) * pagination.per_page;
    const { url } = usePage();
    const isDeletedRoute = url.includes('deleted');

    const legalTypeLabel = (value: string) =>
        legalTypes.find((type) => type.value === value)?.label ?? value;

    return (
        <Table className="striped">
            <TableHeader>
                <TableRow>
                    <TableHead>No.</TableHead>
                    <TableHead>Inisial</TableHead>
                    <TableHead>Nama</TableHead>
                    <TableHead>Jenis</TableHead>
                    <TableHead>NPWP</TableHead>
                    <TableHead>PKP</TableHead>
                    <TableHead>Lokasi</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-center">Aksi</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                {pagination.data.map((legalEntity, index) => (
                    <TableRow key={legalEntity.id}>
                        <TableCell>{startIndex + index + 1}.</TableCell>
                        <TableCell>{legalEntity.initial}</TableCell>
                        <TableCell>{legalEntity.name}</TableCell>
                        <TableCell>
                            {legalTypeLabel(legalEntity.legal_type)}
                        </TableCell>
                        <TableCell>{legalEntity.npwp ?? '-'}</TableCell>
                        <TableCell>
                            <Badge
                                variant={
                                    legalEntity.is_pkp ? 'default' : 'outline'
                                }
                            >
                                {legalEntity.is_pkp ? 'PKP' : 'Non PKP'}
                            </Badge>
                        </TableCell>
                        <TableCell className="max-w-64 whitespace-normal">
                            {legalEntity.locations?.length
                                ? legalEntity.locations
                                      .map((location) => location.name)
                                      .join(', ')
                                : '-'}
                        </TableCell>
                        <TableCell>
                            <Badge
                                variant={
                                    legalEntity.status === 'active'
                                        ? 'secondary'
                                        : 'outline'
                                }
                            >
                                {legalEntity.status === 'active'
                                    ? 'Aktif'
                                    : 'Diarsipkan'}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <div className="flex justify-center gap-2">
                                {!isDeletedRoute && (
                                    <Button
                                        size="icon"
                                        variant="outline"
                                        onClick={() => onEdit(legalEntity.id)}
                                    >
                                        <Pencil />
                                    </Button>
                                )}
                                <Button
                                    size="icon"
                                    variant={
                                        isDeletedRoute
                                            ? 'outline'
                                            : 'destructive'
                                    }
                                    onClick={() =>
                                        onDeleteOrRestore(
                                            legalEntity.id,
                                            !isDeletedRoute,
                                        )
                                    }
                                >
                                    {isDeletedRoute ? <ArchiveRestore /> : <X />}
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                ))}

                {!pagination.data.length && (
                    <TableRow>
                        <TableCell
                            colSpan={9}
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
