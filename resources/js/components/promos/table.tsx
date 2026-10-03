import { Link } from '@inertiajs/react';
import { Eye, Pencil } from 'lucide-react';
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
import type { Pagination, Promo } from '@/lib/model';
import { capitalize, toRupiah } from '@/lib/utils';
import promos from '@/routes/promos';

/** Tanggal promo dari DB berformat "yyyy-MM-dd HH:mm:ss". */
export const parsePromoDate = (value: string) =>
    new Date(value.replace(' ', 'T'));

export const formatPromoDate = (value: string | null) =>
    value
        ? parsePromoDate(value).toLocaleDateString('id-ID', {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
          })
        : '-';

/** Status mengikuti cara kasir menentukan promo berlaku (start_at/end_at). */
export const promoPeriod = (promo: Promo) => {
    const now = new Date();

    if (parsePromoDate(promo.start_at) > now) {
        return { label: 'Terjadwal', variant: 'secondary' as const };
    }

    if (promo.end_at && parsePromoDate(promo.end_at) < now) {
        return { label: 'Selesai', variant: 'outline' as const };
    }

    return { label: 'Berjalan', variant: 'default' as const };
};

export const promoRewardLabel = (promo: Promo) => {
    const reward = promo.promo_reward;
    if (!reward) return '-';

    if (reward.template === 'discount_percentage') {
        return `${reward.reward_amount}%${
            reward.reward_maximum_amount
                ? ` (maks. ${toRupiah(reward.reward_maximum_amount)})`
                : ''
        }`;
    }

    return toRupiah(reward.reward_amount);
};

type Props = {
    pagination: Pagination<Promo>;
};

export default ({ pagination }: Props) => {
    const startIndex = (pagination.current_page - 1) * pagination.per_page;

    return (
        <Table className="stripped">
            <TableHeader>
                <TableRow>
                    <TableHead className="w-[6%]">No.</TableHead>
                    <TableHead className="w-[28%]">Promo</TableHead>
                    <TableHead className="w-[16%]">Diskon</TableHead>
                    <TableHead className="w-[18%]">Periode</TableHead>
                    <TableHead className="w-[14%]">Lokasi</TableHead>
                    <TableHead className="w-[8%]">Status</TableHead>
                    <TableHead className="w-[10%] text-center">Aksi</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                {pagination.data.map((promo, index) => {
                    const period = promoPeriod(promo);

                    return (
                        <TableRow key={promo.id}>
                            <TableCell>{startIndex + index + 1}.</TableCell>
                            <TableCell>
                                <p className="font-medium">{promo.name}</p>
                                <p className="text-xs text-muted-foreground">
                                    {promo.code}
                                </p>
                            </TableCell>
                            <TableCell>{promoRewardLabel(promo)}</TableCell>
                            <TableCell>
                                {formatPromoDate(promo.start_at)} –{' '}
                                {promo.end_at
                                    ? formatPromoDate(promo.end_at)
                                    : 'Tanpa batas'}
                            </TableCell>
                            <TableCell>
                                {promo.owner_location
                                    ? capitalize(promo.owner_location.name)
                                    : '-'}
                            </TableCell>
                            <TableCell>
                                <Badge variant={period.variant}>
                                    {period.label}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div className="flex justify-center gap-2">
                                    <Button
                                        size="icon"
                                        variant="outline"
                                        asChild
                                    >
                                        <Link href={promos.show(promo.id).url}>
                                            <Eye />
                                        </Link>
                                    </Button>
                                    <Button
                                        size="icon"
                                        variant="outline"
                                        asChild
                                    >
                                        <Link href={promos.edit(promo.id).url}>
                                            <Pencil />
                                        </Link>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    );
                })}
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
