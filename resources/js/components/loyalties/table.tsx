import { Link } from '@inertiajs/react';
import {
    Archive,
    Eye,
    MoreHorizontal,
    Pencil,
    Power,
    PowerOff,
    Trash2,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { Loyalty, Pagination } from '@/lib/model';
import { toRupiah } from '@/lib/utils';
import loyalties from '@/routes/loyalties';

export type LoyaltyAction = 'activate' | 'deactivate' | 'archive' | 'destroy';

export const loyaltyStatusLabel: Record<Loyalty['status'], string> = {
    active: 'Aktif',
    in_active: 'Tidak Aktif',
    archived: 'Diarsipkan',
};

const statusVariant: Record<
    Loyalty['status'],
    'default' | 'secondary' | 'outline'
> = {
    active: 'default',
    in_active: 'secondary',
    archived: 'outline',
};

type Props = {
    pagination: Pagination<Loyalty>;
    onAction: (loyalty: Loyalty, action: LoyaltyAction) => void;
};

export default ({ pagination, onAction }: Props) => {
    const startIndex = (pagination.current_page - 1) * pagination.per_page;

    return (
        <Table className="stripped">
            <TableHeader>
                <TableRow>
                    <TableHead className="w-[6%]">No.</TableHead>
                    <TableHead className="w-[30%]">Loyalty</TableHead>
                    <TableHead className="w-[18%]">Minimal Transaksi</TableHead>
                    <TableHead className="w-[12%]">Poin Hadiah</TableHead>
                    <TableHead className="w-[12%]">Produk Hadiah</TableHead>
                    <TableHead className="w-[12%]">Status</TableHead>
                    <TableHead className="w-[10%] text-center">Aksi</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                {pagination.data.map((loyalty, index) => (
                    <TableRow key={loyalty.id}>
                        <TableCell>{startIndex + index + 1}.</TableCell>
                        <TableCell>
                            <p className="font-medium">{loyalty.name}</p>
                            {loyalty.description && (
                                <p className="line-clamp-1 text-xs text-muted-foreground">
                                    {loyalty.description}
                                </p>
                            )}
                        </TableCell>
                        <TableCell>
                            {toRupiah(loyalty.miniminal_transaction_value)}
                        </TableCell>
                        <TableCell>{loyalty.reward_point} poin</TableCell>
                        <TableCell>
                            {loyalty.reward_products_count ?? 0} produk
                        </TableCell>
                        <TableCell>
                            <Badge variant={statusVariant[loyalty.status]}>
                                {loyaltyStatusLabel[loyalty.status]}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <div className="flex justify-center gap-2">
                                <Button size="icon" variant="outline" asChild>
                                    <Link href={loyalties.show(loyalty.id).url}>
                                        <Eye />
                                    </Link>
                                </Button>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button size="icon" variant="outline">
                                            <MoreHorizontal />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem asChild>
                                            <Link
                                                href={
                                                    loyalties.edit(loyalty.id)
                                                        .url
                                                }
                                            >
                                                <Pencil /> Ubah
                                            </Link>
                                        </DropdownMenuItem>
                                        {loyalty.status === 'active' ? (
                                            <DropdownMenuItem
                                                onClick={() =>
                                                    onAction(
                                                        loyalty,
                                                        'deactivate',
                                                    )
                                                }
                                            >
                                                <PowerOff /> Nonaktifkan
                                            </DropdownMenuItem>
                                        ) : (
                                            <DropdownMenuItem
                                                onClick={() =>
                                                    onAction(
                                                        loyalty,
                                                        'activate',
                                                    )
                                                }
                                            >
                                                <Power /> Aktifkan
                                            </DropdownMenuItem>
                                        )}
                                        {loyalty.status !== 'archived' && (
                                            <DropdownMenuItem
                                                onClick={() =>
                                                    onAction(loyalty, 'archive')
                                                }
                                            >
                                                <Archive /> Arsipkan
                                            </DropdownMenuItem>
                                        )}
                                        {loyalty.status !== 'active' && (
                                            <>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    variant="destructive"
                                                    onClick={() =>
                                                        onAction(
                                                            loyalty,
                                                            'destroy',
                                                        )
                                                    }
                                                >
                                                    <Trash2 /> Hapus
                                                </DropdownMenuItem>
                                            </>
                                        )}
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
                {!pagination.data.length && (
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
