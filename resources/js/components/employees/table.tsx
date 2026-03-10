import { ArchiveRestore, Pencil, X } from 'lucide-react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import type { Pagination, OrderType } from '@/lib/model';
import { usePage } from '@inertiajs/react';
import { toRupiah } from '@/lib/utils';

type Props = {
    pagination: Pagination<OrderType>;
    onEdit: (id: unknown) => void;
    onDeleteOrRestore: (id: unknown, action: boolean) => void;
};

export default ({ pagination, onEdit, onDeleteOrRestore }: Props) => {
    const startIndex = (pagination.current_page - 1) * pagination.per_page;
    const { url } = usePage();
    const isDeletedRoute = url.includes('deleted');

    return (
        <Table className="stripped">
            <TableHeader>
                <TableRow>
                    <TableHead className="w-[8%]">No.</TableHead>
                    <TableHead className="w-[32%]">Nama</TableHead>
                    <TableHead className="w-[12%]">Biaya Tetap</TableHead>
                    <TableHead className="w-[8%]">Biaya (%)</TableHead>
                    <TableHead className="w-[12%]">Jenis Pembayaran</TableHead>
                    <TableHead className="w-[8%]">Customer</TableHead>
                    <TableHead className="w-[20%] text-center">Aksi</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                {pagination.data.map((orderType: OrderType, index: number) => (
                    <TableRow key={orderType.id ?? index}>
                        <TableCell className="w-[8%]">
                            {startIndex + index + 1}.
                        </TableCell>
                        <TableCell className="w-[32%] font-medium">
                            {orderType.name}
                        </TableCell>
                        <TableCell className="w-[12%]">
                            {toRupiah(orderType.fixed_fee)}
                        </TableCell>
                        <TableCell className="w-[8%]">
                            {orderType.variable_fee}%
                        </TableCell>
                        <TableCell className="w-[12%]">
                            {orderType.payment_method?.name || '-'}
                        </TableCell>
                        <TableCell className="w-[8%]">
                            {orderType.require_customer_data
                                ? 'Member'
                                : 'Reguler'}
                        </TableCell>
                        <TableCell className="w-[20%]">
                            <div className="flex justify-center gap-2">
                                {!orderType.deleted_at && (
                                    <Button
                                        size="icon"
                                        variant="outline"
                                        onClick={() => onEdit(orderType.id)}
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
                                            orderType.id,
                                            !isDeletedRoute,
                                        )
                                    }
                                >
                                    {isDeletedRoute ? (
                                        <ArchiveRestore />
                                    ) : (
                                        <X />
                                    )}
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
                {!pagination.data.length && (
                    <TableRow>
                        <TableCell
                            colSpan={7}
                            className="text-center py-4 text-muted-foreground"
                        >
                            Data tidak ditemukan
                        </TableCell>
                    </TableRow>
                )}
            </TableBody>
        </Table>
    );
};
