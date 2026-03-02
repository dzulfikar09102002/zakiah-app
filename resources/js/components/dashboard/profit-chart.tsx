import { Bar, BarChart, CartesianGrid, XAxis } from "recharts"
import { Card, CardContent } from "../ui/card"
import { ChartContainer, ChartTooltip, ChartTooltipContent } from "../ui/chart"

export default ({ data }: { data: any[] }) => {
    return (
        <Card>
            <CardContent className="h-80">
                <ChartContainer config={{}} className='w-full h-full'>
                    <BarChart data={data}>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} />
                        <XAxis dataKey="name" />
                        {/* <YAxis /> */}
                        <ChartTooltip
                            content={<ChartTooltipContent />}
                        />
                        <Bar dataKey="sales" fill="var(--chart-1)" radius={4} />
                        <Bar dataKey="profit" fill="var(--chart-2)" radius={4} />
                    </BarChart>
                </ChartContainer>
            </CardContent>
        </Card>
    )
}