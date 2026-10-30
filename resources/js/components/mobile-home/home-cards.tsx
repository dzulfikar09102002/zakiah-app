import { Link } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';
import {
    Boxes,
    ChevronRight,
    Coins,
    PiggyBank,
    Tag,
    type LucideIcon,
} from 'lucide-react';
import { useState } from 'react';
import { Area, AreaChart, Tooltip, XAxis } from 'recharts';
import { ChartContainer } from '@/components/ui/chart';
import { Skeleton } from '@/components/ui/skeleton';
import type { ProfitPotential, Top5Data } from '@/lib/model';
import { cn, toRupiah } from '@/lib/utils';
import reportByLocations from '@/routes/report-by-locations';
import reportByProducts from '@/routes/report-by-products';
import reportTopCustomers from '@/routes/report-top-customers';
import assetsByCategory from '@/routes/reports/assets-by-category';
import type { TrendPoint } from './use-home-dashboard';

const compactNumber = new Intl.NumberFormat('id-ID', {
    notation: 'compact',
    maximumFractionDigits: 1,
});

export const toRupiahCompact = (value: number) =>
    `Rp${compactNumber.format(value)}`;

export const MASKED = '••••••';

export function HomeCard({
    title,
    action,
    children,
    className,
}: {
    title?: string;
    action?: React.ReactNode;
    children: React.ReactNode;
    className?: string;
}) {
    return (
        <section
            className={cn(
                'rounded-3xl border border-border/60 bg-card p-4 shadow-[0_1px_2px_rgb(0_0_0/0.03)]',
                className,
            )}
        >
            {(title || action) && (
                <div className="mb-3 flex items-center justify-between gap-2">
                    {title && (
                        <h2 className="text-[15px] font-semibold tracking-tight">
                            {title}
                        </h2>
                    )}
                    {action}
                </div>
            )}
            {children}
        </section>
    );
}

function Segmented<T extends string>({
    value,
    options,
    onChange,
}: {
    value: T;
    options: { value: T; label: string }[];
    onChange: (value: T) => void;
}) {
    return (
        <div className="flex rounded-lg bg-muted p-0.5">
            {options.map((option) => (
                <button
                    key={option.value}
                    type="button"
                    onClick={() => onChange(option.value)}
                    className={cn(
                        'rounded-md px-3 py-1 text-xs font-medium transition-colors',
                        value === option.value
                            ? 'bg-background text-foreground shadow-sm'
                            : 'text-muted-foreground',
                    )}
                >
                    {option.label}
                </button>
            ))}
        </div>
    );
}

type TrendMetric = 'sales' | 'profit';

