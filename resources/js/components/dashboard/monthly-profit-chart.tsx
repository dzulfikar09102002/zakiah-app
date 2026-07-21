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

// 1. Tambahkan onYearChange ke Props
type Props = {
    monthlyYear: number;
    years: number[];
    monthlyData: any;
    onYearChange?: (year: number) => void;
};

export default ({ monthlyYear, years, monthlyData, onYearChange }: Props) => {
    const [search, setSearch] = useState('');
    const filteredYears = years.filter((y) => y.toString().includes(search));

    const handleChangeYear = (year: string) => {
        // 2. Ganti router.get dengan memanggil props onYearChange
        if (onYearChange) {
            onYearChange(Number(year));
        }
    };

    return (
        <>
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
                            {/* SEARCH INPUT */}
                            <Input
                                className="mb-2 w-full rounded border px-2 py-1 text-sm outline-none"
                                placeholder="Cari tahun..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onClick={(e) => e.stopPropagation()}
                            />

                            {/* LIST */}
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
                <ProfitChart data={monthlyData} />
            </section>
        </>
    );
};
