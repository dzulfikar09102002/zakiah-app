import {
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
    LabelList,
    XAxis,
} from 'recharts';

import { Card, CardContent } from '../ui/card';
import { ChartContainer, ChartTooltip, ChartTooltipContent } from '../ui/chart';

export default ({ data }: { data: any[] }) => {
    return (
        <Card>
            <CardContent className="h-100">
                <ChartContainer config={{}} className="h-full w-full">
                    <BarChart data={data}>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} />

                        <XAxis dataKey="name" />

                        <ChartTooltip content={<ChartTooltipContent />} />

                        <Legend />

                        <Bar
                            dataKey="sales"
                            fill="var(--chart-1)"
                            radius={4}
                            name="Penjualan"
                        >
                            <LabelList
                                dataKey="sales"
                                position="top"
                                fontSize={12}
                            />
                        </Bar>

                        <Bar
                            dataKey="profit"
                            fill="var(--chart-2)"
                            radius={4}
                            name="Laba"
                        >
                            <LabelList
                                dataKey="profit"
                                position="top"
                                fontSize={10}
                            />
                        </Bar>
                    </BarChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
};
