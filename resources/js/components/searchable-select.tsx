import { Check, ChevronsUpDown } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

export type SearchableOption = {
    value: string;
    label: string;
    /** Keterangan kecil di sisi kanan, mis. "Office". */
    hint?: string;
};

type Props = {
    options: SearchableOption[];
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    searchPlaceholder?: string;
    emptyText?: string;
    disabled?: boolean;
    className?: string;
    id?: string;
};

/**
 * Combobox satu pilihan dengan pencarian. Memakai Popover + Command supaya
 * juga berfungsi di dalam Dialog, tidak seperti ui/combobox.
 */
export default function SearchableSelect({
    options,
    value,
    onChange,
    placeholder = 'Pilih',
    searchPlaceholder = 'Cari...',
    emptyText = 'Tidak ditemukan',
    disabled,
    className,
    id,
}: Props) {
    const [open, setOpen] = useState(false);
    const selected = options.find((option) => option.value === value);

    return (
        <Popover open={open} onOpenChange={setOpen} modal>
            <PopoverTrigger asChild>
                <Button
                    id={id}
                    type="button"
                    variant="outline"
                    role="combobox"
                    aria-expanded={open}
                    disabled={disabled}
                    className={cn(
                        'w-full cursor-pointer justify-between font-normal',
                        className,
                    )}
                >
                    <span
                        className={cn(
                            'truncate',
                            !selected && 'text-muted-foreground',
                        )}
                    >
                        {selected ? selected.label : placeholder}
                    </span>
                    <ChevronsUpDown className="shrink-0 opacity-50" />
                </Button>
            </PopoverTrigger>

            <PopoverContent
                align="start"
                className="w-(--radix-popover-trigger-width) p-0"
            >
                <Command>
                    <CommandInput
                        placeholder={searchPlaceholder}
                        // Di bawah 16px, iPhone memperbesar layar saat kolom difokuskan.
                        className="text-base md:text-sm"
                    />
                    <CommandList>
                        <CommandEmpty>{emptyText}</CommandEmpty>
                        {options.map((option) => (
                            <CommandItem
                                key={option.value}
                                value={`${option.label} ${option.hint ?? ''} ${option.value}`}
                                className="min-h-11 cursor-pointer md:min-h-0"
                                onSelect={() => {
                                    onChange(option.value);
                                    setOpen(false);
                                }}
                            >
                                <Check
                                    className={cn(
                                        'shrink-0',
                                        option.value === value
                                            ? 'opacity-100'
                                            : 'opacity-0',
                                    )}
                                />
                                <span className="min-w-0 flex-1 break-words">
                                    {option.label}
                                </span>
                                {option.hint && (
                                    <span className="shrink-0 text-xs text-muted-foreground">
                                        {option.hint}
                                    </span>
                                )}
                            </CommandItem>
                        ))}
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
