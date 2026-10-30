import { format, isSameDay } from 'date-fns';
import { id } from 'date-fns/locale';
import { CalendarDays, Check, Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { DateRange } from 'react-day-picker';
import { BottomSheet } from '@/components/mobile/bottom-sheet';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { cn } from '@/lib/utils';
import type { Option } from '@/types';
import {
    periodLabels,
    type DateRangeValue,
    type HomeFilters,
    type PeriodKey,
} from './use-home-dashboard';

const presetPeriods: PeriodKey[] = [
    'today',
    'yesterday',
    '7d',
    'month',
    'last-month',
];

export function formatRange({ from, to }: DateRangeValue): string {
    if (isSameDay(from, to)) {
        return format(from, 'EEEE, d MMM yyyy', { locale: id });
    }

    const sameYear = from.getFullYear() === to.getFullYear();

    return `${format(from, sameYear ? 'd MMM' : 'd MMM yyyy', { locale: id })} – ${format(to, 'd MMM yyyy', { locale: id })}`;
}

export function PeriodChips({
    filters,
    onChange,
}: {
    filters: HomeFilters;
    onChange: (period: PeriodKey, range?: DateRangeValue) => void;
}) {
    const [calendarOpen, setCalendarOpen] = useState(false);
    const [draft, setDraft] = useState<DateRange | undefined>();

    const tab = (active: boolean) =>
        cn(
            'flex h-9 shrink-0 items-center justify-center rounded-xl px-3 text-[13px] font-medium whitespace-nowrap transition-colors',
            active
                ? 'bg-primary text-primary-foreground shadow-sm shadow-primary/30'
                : 'text-muted-foreground active:bg-muted',
        );

    // Bar tab periode di dalam kartu: preset bisa digeser, tombol kalender
    // tetap di kanan. Rentang custom ditampilkan di kartu omzet.
    return (
        <>
            <div className="flex items-center gap-1.5 rounded-2xl border border-border/60 bg-card p-1.5 shadow-[0_1px_2px_rgb(0_0_0/0.03)]">
                <div className="flex min-w-0 flex-1 snap-x gap-1 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    {presetPeriods.map((period) => (
                        <button
                            key={period}
                            type="button"
                            className={cn(
                                tab(filters.period === period),
                                'snap-start',
                            )}
                            onClick={() => onChange(period)}
                        >
                            {periodLabels[period]}
                        </button>
                    ))}
                </div>

                <span className="h-6 w-px shrink-0 bg-border" aria-hidden />

                <button
                    type="button"
                    aria-label={periodLabels.custom}
                    className={cn(tab(filters.period === 'custom'), 'w-9 px-0')}
                    onClick={() => {
                        setDraft(filters.range);
                        setCalendarOpen(true);
                    }}
                >
                    <CalendarDays className="size-[18px]" />
                </button>
            </div>

            <BottomSheet
                open={calendarOpen}
                onOpenChange={setCalendarOpen}
                title="Pilih rentang tanggal"
                description="Ketuk tanggal awal lalu tanggal akhir."
                footer={
                    <Button
                        className="h-12 w-full rounded-2xl text-base"
                        disabled={!draft?.from}
                        onClick={() => {
                            if (!draft?.from) {
                                return;
                            }

                            onChange('custom', {
                                from: draft.from,
                                to: draft.to ?? draft.from,
                            });
                            setCalendarOpen(false);
                        }}
                    >
                        Terapkan
                    </Button>
                }
            >
                <div className="flex justify-center">
                    <Calendar
                        mode="range"
                        selected={draft}
                        onSelect={setDraft}
                        locale={id}
                        defaultMonth={draft?.from}
                        disabled={{ after: new Date() }}
                        className="w-full max-w-sm [--cell-size:--spacing(11)]"
                    />
                </div>
            </BottomSheet>
        </>
    );
}

export function LocationSheet({
    open,
    onOpenChange,
    options,
    filters,
    onApply,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    options: Option[];
    filters: HomeFilters;
    onApply: (selectAll: boolean, locs: number[]) => void;
}) {
    // Parent me-remount komponen ini (via `key`) setiap kali sheet dibuka,
    // sehingga pilihan awal selalu sama dengan filter aktif.
    const [keyword, setKeyword] = useState('');
    const [selected, setSelected] = useState<number[]>(() =>
        filters.selectAll ? [] : filters.locs,
    );

    const filtered = useMemo(
        () =>
            options.filter((option) =>
                option.label.toLowerCase().includes(keyword.toLowerCase()),
            ),
        [options, keyword],
    );

    const toggle = (value: number) =>
        setSelected((current) =>
            current.includes(value)
                ? current.filter((item) => item !== value)
                : [...current, value],
        );

    const row =
        'flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-left text-sm font-medium active:bg-muted';

    const checkbox = (checked: boolean) => (
        <span
            className={cn(
                'flex size-5 shrink-0 items-center justify-center rounded-md border-2 transition-colors',
                checked
                    ? 'border-primary bg-primary text-primary-foreground'
                    : 'border-muted-foreground/40',
            )}
        >
            {checked && <Check className="size-3.5" strokeWidth={3} />}
        </span>
    );

    return (
        <BottomSheet
            open={open}
            onOpenChange={onOpenChange}
            title="Pilih lokasi"
            description="Data beranda akan mengikuti lokasi yang dipilih."
            footer={
                <Button
                    className="h-12 w-full rounded-2xl text-base"
                    onClick={() => {
                        onApply(selected.length === 0, selected);
                        onOpenChange(false);
                    }}
                >
                    {selected.length === 0
                        ? 'Tampilkan semua lokasi'
                        : `Terapkan (${selected.length} lokasi)`}
                </Button>
            }
        >
            <div className="sticky top-0 z-10 bg-background pb-2">
                <label className="flex h-11 items-center gap-2 rounded-2xl bg-muted px-3">
                    <Search className="size-4 text-muted-foreground" />
                    <input
                        value={keyword}
                        onChange={(event) => setKeyword(event.target.value)}
                        placeholder="Cari toko..."
                        className="h-full flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                    />
                </label>
            </div>

            <button
                type="button"
                className={row}
                onClick={() => setSelected([])}
            >
                {checkbox(selected.length === 0)}
                Semua lokasi
            </button>

            {filtered.map((option) => {
                const value = Number(option.value);

                return (
                    <button
                        key={option.value}
                        type="button"
                        className={row}
                        onClick={() => toggle(value)}
                    >
                        {checkbox(selected.includes(value))}
                        <span className="truncate">{option.label}</span>
                    </button>
                );
            })}

            {filtered.length === 0 && (
                <p className="py-8 text-center text-sm text-muted-foreground">
                    Lokasi tidak ditemukan
                </p>
            )}
        </BottomSheet>
    );
}

export function locationLabel(filters: HomeFilters, options: Option[]): string {
    if (filters.selectAll || filters.locs.length === 0) {
        return 'Semua lokasi';
    }

    if (filters.locs.length === 1) {
        return (
            options.find((option) => Number(option.value) === filters.locs[0])
                ?.label ?? '1 lokasi'
        );
    }

    return `${filters.locs.length} lokasi dipilih`;
}
