import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "../ui/select"
import ProfitChart from "./profit-chart"

type Props = {
    monthlyYear: number
    years: number[]
    monthlyData: any
}
export default ({ monthlyYear, years, monthlyData }: Props) => {
    return (<>
        <section className="space-y-4 mt-4">
            <div className="flex items-center justify-between">
                <h2 className="text-lg font-semibold">Performa Bulanan</h2>
                <Select defaultValue={String(monthlyYear)}>
                    <SelectTrigger className="w-32">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {years.map((y) => (
                            <SelectItem key={y} value={String(y)}>
                                {y}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
            <ProfitChart data={monthlyData} />
        </section>
    </>)
}