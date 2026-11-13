import { format, subDays } from 'date-fns';
import { id } from 'date-fns/locale';
import { CalendarIcon } from 'lucide-react';
import QueryString from 'qs';
import { useEffect, useState } from 'react';
import type { DateRange } from 'react-day-picker';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { formatDate } from '@/lib/utils';

type Props = {
    onValueChange?: (value?: DateRange) => void;
    prefix?: string;

    defaultStartDate?: Date;
    defaultEndDate?: Date;
    className?: string;
};

export default function RangeDatePicker({
    onValueChange,
    defaultStartDate,
    defaultEndDate,
}: Props) {
    const getInitialDate = (): DateRange => {
        const query = QueryString.parse(window.location.search, {
            ignoreQueryPrefix: true,
        }) as { start_at?: string; end_at?: string };

        if (query.start_at && query.end_at) {
            return {
                from: new Date(query.start_at),
                to: new Date(query.end_at),
            };
        }

        return {
            from: defaultStartDate ?? subDays(new Date(), 7),
            to: defaultEndDate ?? new Date(),
        };
    };

    const [date, setDate] = useState<DateRange | undefined>(getInitialDate());

    useEffect(() => {
        onValueChange?.(date);
    }, [date]);

    return (
        <>
            <input
                type="hidden"
                name="start_at"
                value={date?.from ? format(date.from, 'yyyy-MM-dd') : ''}
            />

            <input
                type="hidden"
                name="end_at"
                value={date?.to ? format(date.to, 'yyyy-MM-dd') : ''}
            />

            <Popover>
                <PopoverTrigger asChild>
                    <Button
                        variant="outline"
                        className="w-full justify-start px-2.5 lg:w-auto"
                    >
                        <CalendarIcon />
                        {date?.from ? (
                            date.to ? (
                                <>
                                    {formatDate(date.from)} -{' '}
                                    {formatDate(date.to)}
                                </>
                            ) : (
                                formatDate(date.from)
                            )
                        ) : (
                            <span>Pilih tanggal</span>
                        )}
                    </Button>
                </PopoverTrigger>

                <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                        mode="range"
                        selected={date}
                        onSelect={setDate}
                        numberOfMonths={2}
                        locale={id}
                        defaultMonth={date?.from}
                    />
                </PopoverContent>
            </Popover>
        </>
    );
}
