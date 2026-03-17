import { Check, ChevronDown } from "lucide-react"
import { Button } from "@/components/ui/button"
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover"

import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from "@/components/ui/command"
import { useState } from "react"
import { Option } from "@/types"

type Props = {
    options: Option[]
    placeholder?: ((values: string[]) => string) | string
    name?: string
    defaultValues?: string[]
}

export default ({ options, placeholder, name, defaultValues }: Props) => {
    const [open, setOpen] = useState(false)
    const [values, setValues] = useState<string[]>(defaultValues?.map(el => el.toString()) || [])

    function toggleValue(value: string) {
        if (value === 'all') {
            setValues(values.includes(value) ? [] : options.map(el => el.value.toString()))
        } else {
            setValues(values.includes(value.toString()) ? values.filter((v) => v !== value.toString() && v !== 'all') : [...values, value.toString()])
        }
    }

    return (
        <>
            {values.map((val, idx) => <input key={idx} type="hidden" value={val} name={name} />)}
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        variant="outline"
                        role="combobox"
                        className="justify-between"
                    >
                        {typeof placeholder == 'function' ? placeholder(values) : placeholder}
                        <ChevronDown className="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                </PopoverTrigger>

                <PopoverContent className="p-0">
                    <Command>
                        <CommandInput />

                        <CommandList>
                            <CommandEmpty>Item tidak ditemukan</CommandEmpty>

                            <CommandGroup>
                                {options.map((option) => {
                                    const checked = values.includes(option.value.toString())

                                    return (
                                        <CommandItem
                                            key={option.value}
                                            onSelect={() => toggleValue(option.value)}
                                        >

                                            {option.label}

                                            {checked && (
                                                <Check className="ml-auto h-4 w-4" />
                                            )}
                                        </CommandItem>
                                    )
                                })}
                            </CommandGroup>

                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
        </>
    )
}