export function TrendCard({
    data,
    hidden,
}: {
    data: TrendPoint[] | null;
    hidden: boolean;
}) {
    const [metric, setMetric] = useState<TrendMetric>('sales');
    const total = (data ?? []).reduce((sum, point) => sum + point[metric], 0);
    const color = metric === 'sales' ? 'var(--chart-1)' : 'var(--chart-4)';

    return (
        <HomeCard
            title="Tren penjualan"
            action={
                <Segmented
                    value={metric}
                    onChange={setMetric}
                    options={[
                        { value: 'sales', label: 'Omzet' },
                        { value: 'profit', label: 'Laba' },
                    ]}
                />
            }
        >
            {data === null ? (
                <>
                    <Skeleton className="mb-3 h-6 w-32" />
                    <Skeleton className="h-36 w-full rounded-2xl" />
                </>
            ) : (
                <>
                    <p className="text-xl font-bold tracking-tight tabular-nums">
                        {hidden ? `Rp ${MASKED}` : toRupiah(total)}
                    </p>
                    <p className="mb-2 text-xs text-muted-foreground">
                        {format(parseISO(data[0].date), 'd MMM', {
                            locale: id,
                        })}{' '}
                        –{' '}
                        {format(parseISO(data[data.length - 1].date), 'd MMM', {
                            locale: id,
                        })}
                    </p>
                    <ChartContainer
                        config={{}}
                        className="aspect-auto h-36 w-full"
                    >
                        <AreaChart
                            data={data}
                            margin={{ top: 8, right: 4, left: 4, bottom: 0 }}
                        >
                            <defs>
                                <linearGradient
                                    id="home-trend-fill"
                                    x1="0"
                                    y1="0"
                                    x2="0"
                                    y2="1"
                                >
                                    <stop
                                        offset="0%"
                                        stopColor={color}
                                        stopOpacity={0.35}
                                    />
                                    <stop
                                        offset="100%"
                                        stopColor={color}
                                        stopOpacity={0}
                                    />
                                </linearGradient>
                            </defs>
                            <XAxis
                                dataKey="date"
                                tickLine={false}
                                axisLine={false}
                                interval="preserveStartEnd"
                                minTickGap={24}
                                tick={{ fontSize: 11 }}
                                tickFormatter={(value: string) =>
                                    format(parseISO(value), 'd/M')
                                }
                            />
                            <Tooltip
                                cursor={{
                                    stroke: color,
                                    strokeDasharray: '4 4',
                                }}
                                content={({ active, payload }) => {
                                    if (!active || !payload?.length) {
                                        return null;
                                    }

                                    const point = payload[0]
                                        .payload as TrendPoint;

                                    return (
                                        <div className="rounded-xl border bg-popover px-3 py-2 text-xs shadow-lg">
                                            <p className="text-muted-foreground">
                                                {format(
                                                    parseISO(point.date),
                                                    'EEE, d MMM',
                                                    { locale: id },
                                                )}
                                            </p>
                                            <p className="font-semibold tabular-nums">
                                                {hidden
                                                    ? `Rp ${MASKED}`
                                                    : toRupiah(point[metric])}
                                            </p>
                                        </div>
                                    );
                                }}
                            />
                            <Area
                                type="monotone"
                                dataKey={metric}
                                stroke={color}
                                strokeWidth={2.5}
                                fill="url(#home-trend-fill)"
                                activeDot={{ r: 5, strokeWidth: 2 }}
                                animationDuration={600}
                            />
                        </AreaChart>
                    </ChartContainer>
                </>
            )}
        </HomeCard>
    );
}

function StockStat({
    icon: Icon,
    label,
    value,
    tone,
}: {
    icon: LucideIcon;
    label: string;
    value: string;
    tone: string;
}) {
    return (
        <div className="rounded-2xl bg-muted/60 p-3">
            <span
                className={cn(
                    'mb-2 flex size-8 items-center justify-center rounded-xl',
                    tone,
                )}
            >
                <Icon className="size-4" />
            </span>
            <p className="text-[11px] text-muted-foreground">{label}</p>
            <p className="truncate text-sm font-semibold tabular-nums">
                {value}
            </p>
        </div>
    );
}

export function StockCard({
    potential,
    hidden,
}: {
    potential: ProfitPotential | null;
    hidden: boolean;
}) {
    const money = (value: number) =>
        hidden ? `Rp ${MASKED}` : toRupiahCompact(value);

    return (
        <HomeCard
            title="Nilai stok saat ini"
            action={
                <Link
                    href={assetsByCategory.index().url}
                    className="flex items-center text-xs font-semibold text-primary"
                >
                    Detail <ChevronRight className="size-3.5" />
                </Link>
            }
        >
            {potential === null ? (
                <div className="grid grid-cols-2 gap-2">
                    {Array.from({ length: 4 }, (_, index) => (
                        <Skeleton
                            key={index}
                            className="h-[88px] rounded-2xl"
                        />
                    ))}
                </div>
            ) : (
                <div className="grid grid-cols-2 gap-2">
                    <StockStat
                        icon={Boxes}
                        label="Jumlah stok"
                        value={Number(potential.stock ?? 0).toLocaleString(
                            'id-ID',
                        )}
                        tone="bg-sky-500/12 text-sky-600 dark:text-sky-400"
                    />
                    <StockStat
                        icon={Coins}
                        label="Total HPP"
                        value={money(Number(potential.cogs ?? 0))}
                        tone="bg-amber-500/14 text-amber-600 dark:text-amber-400"
                    />
                    <StockStat
                        icon={Tag}
                        label="Nilai jual"
                        value={money(Number(potential.sell_price ?? 0))}
                        tone="bg-violet-500/12 text-violet-600 dark:text-violet-400"
                    />
                    <StockStat
                        icon={PiggyBank}
                        label="Potensi laba"
                        value={money(
                            Number(potential.sell_price ?? 0) -
                                Number(potential.cogs ?? 0),
                        )}
                        tone="bg-emerald-500/12 text-emerald-600 dark:text-emerald-400"
                    />
                </div>
            )}
        </HomeCard>
    );
}

