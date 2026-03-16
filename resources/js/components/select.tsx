import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"

type Option = {
    label: string
    value: string
}

type Props = {
    name?: string
    placeholder?: string
    options: Option[]
    onValueChange?: (value: string) => void
}

export default ({ placeholder, options, name, onValueChange }: Props) => {
    return (
        <Select onValueChange={onValueChange} name={name}>
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
