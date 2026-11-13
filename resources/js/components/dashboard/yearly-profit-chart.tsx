import { Search } from 'lucide-react';
import { useState } from 'react';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Skeleton } from '../ui/skeleton';
import ProfitChart from './profit-chart';
import type { ProfitChartRow } from './profit-chart';

type Props = {
    yearlyData: ProfitChartRow[];
    earlyYear: number;
    endYear: number;
    onStartYearChange?: (year: number) => void;
    onEndYearChange?: (year: number) => void;
    isLoading?: boolean;
};

export default function YearlyProfitChart({
    yearlyData,
    earlyYear,
    endYear,
    onStartYearChange,
    onEndYearChange,
    isLoading,
}: Props) {
    const [startYear, setStartYear] = useState(String(earlyYear));
    const [finishYear, setFinishYear] = useState(String(endYear));

    // Ikuti rentang tahun dari induk saat berubah (disesuaikan saat render, bukan lewat effect).
    const [syncedYears, setSyncedYears] = useState({ earlyYear, endYear });

    if (syncedYears.earlyYear !== earlyYear || syncedYears.endYear !== endYear) {
        setSyncedYears({ earlyYear, endYear });
        setStartYear(String(earlyYear));
        setFinishYear(String(endYear));
    }

    const applyYearFilter = () => {
        if (onStartYearChange) onStartYearChange(Number(startYear));
        if (onEndYearChange) onEndYearChange(Number(finishYear));
    };

    const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter') {
            applyYearFilter();
        }
    };

    return (
        <section className="mt-4 space-y-4">
            <div className="items-center justify-between lg:flex">
                <h2 className="text-lg font-semibold">Performa Tahunan</h2>

                <div className="flex items-center gap-2">
                    <Input
                        type="number"
                        value={startYear}
                        onChange={(e) => setStartYear(e.target.value)}
                        onKeyDown={handleKeyDown}
                    />

                    <span>-</span>

                    <Input
                        type="number"
                        value={finishYear}
                        onChange={(e) => setFinishYear(e.target.value)}
                        onKeyDown={handleKeyDown}
                    />

                    <Button onClick={applyYearFilter}>
                        <Search className="h-4 w-4" />
                        Cari
                    </Button>
                </div>
            </div>

            {isLoading ? (
                <Skeleton className="h-[450px] w-full rounded-xl" />
            ) : (
                <ProfitChart data={yearlyData} />
            )}
        </section>
    );
}
