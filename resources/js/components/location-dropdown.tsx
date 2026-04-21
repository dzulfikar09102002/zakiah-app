"use client";

import { FC, useEffect, useState, useMemo } from "react";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Check, MapPinIcon, Square, SquareCheck } from "lucide-react";
import {
    Command,
    CommandEmpty,
    CommandInput,
    CommandItem,
    CommandList,
} from "@/components/ui/command";

type Locations = {
    id: number;
    name: string;
};

interface LocationDropdownProps {
    options: Locations[]; // 🔥 data dari luar
    multiSelect?: boolean;
    disabled?: boolean;
    full?: boolean;
    defaultSelectAll?: boolean;
    defaultId?: number;
    defaultIds?: number[];
    defaultExcludeIds?: number[];

    handleIdChange?: (id: number) => void;
    handleIdsChange?: (ids: number[]) => void;
    handleExcludeIdsChange?: (ids: number[]) => void;
    handleSelectAllChange?: (selectAll: boolean) => void;
}

const LocationDropdown: FC<LocationDropdownProps> = (props) => {

    const [selectAll, setSelectAll] = useState(props.defaultSelectAll ?? true);
    const [keyword, setKeyword] = useState("");
    const [id, setId] = useState(props.defaultId ?? 0);
    const [ids, setIds] = useState<number[]>(props.defaultIds ?? []);
    const [excludeIds, setExcludeIds] = useState<number[]>(props.defaultExcludeIds ?? []);

    // 🔥 FILTER LOCAL
    const filteredOptions = useMemo(() => {
        return props.options.filter((loc) =>
            loc.name.toLowerCase().includes(keyword.toLowerCase())
        );
    }, [keyword, props.options]);

    // 🔥 EMIT KE PARENT
    useEffect(() => {
        props.handleIdChange?.(id);
    }, [id]);

    useEffect(() => {
        props.handleIdsChange?.(ids);
    }, [ids]);

    useEffect(() => {
        props.handleExcludeIdsChange?.(excludeIds);
    }, [excludeIds]);

    useEffect(() => {
        props.handleSelectAllChange?.(selectAll);
    }, [selectAll]);

    const itemChecked = (selectedId: number) => {
        if (selectAll && !excludeIds.includes(selectedId)) return true;
        if (!selectAll && ids.includes(selectedId)) return true;
        return id === selectedId;
    };

    const handleSelect = (loc: Locations) => {
        if (!props.multiSelect) {
            setId(loc.id);
            return;
        }

        if (selectAll) {
            // exclude mode
            setExcludeIds((prev) =>
                prev.includes(loc.id)
                    ? prev.filter((x) => x !== loc.id)
                    : [...prev, loc.id]
            );
        } else {
            // include mode
            setIds((prev) =>
                prev.includes(loc.id)
                    ? prev.filter((x) => x !== loc.id)
                    : [...prev, loc.id]
            );
        }
    };

    const handleSelectAll = () => {
        setSelectAll((prev) => !prev);
        setIds([]);
        setExcludeIds([]);
    };

    const checkMark = (checked: boolean) =>
        props.multiSelect ? (
            checked ? (
                <SquareCheck className="mr-2 h-4 w-4" />
            ) : (
                <Square className="mr-2 h-4 w-4" />
            )
        ) : (
            <Check className={cn("mr-2 h-4 w-4", checked ? "opacity-100" : "opacity-0")} />
        );

    const labelButton = () => {
        if (!props.multiSelect) {
            const loc = props.options.find((l) => l.id === id);
            return loc ? loc.name : "Pilih lokasi";
        }

        if (selectAll && excludeIds.length > 0) {
            return `${excludeIds.length} lokasi dikecualikan`;
        }

        if (!selectAll && ids.length > 0) {
            return `${ids.length} lokasi dipilih`;
        }

        if (selectAll) {
            return "Semua lokasi";
        }

        return "Pilih lokasi";
    };

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    disabled={props.disabled}
                    className={cn(
                        "w-[180px] justify-start",
                        props.full && "w-full"
                    )}
                >
                    <MapPinIcon/>
                    <span>{labelButton()}</span>
                </Button>
            </PopoverTrigger>

            <PopoverContent className="p-0">
                <Command shouldFilter={false}>
                    <CommandInput
                        placeholder="Cari lokasi..."
                        onValueChange={setKeyword}
                    />

                    <CommandList>
                        <CommandEmpty>Tidak ditemukan</CommandEmpty>

                        {props.multiSelect && (
                            <CommandItem onSelect={handleSelectAll}>
                                {checkMark(selectAll)} Semua lokasi
                            </CommandItem>
                        )}

                        {filteredOptions.map((loc) => (
                            <CommandItem
                                key={loc.id}
                                value={loc.id.toString()}
                                onSelect={() => handleSelect(loc)}
                            >
                                {checkMark(itemChecked(loc.id))}
                                {loc.name}
                            </CommandItem>
                        ))}
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
};

export default LocationDropdown;