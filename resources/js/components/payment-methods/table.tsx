import { ArchiveRestore, Pencil, X } from 'lucide-react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import type { Pagination, PaymentMethod } from '@/lib/model';
import { Button } from '../ui/button';
import { usePage } from '@inertiajs/react';
import { toRupiah } from '@/lib/utils';

type Props = {
    pagination: Pagination<PaymentMethod>
    onEdit: (id: unknown) => void
    onDeleteOrRestore: (id: unknown, action: boolean) => void
}

export default ({
    pagination,
    onEdit,
    onDeleteOrRestore
}: Props) => {

    const startIndex =
        (pagination.current_page - 1) * pagination.per_page;

    const { url } = usePage();
    const isDeletedRoute = url.includes('deleted');

    return (
        <Table className="stripped">
            <TableHeader>
                <TableRow>
                    <TableHead>No.</TableHead>
                    <TableHead>Nama</TableHead>
                    <TableHead>Jenis</TableHead>
                    <TableHead>Biaya Tetap</TableHead>
                    <TableHead>Biaya (%)</TableHead>
                    <TableHead className="text-center">Aksi</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                {pagination.data.map((pmethod: PaymentMethod, index: number) => (
                    <TableRow key={pmethod.id ?? index}>
                        <TableCell>
                            {startIndex + index + 1}.
                        </TableCell>

                        <TableCell>
                            {pmethod.name}
                        </TableCell>
                        <TableCell>
                            {pmethod.kind.charAt(0).toUpperCase() + pmethod.kind.slice(1)}
                        </TableCell>
                        <TableCell>
                            {toRupiah(pmethod.fixed_fee)}
                        </TableCell>
                        <TableCell>
                            {pmethod.variable_fee}
                        </TableCell>
                        <TableCell>
                            <div className="flex justify-center gap-2">
                                {!pmethod.deleted_at && (
                                    <Button
                                        size="icon"
                                        variant="outline"
                                        onClick={() => onEdit(pmethod.id)}
                                    >
                                        <Pencil />
                                    </Button>
                                )}

                                {/* DELETE / RESTORE */}
                                <Button
                                    size="icon"
                                    variant={isDeletedRoute ? "outline" : "destructive"}
                                    onClick={() =>
                                        onDeleteOrRestore(
                                            pmethod.id,
                                            !isDeletedRoute
                                        )
                                    }
                                >
                                    {isDeletedRoute
                                        ? <ArchiveRestore />
                                        : <X />}
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                ))}

                {!pagination.data.length && (
                    <TableRow>
                        <TableCell
                            colSpan={3}
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