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
import type { Pagination, CustomerCategory } from '@/lib/model';
import { usePage } from '@inertiajs/react';

type Props = {
    pagination: Pagination<CustomerCategory>;
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
                    <TableHead>No.</TableHead>
                    <TableHead>Nama</TableHead>
                    <TableHead className="text-center">Aksi</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                {pagination.data.map(
                    (customerCategory: CustomerCategory, index: number) => (
                        <TableRow key={customerCategory.id ?? index}>
                            <TableCell>{startIndex + index + 1}.</TableCell>
                            <TableCell>{customerCategory.name}</TableCell>
                            <TableCell>
                                <div className="flex justify-center gap-2">
                                    {!customerCategory.deleted_at && (
                                        <Button
                                            size="icon"
                                            variant="outline"
                                            onClick={() =>
                                                onEdit(customerCategory.id)
                                            }
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
                                                customerCategory.id,
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
                    ),
                )}
            </TableBody>
        </Table>
    );
};
