import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover"
import { format } from "date-fns"
import { CalendarIcon } from "lucide-react"
import { useEffect, useState } from "react"
import { DateRange } from "react-day-picker"

type Props = {
    onValueChange?: (value?: DateRange) => void
    prefix?: string
}

export default ({ onValueChange, prefix }: Props) => {
    const [date, setDate] = useState<DateRange | undefined>({
        from: new Date(),
        to: new Date(),
    })

    useEffect(() => {
        if (onValueChange) onValueChange(date)
    }, [date]);

    return (
        <>
            <input type="hidden" name={`${prefix ? prefix + '_' : ''}start_at}`} defaultValue={date?.from?.toISOString()} />
            <input type="hidden" name={`${prefix ? prefix + '_' : ''}end_at}`} defaultValue={date?.to?.toISOString()} />
            <Popover>
                <PopoverTrigger asChild>
                    <Button
                        variant="outline"
                        id="date-picker-range"
                        className="justify-start px-2.5 font-normal"
                    >
                        <CalendarIcon />
                        {date?.from ? (
                            date.to ? (
                                <>
                                    {format(date.from, "LLL dd, y")} -{" "}
                                    {format(date.to, "LLL dd, y")}
                                </>
                            ) : (
                                format(date.from, "LLL dd, y")
                            )
                        ) : (
                            <span>Pick a date</span>
                        )}
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                        mode="range"
                        defaultMonth={date?.from}
                        selected={date}
                        onSelect={setDate}
                        numberOfMonths={2}
                    />
                </PopoverContent>
            </Popover>
        </>
    )
}
