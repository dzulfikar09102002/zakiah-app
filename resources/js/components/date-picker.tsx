import { format, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';
import { CalendarIcon, X } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

type Props = {
    /** Tanggal dalam format yyyy-MM-dd, atau '' bila kosong. */
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    disabled?: boolean;
    clearable?: boolean;
    /** Tanggal sebelum ini tidak bisa dipilih (yyyy-MM-dd). */
    minDate?: string;
};

export default function DatePicker({
    value,
    onChange,
    placeholder = 'Pilih tanggal',
    disabled,
    clearable,
    minDate,
}: Props) {
    const [open, setOpen] = useState(false);
    const selected = value ? parseISO(value) : undefined;
    const min = minDate ? parseISO(minDate) : undefined;

    return (
        <div className="flex gap-1">
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={disabled}
                        className={cn(
                            'flex-1 justify-start font-normal',
                            !value && 'text-muted-foreground',
                        )}
                    >
                        <CalendarIcon />
                        {selected
                            ? format(selected, 'dd MMMM yyyy', { locale: id })
                            : placeholder}
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                        mode="single"
                        selected={selected}
                        defaultMonth={selected ?? min}
                        disabled={min ? { before: min } : undefined}
                        onSelect={(day) => {
                            if (day) {
                                onChange(format(day, 'yyyy-MM-dd'));
                                setOpen(false);
                            }
                        }}
                    />
                </PopoverContent>
            </Popover>
            {clearable && value && !disabled && (
                <Button
                    type="button"
                    size="icon"
                    variant="ghost"
                    onClick={() => onChange('')}
                >
                    <X />
                </Button>
            )}
        </div>
    );
}
