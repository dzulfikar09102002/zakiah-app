import { usePage } from '@inertiajs/react';
import { ArchiveRestore, Pencil, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { TableRowsSkeleton } from '@/components/ui/table-rows-skeleton';
import type { Location, Pagination } from '@/lib/model';

type Props = {
    pagination?: Pagination<Location>;
    onEdit: (id: unknown) => void;
    onDeleteOrRestore: (id: unknown, action: boolean) => void;
};
export default ({ pagination, onEdit, onDeleteOrRestore }: Props) => {
    const startIndex = ((pagination?.current_page ?? 1) - 1) * (pagination?.per_page ?? 0);
    const { url } = usePage();
    const isDeletedRoute = url.includes('deleted');
    return (
        <Table className="striped">
            <TableHeader>
                <TableRow>
                    <TableHead>No.</TableHead>
                    <TableHead>Initial</TableHead>
                    <TableHead>Nama</TableHead>
                    <TableHead>Jenis</TableHead>
                    <TableHead>Alamat</TableHead>
                    <TableHead>Kecamatan</TableHead>
                    <TableHead>Kota</TableHead>
                    <TableHead className="text-center">Aksi</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                {(pagination?.data ?? []).map((location, index) => (
                    <TableRow key={location.id ?? index}>
                        <TableCell>{startIndex + index + 1}.</TableCell>
                        <TableCell>{location.initial ?? "-"}</TableCell>
                        <TableCell>{location.name ?? "-"}</TableCell>
                        <TableCell>{location.kind ?? "-"}</TableCell>
                        <TableCell>{location.full_address ?? "-"}</TableCell>
                        <TableCell>{location.district ?? "-"}</TableCell>
                        <TableCell>{location.city ?? "-"}</TableCell>
                        <TableCell>
                            <div className="flex justify-center gap-2">
                            {!location.deleted_at && (
                                    <Button
                                        size="icon"
                                        variant="outline"
                                        onClick={() => onEdit(location.id)}
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
                                            location.id,
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

                {!pagination && <TableRowsSkeleton columns={7} />}
                {pagination && !pagination.data.length && (
                    <TableRow>
                        <TableCell
                            colSpan={7}
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
