import {
    Combobox,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList,
} from "@/components/ui/combobox"
import { ComboboxRoot } from "@base-ui/react"

type Option = {
    label: string
    value: string
}

type Props = {
    options: Option[]
    placeholder: string
    onValueChange?: ((value: Option | null, eventDetails: ComboboxRoot.ChangeEventDetails) => void)
}

export default ({ options, placeholder, onValueChange }: Props) => {
    return (
        <Combobox<Option>
            items={options}
            itemToStringValue={(option) => option.label}
            onValueChange={onValueChange}
        >
            <ComboboxInput placeholder={placeholder} className='w-full' />
            <ComboboxContent>
                <ComboboxEmpty>Item tidak ditemukan</ComboboxEmpty>
                <ComboboxList>
                    {(option) => (
                        <ComboboxItem key={option.value} value={option}>
                            {option.label}
                        </ComboboxItem>
                    )}
                </ComboboxList>
            </ComboboxContent>
        </Combobox>
    )
}