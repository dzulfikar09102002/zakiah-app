import { toRupiah } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import {
    BarChart3,
    DollarSign,
    Package,
    Percent,
    RotateCcw,
    TrendingUp,
} from 'lucide-react';

function StatCard({
    title,
    value,
    icon: Icon,
    suffix,
    isCurrency = true,
}: {
    title: string;
    value: string | number;
    icon: any;
    suffix?: string;
    isCurrency?: boolean;
}) {
    return (
        <Card>
            <CardHeader className="flex-row items-center justify-between">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
                <div className="flex size-8 items-center justify-center rounded-lg bg-secondary">
                    <Icon className="size-4 animate-pulse" />
                </div>
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold">
                    {typeof value === 'number'
                        ? isCurrency
                            ? toRupiah(value)
                            : value.toLocaleString('id-ID')
                        : value}
                    {suffix}
                </div>
            </CardContent>
        </Card>
    );
}

type Props = {
    total_sale: number;
    total_profit: number;
    total_return: number;
    total_stock: number;
    total_hpp: number;
    total_stock_price: number;
    potential_profit: number;
};

export default (props: Props) => {
    const potentialProfit = props.total_stock_price - props.total_hpp;

    const potentialProfitPercentage =
        props.total_hpp > 0
            ? (potentialProfit / props.total_hpp).toFixed(2)
            : '0.00';
    return (
        <>
            {/* Stats */}
            <div className="grid gap-4 md:grid-cols-3">
                <StatCard
                    title="Total Penjualan"
                    value={props.total_sale}
                    icon={DollarSign}
                />
                <StatCard
                    title="Total Laba Bersih"
                    value={props.total_profit}
                    icon={TrendingUp}
                />
                <StatCard
                    title="Pengembalian"
                    value={props.total_return}
                    icon={RotateCcw}
                />
            </div>

            <div className="grid gap-4 md:grid-cols-4">
                <StatCard
                    title="Total Stok"
                    value={props.total_stock}
                    isCurrency={false}
                    icon={Package}
                />
                <StatCard
                    title="Nominal HPP"
                    value={props.total_hpp}
                    icon={BarChart3}
                />
                <StatCard
                    title="Harga Stok"
                    value={props.total_stock_price}
                    icon={DollarSign}
                />
                <StatCard
                    title="Potensi Laba"
                    value={potentialProfit}
                    icon={Percent}
                    suffix={` (${potentialProfitPercentage}%)`}
                />
            </div>
        </>
    );
};
