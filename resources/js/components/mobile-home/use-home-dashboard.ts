import {
    addDays,
    differenceInCalendarDays,
    endOfMonth,
    format,
    isWithinInterval,
    startOfDay,
    startOfMonth,
    subDays,
    subMonths,
} from 'date-fns';
import QueryString from 'qs';
import { useCallback, useEffect, useRef, useState } from 'react';
import type { ProfitPotential, Top5Data } from '@/lib/model';
import type { Option } from '@/types';

export type PeriodKey =
    | 'today'
    | 'yesterday'
    | '7d'
    | 'month'
    | 'last-month'
    | 'custom';

export type DateRangeValue = { from: Date; to: Date };

export type HomeFilters = {
    period: PeriodKey;
    range: DateRangeValue;
    selectAll: boolean;
    locs: number[];
};

export type SalesSummary = {
    net_sales_after_tax: number;
    net_profit: number;
    transaction_count: number;
};

export type TrendPoint = { date: string; sales: number; profit: number };

export const periodLabels: Record<PeriodKey, string> = {
    today: 'Hari ini',
    yesterday: 'Kemarin',
    '7d': '7 hari',
    month: 'Bulan ini',
    'last-month': 'Bulan lalu',
    custom: 'Pilih tanggal',
};

/** Label pembanding untuk persentase kenaikan/penurunan. */
export const comparisonLabels: Record<PeriodKey, string> = {
    today: 'dari kemarin',
    yesterday: 'dari hari sebelumnya',
    '7d': 'dari 7 hari sebelumnya',
    month: 'dari periode sama bulan lalu',
    'last-month': 'dari bulan sebelumnya',
    custom: 'dari periode sebelumnya',
};

export function rangeFor(period: PeriodKey, now = new Date()): DateRangeValue {
    const today = startOfDay(now);

    switch (period) {
        case 'yesterday':
            return { from: subDays(today, 1), to: subDays(today, 1) };
        case '7d':
            return { from: subDays(today, 6), to: today };
        case 'month':
            return { from: startOfMonth(today), to: today };
        case 'last-month': {
            const lastMonth = subMonths(today, 1);

            return { from: startOfMonth(lastMonth), to: endOfMonth(lastMonth) };
        }
        default:
            return { from: today, to: today };
    }
}

/** Periode sebelumnya dengan panjang yang sama, untuk perbandingan. */
function previousRange(filters: HomeFilters): DateRangeValue {
    const { from, to } = filters.range;

    if (filters.period === 'month') {
        const prevFrom = startOfMonth(subMonths(from, 1));
        const prevTo = addDays(prevFrom, differenceInCalendarDays(to, from));

        return {
            from: prevFrom,
            to: prevTo > endOfMonth(prevFrom) ? endOfMonth(prevFrom) : prevTo,
        };
    }

    if (filters.period === 'last-month') {
        const prevFrom = startOfMonth(subMonths(from, 1));

        return { from: prevFrom, to: endOfMonth(prevFrom) };
    }

    const days = differenceInCalendarDays(to, from) + 1;

    return { from: subDays(from, days), to: subDays(to, days) };
}

/** Grafik tren minimal 7 hari agar tetap informatif untuk filter satu hari. */
function trendRange({ from, to }: DateRangeValue): DateRangeValue {
    return differenceInCalendarDays(to, from) < 6
        ? { from: subDays(to, 6), to }
        : { from, to };
}

const STORAGE_KEY = 'mobile-home:filters';

function loadFilters(): HomeFilters {
    try {
        const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '{}');
        const period: PeriodKey =
            saved.period && saved.period !== 'custom' ? saved.period : 'today';

        return {
            period,
            range: rangeFor(period),
            selectAll: saved.selectAll ?? true,
            locs: Array.isArray(saved.locs) ? saved.locs.map(Number) : [],
        };
    } catch {
        return {
            period: 'today',
            range: rangeFor('today'),
            selectAll: true,
            locs: [],
        };
    }
}

const ymd = (date: Date) => format(date, 'yyyy-MM-dd');

function buildQuery(filters: HomeFilters, range = filters.range): string {
    return QueryString.stringify(
        {
            start_at: ymd(range.from),
            end_at: ymd(range.to),
            select_all_location: filters.selectAll ? 1 : 0,
            locs: filters.selectAll ? [] : filters.locs,
        },
        { arrayFormat: 'indices', encodeValuesOnly: true },
    );
}

