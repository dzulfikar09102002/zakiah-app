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
    placeholder?: string
    options: Option[]
    onValueChange: (value: string) => void
}

export default ({ placeholder, options, onValueChange }: Props) => {
    return (
        <Select onValueChange={onValueChange}>
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
