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
import type { Customer, Pagination } from '@/lib/model';
import { usePage } from '@inertiajs/react';

type Props = {
    pagination: Pagination<Customer>;
    onEdit: (id: any) => void;
    onDeleteOrRestore: (id: any, action: boolean) => void;
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
                    <TableHead>No Telepon</TableHead>
                    <TableHead>Jenis</TableHead>
                    <TableHead>Asal Toko</TableHead>
                    <TableHead className="text-center">Aksi</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                {pagination.data.map((customer: Customer, index: number) => (
                    <TableRow key={customer.id ?? index}>
                        <TableCell>{startIndex + index + 1}.</TableCell>
                        <TableCell>
                            {customer.first_name + ' ' + customer.last_name}
                        </TableCell>
                        <TableCell>
                            {(() => {
                                const cleanNumber =
                                    `${customer.phone_number_country_code}${customer.phone_number}`.replace(
                                        /\D/g,
                                        '',
                                    );

                                return (
                                    <a
                                        href={`https://wa.me/${cleanNumber}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="cursor-pointer hover:underline"
                                    >
                                        {'+' +
                                            customer.phone_number_country_code +
                                            customer.phone_number}
                                    </a>
                                );
                            })()}
                        </TableCell>
                        <TableCell>{customer.customer_category?.name ?? "-"}</TableCell>
                        <TableCell>{customer.location.name}</TableCell>
                        <TableCell>
                            <div className="flex justify-center gap-2">
                                {!customer.deleted_at && (
                                    <Button
                                        size="icon"
                                        variant="outline"
                                        onClick={() => onEdit(customer.id)}
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
                                            customer.id,
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
                            colSpan={6}
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
