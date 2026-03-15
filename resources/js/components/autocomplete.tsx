import {
    Combobox,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList,
} from "@/components/ui/combobox"
import { ComboboxRoot } from "@base-ui/react"
import { useState } from "react"

type Option = {
    label: string
    value: string
}

type Props = {
    options: Option[]
    placeholder: string
    onValueChange?: ((value: Option | null, eventDetails: ComboboxRoot.ChangeEventDetails) => void)
    name?: string,
    defaultValue?: string
}

export default ({ options, placeholder, name, defaultValue, onValueChange }: Props) => {

    const [val, setVal] = useState(defaultValue);

    const onChange = (value: Option | null, eventDetails: ComboboxRoot.ChangeEventDetails) => {
        if (onValueChange) onValueChange(value, eventDetails);
        setVal(value?.value)
    }

    return (
        <>
            <input type="hidden" name={name} defaultValue={val} />
            <Combobox<Option>
                items={options}
                itemToStringValue={(option) => option.label}
                onValueChange={onChange}
                defaultValue={options.find(opt => opt.value == defaultValue)}
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
        </>
    )
}