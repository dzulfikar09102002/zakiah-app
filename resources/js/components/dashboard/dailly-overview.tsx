import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import {
    BarChart3,
    DollarSign,
    Package,
    Percent,
    RotateCcw,
    TrendingUp,
} from 'lucide-react';
import { AnimatedValue } from '../ui/count-up';
import { Spinner } from '../ui/spinner';

function StatCard({
    title,
    value,
    icon: Icon,
    suffix,
    isCurrency = true,
    isLoading = false,
}: {
    title: string;
    value: string | number;
    icon: any;
    suffix?: string;
    isCurrency?: boolean;
    isLoading?: boolean;
}) {
    return (
        <Card>
            <CardHeader className="flex-row items-center justify-between">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
                <div className="flex size-8 items-center justify-center rounded-lg bg-secondary">
                    <Icon className="size-4 text-muted-foreground" />
                </div>
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold">
                    {isLoading ? (
                        <div className="flex items-center py-1">
                            <Spinner className="size-6" />
                        </div>
                    ) : (
                        <>
                            {typeof value === 'number' ? (
                                <AnimatedValue
                                    value={value}
                                    isCurrency={isCurrency}
                                />
                            ) : (
                                value
                            )}
                            {suffix}
                        </>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}

type Props = {
    total_sale?: number;
    total_profit?: number;
    total_return?: number;
    total_stock?: number;
    total_hpp?: number;
    total_stock_price?: number;
    potential_profit?: number;
    summaryLoading?: boolean;
    refundLoading?: boolean;
    potentialLoading?: boolean;
};

export default (props: Props) => {
    const totalStockPrice = props.total_stock_price ?? 0;
    const totalHpp = props.total_hpp ?? 0;

    const potentialProfit = totalStockPrice - totalHpp;

    const potentialProfitPercentage =
        totalHpp > 0 ? (potentialProfit / totalHpp).toFixed(2) : '0.00';

    return (
        <div className="space-y-4">
            <div className="grid gap-4 md:grid-cols-3">
                <StatCard
                    title="Total Penjualan"
                    value={props.total_sale ?? 0}
                    icon={DollarSign}
                    isLoading={props.summaryLoading}
                />

                <StatCard
                    title="Total Laba Kotor (Margin)"
                    value={props.total_profit ?? 0}
                    icon={TrendingUp}
                    isLoading={props.summaryLoading}
                />

                <StatCard
                    title="Pengembalian"
                    value={props.total_return ?? 0}
                    icon={RotateCcw}
                    isLoading={props.refundLoading}
                />
            </div>

            <div className="grid gap-4 md:grid-cols-4">
                <StatCard
                    title="Total Stok"
                    value={props.total_stock ?? 0}
                    isCurrency={false}
                    icon={Package}
                    isLoading={props.potentialLoading}
                />

                <StatCard
                    title="Nominal HPP"
                    value={totalHpp}
                    icon={BarChart3}
                    isLoading={props.potentialLoading}
                />

                <StatCard
                    title="Harga Stok"
                    value={totalStockPrice}
                    icon={DollarSign}
                    isLoading={props.potentialLoading}
                />

                <StatCard
                    title="Potensi Laba"
                    value={potentialProfit}
                    icon={Percent}
                    suffix={` (${potentialProfitPercentage}%)`}
                    isLoading={props.potentialLoading}
                />
            </div>
        </div>
    );
};
