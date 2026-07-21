import { useState, useEffect } from 'react';
import ProfitChart from './profit-chart';
import { Input } from '../ui/input';
import { Button } from '../ui/button';
import { Search } from 'lucide-react';

// 1. Tambahkan onStartYearChange dan onEndYearChange
type Props = {
    yearlyData: any;
    earlyYear: number;
    endYear: number;
    onStartYearChange?: (year: number) => void;
    onEndYearChange?: (year: number) => void;
};

export default function YearlyProfitChart({
    yearlyData,
    earlyYear,
    endYear,
    onStartYearChange,
    onEndYearChange,
}: Props) {
    const [startYear, setStartYear] = useState(String(earlyYear));
    const [finishYear, setFinishYear] = useState(String(endYear));

    // Sinkronisasi state lokal jika props berubah dari luar
    useEffect(() => {
        setStartYear(String(earlyYear));
        setFinishYear(String(endYear));
    }, [earlyYear, endYear]);

    const applyYearFilter = () => {
        // 2. Ganti router.get dengan memanggil props
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

            <ProfitChart data={yearlyData} />
        </section>
    );
}
