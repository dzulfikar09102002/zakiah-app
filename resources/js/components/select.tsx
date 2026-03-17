import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import { Option } from "@/types"

type Props = {
    name?: string
    placeholder?: string
    options: Option[]
    defaultValue?: string
    onValueChange?: (value: string) => void
}

export default ({ placeholder, options, name, defaultValue, onValueChange }: Props) => {
    return (
        <Select onValueChange={onValueChange} name={name} defaultValue={defaultValue}>
            <SelectTrigger className="w-full">
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent>
                <SelectGroup>
                    {options.map((opt, i) => (
                        <SelectItem key={i} value={opt.value}>{opt.label}</SelectItem>
                    ))}
                </SelectGroup>
            </SelectContent>
        </Select>
    )
}