async function fetchJson<T>(url: string, query: string): Promise<T> {
    const res = await fetch(`${url}?${query}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
    });

    if (res.status === 401 || res.status === 419) {
        window.location.href = '/login';

        throw new Error('Session expired');
    }

    if (!res.ok) {
        throw new Error(`Failed ${url}`);
    }

    return res.json();
}

const toSummary = (data: Partial<SalesSummary> | null): SalesSummary => ({
    net_sales_after_tax: Number(data?.net_sales_after_tax ?? 0),
    net_profit: Number(data?.net_profit ?? 0),
    transaction_count: Number(data?.transaction_count ?? 0),
});

function fillTrend(
    rows: {
        local_sales_date: string;
        net_sales_after_tax: number;
        net_profit: number;
    }[],
    range: DateRangeValue,
): TrendPoint[] {
    const byDate = new Map(rows.map((row) => [row.local_sales_date, row]));
    const days = differenceInCalendarDays(range.to, range.from) + 1;

    return Array.from({ length: days }, (_, index) => {
        const date = ymd(addDays(range.from, index));
        const row = byDate.get(date);

        return {
            date,
            sales: Number(row?.net_sales_after_tax ?? 0),
            profit: Number(row?.net_profit ?? 0),
        };
    });
}

const POLL_INTERVAL = 30_000;

export function useHomeDashboard() {
    const [filters, setFilters] = useState<HomeFilters>(loadFilters);
    const [locationOptions, setLocationOptions] = useState<Option[]>([]);

    const [summary, setSummary] = useState<SalesSummary | null>(null);
    const [previous, setPrevious] = useState<SalesSummary | null>(null);
    const [refund, setRefund] = useState<number | null>(null);
    const [potential, setPotential] = useState<ProfitPotential | null>(null);
    const [trend, setTrend] = useState<TrendPoint[] | null>(null);
    const [top5, setTop5] = useState<Top5Data | null>(null);
    const [error, setError] = useState(false);
    const [updatedAt, setUpdatedAt] = useState<Date | null>(null);

    // Abaikan respons lama bila filter berganti saat request masih berjalan.
    const requestId = useRef(0);

    const load = useCallback(
        async (
            current: HomeFilters,
            {
                silent = false,
                stock = !silent,
            }: { silent?: boolean; stock?: boolean } = {},
        ) => {
            const id = ++requestId.current;
            const query = buildQuery(current);
            const isCurrent = () => id === requestId.current;

            if (!silent) {
                setSummary(null);
                setPrevious(null);
                setRefund(null);
                setTrend(null);
                setTop5(null);
                setPotential(null);
            }

            setError(false);

            const trendDates = trendRange(current.range);

            const tasks = [
                fetchJson<Partial<SalesSummary>>(
                    '/dashboard/sales-summary',
                    query,
                ).then((data) => isCurrent() && setSummary(toSummary(data))),
                fetchJson<Partial<SalesSummary>>(
                    '/dashboard/sales-summary',
                    buildQuery(current, previousRange(current)),
                ).then((data) => isCurrent() && setPrevious(toSummary(data))),
                fetchJson<{ net_sales_after_tax: number } | null>(
                    '/dashboard/sales-refund-summary',
                    query,
                ).then(
                    (data) =>
                        isCurrent() &&
                        setRefund(Number(data?.net_sales_after_tax ?? 0)),
                ),
                fetchJson<
                    {
                        local_sales_date: string;
                        net_sales_after_tax: number;
                        net_profit: number;
                    }[]
                >(
                    '/dashboard/sales-by-date',
                    buildQuery(current, trendDates),
                ).then(
                    (rows) =>
                        isCurrent() && setTrend(fillTrend(rows, trendDates)),
                ),
                fetchJson<Top5Data>('/dashboard/top5', query).then(
                    (data) => isCurrent() && setTop5(data),
                ),
            ];

            // Nilai stok tidak bergantung periode & relatif berat; tidak ikut polling.
            if (stock) {
                tasks.push(
                    fetchJson<ProfitPotential>(
                        '/dashboard/profit-potential',
                        query,
                    ).then((data) => isCurrent() && setPotential(data)),
                );
            }

            const results = await Promise.allSettled(tasks);

            if (!isCurrent()) {
                return;
            }

            if (results.some((result) => result.status === 'rejected')) {
                setError(true);
            }

            setUpdatedAt(new Date());
        },
        [],
    );

    useEffect(() => {
        fetchJson<Option[]>('/dashboard/location-options', '')
            .then(setLocationOptions)
            .catch(() => setLocationOptions([]));
    }, []);

    useEffect(() => {
        try {
            localStorage.setItem(
                STORAGE_KEY,
                JSON.stringify({
                    period: filters.period,
                    selectAll: filters.selectAll,
                    locs: filters.locs,
                }),
            );
        } catch {
            // localStorage tidak tersedia (mode privat); abaikan.
        }

        load(filters);
    }, [filters, load]);

    // Polling saat tab terlihat dan periode mencakup hari ini.
    useEffect(() => {
        const includesToday = isWithinInterval(startOfDay(new Date()), {
            start: filters.range.from,
            end: filters.range.to,
        });

        if (!includesToday) {
            return;
        }

        let interval: ReturnType<typeof setInterval> | null = null;

        const start = () => {
            interval ??= setInterval(
                () => load(filters, { silent: true }),
                POLL_INTERVAL,
            );
        };

        const stop = () => {
            if (interval) {
                clearInterval(interval);
                interval = null;
            }
        };

        const onVisibility = () => {
            if (document.visibilityState === 'visible') {
                load(filters, { silent: true });
                start();
            } else {
                stop();
            }
        };

        if (document.visibilityState === 'visible') {
            start();
        }

        document.addEventListener('visibilitychange', onVisibility);

        return () => {
            stop();
            document.removeEventListener('visibilitychange', onVisibility);
        };
    }, [filters, load]);

    const setPeriod = (period: PeriodKey, range?: DateRangeValue) =>
        setFilters((current) => ({
            ...current,
            period,
            range: range ?? rangeFor(period),
        }));

    const setLocations = (selectAll: boolean, locs: number[]) =>
        setFilters((current) => ({
            ...current,
            selectAll: selectAll || locs.length === 0,
            locs: selectAll ? [] : locs,
        }));

    const refresh = () => load(filters, { silent: true, stock: true });

    return {
        filters,
        setPeriod,
        setLocations,
        refresh,
        locationOptions,
        summary,
        previous,
        refund,
        potential,
        trend,
        top5,
        error,
        updatedAt,
    };
}
