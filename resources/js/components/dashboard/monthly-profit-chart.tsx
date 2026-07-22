import { useState } from 'react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '../ui/select';
import ProfitChart from './profit-chart';
import { Input } from '@base-ui/react';
import { Skeleton } from '../ui/skeleton';

type Props = {
    monthlyYear: number;
    years: number[];
    monthlyData: any;
    onYearChange?: (year: number) => void;
    isLoading?: boolean;
};

export default ({
    monthlyYear,
    years,
    monthlyData,
    onYearChange,
    isLoading,
}: Props) => {
    const [search, setSearch] = useState('');
    const filteredYears = years.filter((y) => y.toString().includes(search));

    const handleChangeYear = (year: string) => {
        if (onYearChange) {
            onYearChange(Number(year));
        }
    };

    return (
        <section className="mt-4 space-y-4">
            <div className="flex items-center justify-between">
                <h2 className="text-lg font-semibold">Performa Bulanan</h2>
                <Select
                    defaultValue={String(monthlyYear)}
                    onValueChange={handleChangeYear}
                >
                    <SelectTrigger className="w-32">
                        <SelectValue />
                    </SelectTrigger>

                    <SelectContent className="p-2">
                        <Input
                            className="mb-2 w-full rounded border px-2 py-1 text-sm outline-none"
                            placeholder="Cari tahun..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onClick={(e) => e.stopPropagation()}
                        />

                        {filteredYears.length > 0 ? (
                            filteredYears.slice(0, 10).map((y) => (
                                <SelectItem
                                    key={y}
                                    value={String(y)}
                                    className="cursor-pointer"
                                >
                                    {y}
                                </SelectItem>
                            ))
                        ) : (
                            <div className="px-2 py-2 text-sm text-muted-foreground">
                                Tidak ditemukan
                            </div>
                        )}
                    </SelectContent>
                </Select>
            </div>
            {isLoading ? (
                <Skeleton className="h-[450px] w-full rounded-xl" />
            ) : (
                <ProfitChart data={monthlyData} />
            )}
        </section>
    );
};
