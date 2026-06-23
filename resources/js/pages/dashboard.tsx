import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, Option } from '@/types';
import RangeDatePicker from '@/components/date-range-picker';
import { useState } from 'react';
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

const dailyData = [
    { name: '2026-02-15', sales: 12500000, profit: 3200000 },
    { name: '2026-02-16', sales: 15800000, profit: 4100000 },
    { name: '2026-02-17', sales: 14200000, profit: 3800000 },
    { name: '2026-02-18', sales: 19500000, profit: 5200000 },
    { name: '2026-02-19', sales: 17100000, profit: 4600000 },
    { name: '2026-02-20', sales: 21418500, profit: 5777660 },
    { name: '2026-02-21', sales: 18900000, profit: 4900000 },
];

const monthlyData = [
    { name: 'Jan', sales: 450000000, profit: 120000000 },
    { name: 'Feb', sales: 520000000, profit: 145000000 },
    { name: 'Mar', sales: 480000000, profit: 130000000 },
    { name: 'Apr', sales: 610000000, profit: 170000000 },
    { name: 'Mei', sales: 550000000, profit: 155000000 },
    { name: 'Jun', sales: 590000000, profit: 165000000 },
    { name: 'Jul', sales: 630000000, profit: 180000000 },
    { name: 'Agu', sales: 580000000, profit: 160000000 },
    { name: 'Sep', sales: 650000000, profit: 190000000 },
    { name: 'Okt', sales: 710000000, profit: 210000000 },
    { name: 'Nov', sales: 680000000, profit: 200000000 },
    { name: 'Des', sales: 850000000, profit: 250000000 },
];

const yearlyData = [
    { name: '2022', sales: 4800000000, profit: 1200000000 },
    { name: '2023', sales: 5600000000, profit: 1450000000 },
    { name: '2024', sales: 6200000000, profit: 1700000000 },
    { name: '2025', sales: 7500000000, profit: 2100000000 },
    { name: '2026', sales: 8200000000, profit: 2400000000 },
];

const currentYear = new Date().getFullYear();

const years = Array.from(
    { length: currentYear - 2023 + 1 },
    (_, i) => 2023 + i,
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
};
function DashboardContent({
    locationOptions,
    profitPotential,
    salesRefundSummary,
    salesSummary,
    top5,
}: Props) {
    const params = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    });

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

            {/* Charts */}
            <ProfitChart data={dailyData} />

            <div className="grid-cols-2 gap-6 lg:grid">
                <MonthlyProfitChart
                    monthlyData={monthlyData}
                    years={years}
                    monthlyYear={2026}
                />
                <YearlyProfitChart
                    yearlyData={yearlyData}
                    earlyYear={2022}
                    endYear={2026}
                />
            </div>
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
            />
        </AppLayout>
    );
}
