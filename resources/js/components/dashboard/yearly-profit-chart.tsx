import { useState } from "react";
import ProfitChart from "./profit-chart";
import { Input } from "../ui/input";

type Props = {
    yearlyData: any
    earlyYear: number
    endYear: number
}

export default ({ yearlyData, earlyYear, endYear }: Props) => {
    return (
        <section className="space-y-4 mt-4">
            <div className="lg:flex items-center justify-between">
                <h2 className="text-lg font-semibold">Performa Tahunan</h2>

                <div className="flex items-center gap-2">
                    <Input
                        type='number'
                        defaultValue={earlyYear}
                    />
                    <span className="text-muted-foreground">-</span>
                    <Input
                        type='number'
                        defaultValue={endYear}
                    />
                </div>
            </div>

            <ProfitChart data={yearlyData} />
        </section>
    )
}