type TopKey = 'products' | 'categories' | 'locations' | 'customers';

type TopRow = { name: string; caption: string; amount: number };

const topConfig: Record<TopKey, { label: string; href: string }> = {
    products: { label: 'Produk', href: reportByProducts.index().url },
    categories: { label: 'Kategori', href: reportByProducts.index().url },
    locations: { label: 'Toko', href: reportByLocations.index().url },
    customers: { label: 'Pelanggan', href: reportTopCustomers.index().url },
};

function topRows(data: Top5Data, key: TopKey): TopRow[] {
    switch (key) {
        case 'products':
            return (data.products ?? []).map((row) => ({
                name: row.product_name,
                caption: `${Number(row.quantity).toLocaleString('id-ID')} terjual`,
                amount: Number(row.total_line_amount),
            }));
        case 'categories':
            return (data.categories ?? []).map((row) => ({
                name: row.product_category_name,
                caption: `${Number(row.quantity).toLocaleString('id-ID')} terjual`,
                amount: Number(row.total_line_amount),
            }));
        case 'locations':
            return (data.locations ?? []).map((row) => ({
                name: row.location_name,
                caption: 'Omzet toko',
                amount: Number(row.net_sales_after_tax),
            }));
        case 'customers':
            return (data.customers ?? []).slice(0, 5).map((row) => ({
                name: row.customer_name,
                caption: `${row.transaction_count} transaksi`,
                amount: Number(row.total_spend),
            }));
    }
}

const rankTones = [
    'bg-amber-400 text-amber-950',
    'bg-slate-300 text-slate-800',
    'bg-orange-300 text-orange-950',
];

export function TopCard({
    top5,
    hidden,
}: {
    top5: Top5Data | null;
    hidden: boolean;
}) {
    const [tab, setTab] = useState<TopKey>('products');
    const rows = top5 ? topRows(top5, tab) : [];
    const max = Math.max(1, ...rows.map((row) => row.amount));

    return (
        <HomeCard
            title="Terlaris"
            action={
                <Link
                    href={topConfig[tab].href}
                    className="flex items-center text-xs font-semibold text-primary"
                >
                    Lihat semua <ChevronRight className="size-3.5" />
                </Link>
            }
        >
            <div className="mb-4 grid grid-cols-4 gap-1 rounded-xl bg-muted p-1">
                {(Object.keys(topConfig) as TopKey[]).map((key) => (
                    <button
                        key={key}
                        type="button"
                        onClick={() => setTab(key)}
                        className={cn(
                            'h-8 truncate rounded-lg px-1 text-xs font-medium transition-colors',
                            tab === key
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground',
                        )}
                    >
                        {topConfig[key].label}
                    </button>
                ))}
            </div>

            {top5 === null ? (
                <div className="space-y-3">
                    {Array.from({ length: 5 }, (_, index) => (
                        <Skeleton key={index} className="h-12 rounded-2xl" />
                    ))}
                </div>
            ) : rows.length === 0 ? (
                <p className="py-8 text-center text-sm text-muted-foreground">
                    Belum ada penjualan di periode ini
                </p>
            ) : (
                <ol className="space-y-3.5">
                    {rows.map((row, index) => (
                        <li key={`${row.name}-${index}`} className="flex gap-3">
                            <span
                                className={cn(
                                    'flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                                    rankTones[index] ??
                                        'bg-muted text-muted-foreground',
                                )}
                            >
                                {index + 1}
                            </span>
                            <div className="min-w-0 flex-1">
                                <div className="flex items-baseline justify-between gap-2">
                                    <p className="truncate text-sm font-medium">
                                        {row.name || '-'}
                                    </p>
                                    <p className="shrink-0 text-sm font-semibold tabular-nums">
                                        {hidden
                                            ? MASKED
                                            : toRupiahCompact(row.amount)}
                                    </p>
                                </div>
                                <p className="text-[11px] text-muted-foreground">
                                    {row.caption}
                                </p>
                                <div className="mt-1.5 h-1.5 overflow-hidden rounded-full bg-muted">
                                    <div
                                        className="h-full rounded-full bg-primary/70 transition-[width] duration-700"
                                        style={{
                                            width: `${(row.amount / max) * 100}%`,
                                        }}
                                    />
                                </div>
                            </div>
                        </li>
                    ))}
                </ol>
            )}
        </HomeCard>
    );
}
