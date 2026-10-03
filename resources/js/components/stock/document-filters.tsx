import { Form, Link } from '@inertiajs/react';
import { subDays } from 'date-fns';
import { Search } from 'lucide-react';
import QueryString from 'qs';
import { useState } from 'react';
import DateRangePicker from '@/components/date-range-picker';
import LocationDropdown from '@/components/location-dropdown';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { Option } from '@/types';

type Props = {
    /** URL halaman daftar (tanpa query). */
    indexUrl: string;
    locationOptions: Option[];
    statusTabs: { value: string; label: string }[];
};

const toNumberArray = (value: unknown): number[] => {
    if (!value) return [];

    return (Array.isArray(value) ? value : String(value).split(',')).map(
        Number,
    );
};

/**
 * Filter daftar dokumen stok: kode, rentang tanggal pengajuan (default 30 hari), lokasi, dan status.
 */
export default function DocumentFilters({
    indexUrl,
    locationOptions,
    statusTabs,
}: Props) {
    const params = QueryString.parse(window.location.search, {
        ignoreQueryPrefix: true,
    });
    const status = typeof params.status === 'string' ? params.status : 'all';

    const initialSelectAll = params.select_all_location !== '0';
    const initialLocs = toNumberArray(params.locs);
    const initialExcludeLocs = toNumberArray(params.exclude_locs);

    const [selectAll, setSelectAll] = useState(initialSelectAll);
    const [locs, setLocs] = useState<number[]>(initialLocs);
    const [excludeLocs, setExcludeLocs] =
        useState<number[]>(initialExcludeLocs);

    const statusUrl = (value: string) => {
        const query: Record<string, unknown> = { ...params };
        delete query.page;
        delete query.status;

        if (value !== 'all') {
            query.status = value;
        }

        const search = QueryString.stringify(query, {
            arrayFormat: 'brackets',
            skipNulls: true,
        });

        return search ? `${indexUrl}?${search}` : indexUrl;
    };

    return (
        <div className="space-y-4">
            <Form method="GET" className="grid gap-2 lg:flex">
                <input type="hidden" name="page" value={1} />
                {status !== 'all' && (
                    <input type="hidden" name="status" value={status} />
                )}
                <Input
                    name="search"
                    placeholder="Cari kode..."
                    defaultValue={
                        typeof params.search === 'string' ? params.search : ''
                    }
                />
                <DateRangePicker defaultStartDate={subDays(new Date(), 30)} />
                <LocationDropdown
                    multiSelect
                    options={locationOptions.map((option) => ({
                        id: Number(option.value),
                        name: option.label,
                    }))}
                    defaultSelectAll={initialSelectAll}
                    defaultIds={initialLocs}
                    defaultExcludeIds={initialExcludeLocs}
                    handleSelectAllChange={setSelectAll}
                    handleIdsChange={setLocs}
                    handleExcludeIdsChange={setExcludeLocs}
                />
                <input
                    type="hidden"
                    name="select_all_location"
                    value={selectAll ? '1' : '0'}
                />
                {locs.map((id) => (
                    <input
                        key={`loc-${id}`}
                        type="hidden"
                        name="locs[]"
                        value={id}
                    />
                ))}
                {excludeLocs.map((id) => (
                    <input
                        key={`exclude-${id}`}
                        type="hidden"
                        name="exclude_locs[]"
                        value={id}
                    />
                ))}
                <Button variant="secondary">
                    <Search /> Cari
                </Button>
            </Form>

            <Tabs value={status}>
                <TabsList>
                    {statusTabs.map((tab) => (
                        <TabsTrigger key={tab.value} value={tab.value} asChild>
                            <Link href={statusUrl(tab.value)}>{tab.label}</Link>
                        </TabsTrigger>
                    ))}
                </TabsList>
            </Tabs>
        </div>
    );
}
