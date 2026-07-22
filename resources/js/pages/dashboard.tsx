import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Option } from '@/types';
import RangeDatePicker from '@/components/date-range-picker';
import { useEffect, useRef, useState } from 'react';
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
import { Skeleton } from '@/components/ui/skeleton';

const currentYear = new Date().getFullYear();

const years = Array.from(
    { length: currentYear - 2023 + 1 },
    (_, i) => currentYear - i,
);
type MonthlySales = {
    year: number;
    months: string[];
    net_sales_after_tax: number[];
    net_profit: number[];
};

type YearlySales = {
    years: string[];
    net_sales_after_tax: number[];
    net_profit: number[];
};
type Props = {
    locationOptions?: Option[];

    profitPotential?: ProfitPotential;

    salesRefundSummary?: {
        net_sales_after_tax: number;
    };

    salesSummary?: {
        net_sales_after_tax: number;
        net_profit: number;
    };

    top5?: Top5Data;

    salesByDate?: {
        local_sales_date: string;
        net_sales_after_tax: number;
        net_profit: number;
    }[];

    monthlySales?: {
        year: number;
        months: string[];
        net_sales_after_tax: number[];
        net_profit: number[];
    };

    yearlySales?: {
        years: string[];
        net_sales_after_tax: number[];
        net_profit: number[];
    };
};
function DashboardContent({ locationOptions }: Props) {
    const [profitPotential, setProfitPotential] = useState<ProfitPotential>();

    const [salesRefundSummary, setSalesRefundSummary] = useState<any>();

    const [salesSummary, setSalesSummary] = useState<any>();

    const [top5, setTop5] = useState<Top5Data>();

    const [salesByDate, setSalesByDate] = useState<any[]>([]);

    const [monthlySales, setMonthlySales] = useState<MonthlySales | null>(null);

    const [yearlySales, setYearlySales] = useState<YearlySales | null>(null);

    const [summaryLoading, setSummaryLoading] = useState(true);
    const [refundLoading, setRefundLoading] = useState(true);
    const [potentialLoading, setPotentialLoading] = useState(true);

    const [chartLoading, setChartLoading] = useState(true);
    const [top5Loading, setTop5Loading] = useState(true);
    const [monthlyLoading, setMonthlyLoading] = useState(true);
    const [yearlyLoading, setYearlyLoading] = useState(true);
    const [localLocationOptions, setLocalLocationOptions] = useState<Option[]>(
        [],
    );

    const params = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    });
    const [monthlyYear, setMonthlyYear] = useState(
        Number(params.monthly_year ?? currentYear),
    );

    const [startYear, setStartYear] = useState(
        Number(params.start_year ?? currentYear - 1),
    );

    const [endYear, setEndYear] = useState(
        Number(params.end_year ?? currentYear),
    );
    const dailyData =
        salesByDate?.map((item) => ({
            name: item.local_sales_date,
            sales: Number(item.net_sales_after_tax ?? 0),
            profit: Number(item.net_profit ?? 0),
        })) ?? [];

    const months: string[] = monthlySales?.months ?? [];

    const salesArr: number[] = monthlySales?.net_sales_after_tax ?? [];

    const profitArr: number[] = monthlySales?.net_profit ?? [];

    const monthlyChartData = months.map((m, i) => ({
        name: m,
        year: monthlySales?.year ?? new Date().getFullYear(),
        sales: Number(salesArr[i] ?? 0),
        profit: Number(profitArr[i] ?? 0),
    }));

    const yearsData = yearlySales?.years ?? [];

    const earlyYear = Number(yearsData[0] ?? new Date().getFullYear() - 1);

    const yearlyChartData = yearsData.map((y, i) => ({
        name: String(y),
        sales: Number(yearlySales?.net_sales_after_tax?.[i] ?? 0),
        profit: Number(yearlySales?.net_profit?.[i] ?? 0),
    }));

    const parseToNumberArray = (val: any): number[] => {
        if (!val) return [];
        if (Array.isArray(val)) {
            return val.map(Number);
        }
        if (typeof val === 'object') {
            return Object.values(val).map(Number);
        }
        return String(val).split(',').map(Number);
    };

    const [dateRange, setDateRange] = useState<DateRange | undefined>(() => {
        if (params.start_at && params.end_at) {
            return {
                from: new Date(params.start_at as string),
                to: new Date(params.end_at as string),
            };
        }
        return undefined;
    });

    const initialSelectAll = params.select_all_location !== '0';

    const initialLocs = parseToNumberArray(params.locs);

    const initialExcludeLocs = parseToNumberArray(params.exclude_locs);

    const [selectAllLocation, setSelectAllLocation] =
        useState<boolean>(initialSelectAll);

    const [locs, setLocs] = useState<number[]>(initialLocs);

    const [excludeLocs, setExcludeLocs] =
        useState<number[]>(initialExcludeLocs);

    const prevMonthlyYear = useRef(monthlyYear);
    const prevStartYear = useRef(startYear);
    const prevEndYear = useRef(endYear);

    const dailyOverviewData = {
        total_sale: Number(salesSummary?.net_sales_after_tax ?? 0),

        total_profit: Number(salesSummary?.net_profit ?? 0),

        total_return: Number(salesRefundSummary?.net_sales_after_tax ?? 0),

        total_stock: Number(profitPotential?.stock ?? 0),

        total_hpp: Number(profitPotential?.cogs ?? 0),

        total_stock_price: Number(profitPotential?.sell_price ?? 0),

        potential_profit: Number(
            (profitPotential?.sell_price ?? 0) - (profitPotential?.cogs ?? 0),
        ),
    };

    const handleApplyFilter = async () => {
        window.history.replaceState({}, '', `/dashboard?${buildQuery()}`);
        loadSalesSummary();
        loadRefundSummary();
        loadProfitPotential();
        loadChart();
        loadTop5();
        loadMonthly();
        loadYearly();
    };
    const shouldPolling = () => {
        if (!dateRange?.from || !dateRange?.to) {
            return true;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const from = new Date(dateRange.from);
        from.setHours(0, 0, 0, 0);

        const to = new Date(dateRange.to);
        to.setHours(0, 0, 0, 0);

        return today >= from && today <= to;
    };

    const buildQuery = () =>
        QueryString.stringify(
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

                monthly_year: monthlyYear,
                start_year: startYear,
                end_year: endYear,
            },
            {
                arrayFormat: 'indices',
                skipNulls: true,
                encodeValuesOnly: true,
            },
        );
    const fetchJson = async <T,>(url: string): Promise<T> => {
        const query = buildQuery();

        const res = await fetch(query ? `${url}?${query}` : url);

        if (!res.ok) {
            throw new Error(`Failed ${url}`);
        }

        return res.json();
    };
    const loadLocations = async () => {
        try {
            const data = await fetchJson<Option[]>(
                '/dashboard/location-options',
            );
            setLocalLocationOptions(data);
        } catch (error) {
            console.error('Gagal meload lokasi', error);
        }
    };

    const loadSalesSummary = async () => {
        setSummaryLoading(true);
        try {
            const data = await fetchJson<any>('/dashboard/sales-summary');
            setSalesSummary(data);
        } finally {
            setSummaryLoading(false);
        }
    };

    const loadRefundSummary = async () => {
        setRefundLoading(true);
        try {
            const data = await fetchJson<any>(
                '/dashboard/sales-refund-summary',
            );
            setSalesRefundSummary(data);
        } finally {
            setRefundLoading(false);
        }
    };

    const loadProfitPotential = async () => {
        setPotentialLoading(true);
        try {
            const data = await fetchJson<ProfitPotential>(
                '/dashboard/profit-potential',
            );
            setProfitPotential(data);
        } finally {
            setPotentialLoading(false);
        }
    };

    const loadChart = async () => {
        setChartLoading(true);
        try {
            const data = await fetchJson<any[]>('/dashboard/sales-by-date');
            setSalesByDate(data);
        } finally {
            setChartLoading(false);
        }
    };

    const loadTop5 = async () => {
        setTop5Loading(true);
        try {
            const data = await fetchJson<Top5Data>('/dashboard/top5');
            setTop5(data);
        } finally {
            setTop5Loading(false);
        }
    };
    const loadMonthly = async () => {
        setMonthlyLoading(true);

        try {
            const data = await fetchJson<MonthlySales>(
                '/dashboard/monthly-sales',
            );

            setMonthlySales(data);
        } finally {
            setMonthlyLoading(false);
        }
    };
    const loadYearly = async () => {
        setYearlyLoading(true);

        try {
            const data = await fetchJson<YearlySales>(
                '/dashboard/yearly-sales',
            );

            setYearlySales(data);
        } finally {
            setYearlyLoading(false);
        }
    };
    useEffect(() => {
        loadLocations();
        loadSalesSummary();
        loadRefundSummary();
        loadProfitPotential();
        loadChart();
        loadTop5();
        loadMonthly();
        loadYearly();
    }, []);

    useEffect(() => {
        if (prevMonthlyYear.current === monthlyYear) return;
        prevMonthlyYear.current = monthlyYear;

        loadMonthly();
        window.history.replaceState({}, '', `/dashboard?${buildQuery()}`);
    }, [monthlyYear]);

    useEffect(() => {
        if (
            prevStartYear.current === startYear &&
            prevEndYear.current === endYear
        )
            return;

        prevStartYear.current = startYear;
        prevEndYear.current = endYear;

        loadYearly();
        window.history.replaceState({}, '', `/dashboard?${buildQuery()}`);
    }, [startYear, endYear]);

    useEffect(() => {
        let interval: ReturnType<typeof setInterval> | null = null;

        const reload = async () => {
            try {
                const [dashboardData, monthlyData, yearlyData] =
                    await Promise.all([
                        fetchJson<any>('/dashboard/sales-summary'),
                        fetchJson<MonthlySales>('/dashboard/monthly-sales'),
                        fetchJson<YearlySales>('/dashboard/yearly-sales'),
                    ]);

                setSalesSummary(dashboardData);
                setMonthlySales(monthlyData);
                setYearlySales(yearlyData);
            } catch (error) {
                console.error('Gagal polling data', error);
            }
        };

        const startPolling = () => {
            if (interval) return;
            interval = setInterval(reload, 10000);
        };

        const stopPolling = () => {
            if (!interval) return;
            clearInterval(interval);
            interval = null;
        };

        const handleVisibilityChange = () => {
            if (document.visibilityState === 'visible' && shouldPolling()) {
                reload();
                startPolling();
            } else {
                stopPolling();
            }
        };

        if (document.visibilityState === 'visible' && shouldPolling()) {
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
    }, [
        dateRange,
        monthlyYear,
        startYear,
        endYear,
        selectAllLocation,
        locs,
        excludeLocs,
    ]);
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
                                options={localLocationOptions.map((l) => ({
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

            <DaillyOverview
                {...dailyOverviewData}
                summaryLoading={summaryLoading}
                refundLoading={refundLoading}
                potentialLoading={potentialLoading}
            />

            <Separator />

            <ProfitChart data={dailyData} isLoading={chartLoading} />

            <Separator />

            <div className="grid-cols-2 gap-6 lg:grid">
                <MonthlyProfitChart
                    monthlyData={monthlyChartData}
                    years={years}
                    monthlyYear={monthlyYear}
                    onYearChange={(year) => setMonthlyYear(year)}
                    isLoading={monthlyLoading}
                />

                <YearlyProfitChart
                    yearlyData={yearlyChartData}
                    earlyYear={startYear}
                    endYear={endYear}
                    onStartYearChange={(year) => setStartYear(year)}
                    onEndYearChange={(year) => setEndYear(year)}
                    isLoading={yearlyLoading}
                />
            </div>
            <Separator />
            {top5Loading ? (
                <Skeleton className="h-[300px] w-full rounded-xl" />
            ) : (
                <TopFive top5={top5!} />
            )}
        </div>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard({ locationOptions }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <DashboardContent locationOptions={locationOptions} />
        </AppLayout>
    );
}
