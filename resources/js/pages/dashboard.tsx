import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, Option } from '@/types';
import RangeDatePicker from '@/components/date-range-picker';
import { useEffect, useState } from 'react';
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import TopFive from '@/components/dashboard/top-five';
import DaillyOverview from '@/components/dashboard/dailly-overview';
import ProfitChart from '@/components/dashboard/profit-chart';
import MonthlyProfitChart from '@/components/dashboard/monthly-profit-chart';
import YearlyProfitChart from '@/components/dashboard/yearly-profit-chart';
import { Filter, MapPin, Search } from 'lucide-react';
import QueryString from 'qs';
import LocationDropdown from '@/components/location-dropdown';
import { ProfitPotential, Top5Data } from '@/lib/model';
import { DateRange } from 'node_modules/react-day-picker/dist/esm/types/shared';
import { format } from 'date-fns';

const currentYear = new Date().getFullYear();

const years = Array.from(
    { length: currentYear - 2023 + 1 },
    (_, i) => currentYear - i,
);
type Props = {
    locationOptions: Option[];
    profitPotential: ProfitPotential;
    salesRefundSummary: {
        net_sales_after_tax: number;
    };
    salesSummary: {
        net_sales_after_tax: number;
        net_profit: number;
    };
    top5: Top5Data;
    salesByDate: {
        local_sales_date: string;
        net_sales_after_tax: number;
        net_profit: number;
    }[];
    monthlySales: {
        year: number;
        months: string[];
        net_sales_after_tax: number[];
        net_profit: number[];
    };
    yearlySales: {
        years: string[];
        net_sales_after_tax: number[];
        net_profit: number[];
    };
};
function DashboardContent({
    locationOptions,
    profitPotential,
    salesRefundSummary,
    salesSummary,
    top5,
    salesByDate,
    monthlySales,
    yearlySales,
}: Props) {
    const params = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    });

    const dailyData =
        salesByDate?.map((item) => ({
            name: item.local_sales_date,
            sales: Number(item.net_sales_after_tax ?? 0),
            profit: Number(item.net_profit ?? 0),
        })) ?? [];
    const months = monthlySales?.months ?? [];
    const salesArr = monthlySales?.net_sales_after_tax ?? [];
    const profitArr = monthlySales?.net_profit ?? [];

    const monthlyChartData = months.map((m, i) => ({
        name: m,
        year: monthlySales.year,
        sales: Number(salesArr[i] ?? 0),
        profit: Number(profitArr[i] ?? 0),
    }));

    const yearsData = yearlySales.years ?? [];

    const earlyYear = Number(yearsData[0] ?? new Date().getFullYear() - 1);
    const endYear = Number(
        yearsData[yearsData.length - 1] ?? new Date().getFullYear(),
    );

    const yearlyChartData = yearsData.map((y, i) => ({
        name: String(y),
        sales: Number(yearlySales.net_sales_after_tax?.[i] ?? 0),
        profit: Number(yearlySales.net_profit?.[i] ?? 0),
    }));
    const handleApplyFilter = () => {
        router.get(
            dashboard().url,
            {
                start_at: dateRange?.from
                    ? format(dateRange.from, 'yyyy-MM-dd')
                    : undefined,

                end_at: dateRange?.to
                    ? format(dateRange.to, 'yyyy-MM-dd')
                    : undefined,

                select_all_location: selectAllLocation ? 1 : 0,
                locs,
                exclude_locs: excludeLocs,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };
    const parseToNumberArray = (val: any): number[] => {
        if (!val) return [];

        if (Array.isArray(val)) {
            return val.map(Number);
        }

        return String(val).split(',').map(Number);
    };
    const [dateRange, setDateRange] = useState<DateRange | undefined>();
    const initialSelectAll = params.select_all_location !== '0';
    const initialLocs = parseToNumberArray(params.locs);
    const initialExcludeLocs = parseToNumberArray(params.exclude_locs);

    const [selectAllLocation, setSelectAllLocation] =
        useState<boolean>(initialSelectAll);

    const [locs, setLocs] = useState<number[]>(initialLocs);

    const [excludeLocs, setExcludeLocs] =
        useState<number[]>(initialExcludeLocs);
    const dailyOverviewData = {
        total_sale: Number(salesSummary?.net_sales_after_tax ?? 0),
        total_profit: Number(salesSummary?.net_profit ?? 0),
        total_return: Number(salesRefundSummary?.net_sales_after_tax ?? 0),
        total_stock: Number(profitPotential.stock ?? 0),
        total_hpp: Number(profitPotential.cogs ?? 0),
        total_stock_price: Number(profitPotential.sell_price ?? 0),
        potential_profit: Number(
            profitPotential.sell_price - profitPotential.cogs,
        ),
    };

    useEffect(() => {
        let interval: ReturnType<typeof setInterval> | null = null;

        const reload = () => {
            router.reload({
                async: true,
                only: [
                    'profitPotential',
                    'salesRefundSummary',
                    'salesSummary',
                    'top5',
                    'salesByDate',
                ],
            });
        };

        const startPolling = () => {
            if (interval) return;

            interval = setInterval(reload, 5000);
        };

        const stopPolling = () => {
            if (!interval) return;

            clearInterval(interval);
            interval = null;
        };

        const handleVisibilityChange = () => {
            if (document.visibilityState === 'visible') {
                reload();
                startPolling();
            } else {
                stopPolling();
            }
        };

        if (document.visibilityState === 'visible') {
            startPolling();
        }

        document.addEventListener('visibilitychange', handleVisibilityChange);

        return () => {
            stopPolling();

            document.removeEventListener(
                'visibilitychange',
                handleVisibilityChange,
            );
        };
    }, []);
    return (
        <div className="space-y-4">
            <input
                type="hidden"
                name="select_all_location"
                value={selectAllLocation ? '1' : '0'}
            />
            {locs.map((id, i) => (
                <input key={i} type="hidden" name="locs[]" value={id} />
            ))}

            {excludeLocs.map((id, i) => (
                <input key={i} type="hidden" name="exclude_locs[]" value={id} />
            ))}
            <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <div className="flex w-full flex-col gap-3 lg:w-auto lg:flex-row lg:items-center lg:gap-2">
                        <div className="w-full lg:w-auto">
                            <LocationDropdown
                                multiSelect
                                options={locationOptions.map((l) => ({
                                    id: Number(l.value),
                                    name: l.label,
                                }))}
                                defaultSelectAll={initialSelectAll}
                                defaultIds={initialLocs}
                                defaultExcludeIds={initialExcludeLocs}
                                handleSelectAllChange={setSelectAllLocation}
                                handleIdsChange={setLocs}
                                handleExcludeIdsChange={setExcludeLocs}
                            />
                        </div>

                        <div className="w-full lg:w-auto">
                            <RangeDatePicker
                                onValueChange={setDateRange}
                                defaultStartDate={new Date()}
                                defaultEndDate={new Date()}
                            />
                        </div>

                        <Button
                            onClick={handleApplyFilter}
                            className="w-full lg:w-auto"
                        >
                            <Search className="h-4 w-4" />
                            Cari
                        </Button>
                    </div>
                </div>
            </div>
            <Separator />
            <div className="items-center justify-between lg:flex">
                <h2 className="text-lg font-semibold">Performa Harian</h2>
            </div>
            <DaillyOverview {...dailyOverviewData} />
            <Separator />
            {/* Charts */}
            <ProfitChart data={dailyData} />
            <Separator />
            <div className="grid-cols-2 gap-6 lg:grid">
                <MonthlyProfitChart
                    monthlyData={monthlyChartData}
                    years={years}
                    monthlyYear={monthlyChartData[0]?.year ?? currentYear}
                />
                <YearlyProfitChart
                    yearlyData={yearlyChartData}
                    earlyYear={earlyYear}
                    endYear={endYear}
                />
            </div>
            <Separator />
            <TopFive top5={top5} />
        </div>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

export default function Dashboard({
    locationOptions,
    profitPotential,
    salesRefundSummary,
    salesSummary,
    top5,
    salesByDate,
    monthlySales,
    yearlySales,
}: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <DashboardContent
                locationOptions={locationOptions}
                profitPotential={profitPotential}
                salesRefundSummary={salesRefundSummary}
                salesSummary={salesSummary}
                top5={top5}
                salesByDate={salesByDate}
                yearlySales={yearlySales}
                monthlySales={monthlySales}
            />
        </AppLayout>
    );
}
