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
import { router } from '@inertiajs/react';
import { dashboard } from '@/routes';

type Props = {
    monthlyYear: number;
    years: number[];
    monthlyData: any;
};
export default ({ monthlyYear, years, monthlyData }: Props) => {
    const [search, setSearch] = useState('');
    const filteredYears = years.filter((y) => y.toString().includes(search));
    const handleChangeYear = (year: string) => {
        router.get(
            dashboard().url,
            {
                monthly_year: year,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
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
                                    <SelectItem key={y} value={String(y)}>
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
