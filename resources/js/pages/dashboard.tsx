import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import RangeDatePicker from '@/components/date-range-picker';
import { useState } from "react"
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from "@/components/ui/select"
import { Button } from "@/components/ui/button"
import { Separator } from '@/components/ui/separator';
import TopFive from '@/components/dashboard/top-five';
import DaillyOverview from '@/components/dashboard/dailly-overview';
import ProfitChart from '@/components/dashboard/profit-chart';
import MonthlyProfitChart from '@/components/dashboard/monthly-profit-chart';
import YearlyProfitChart from '@/components/dashboard/yearly-profit-chart';
import { MapPin } from 'lucide-react';

const dailyData = [
    { name: "2026-02-15", sales: 12500000, profit: 3200000 },
    { name: "2026-02-16", sales: 15800000, profit: 4100000 },
    { name: "2026-02-17", sales: 14200000, profit: 3800000 },
    { name: "2026-02-18", sales: 19500000, profit: 5200000 },
    { name: "2026-02-19", sales: 17100000, profit: 4600000 },
    { name: "2026-02-20", sales: 21418500, profit: 5777660 },
    { name: "2026-02-21", sales: 18900000, profit: 4900000 },
]

const monthlyData = [
    { name: "Jan", sales: 450000000, profit: 120000000 },
    { name: "Feb", sales: 520000000, profit: 145000000 },
    { name: "Mar", sales: 480000000, profit: 130000000 },
    { name: "Apr", sales: 610000000, profit: 170000000 },
    { name: "Mei", sales: 550000000, profit: 155000000 },
    { name: "Jun", sales: 590000000, profit: 165000000 },
    { name: "Jul", sales: 630000000, profit: 180000000 },
    { name: "Agu", sales: 580000000, profit: 160000000 },
    { name: "Sep", sales: 650000000, profit: 190000000 },
    { name: "Okt", sales: 710000000, profit: 210000000 },
    { name: "Nov", sales: 680000000, profit: 200000000 },
    { name: "Des", sales: 850000000, profit: 250000000 },
]

const yearlyData = [
    { name: "2022", sales: 4800000000, profit: 1200000000 },
    { name: "2023", sales: 5600000000, profit: 1450000000 },
    { name: "2024", sales: 6200000000, profit: 1700000000 },
    { name: "2025", sales: 7500000000, profit: 2100000000 },
    { name: "2026", sales: 8200000000, profit: 2400000000 },
]

const dailyOverviewData = {
    total_sale: 21418500,
    total_profit: 5777660,
    total_return: 0,
    total_stock: 28837,
    total_hpp: 1624601365,
    total_stock_price: 2337998100,
    potential_profit: 713396735,
}

const locations = ["Semua Lokasi", "Jakarta", "Surabaya", "Bandung", "Medan"]
const years = [2022, 2023, 2024, 2025, 2026]

function DashboardContent() {
    const [location, setLocation] = useState("Semua Lokasi")

    return (
        <div className="space-y-4">
            <div className="flex items-center gap-2">
                <Button size={'icon'}><MapPin /></Button>
                <Select value={location} onValueChange={setLocation}>
                    <SelectTrigger className="w-36">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {locations.map((l) => (
                            <SelectItem key={l} value={l}>
                                {l}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <Separator />

            <div className="justify-between items-center lg:flex">
                <h2 className="text-lg font-semibold">Performa Harian</h2>
                <RangeDatePicker />
            </div>
            <DaillyOverview {...dailyOverviewData} />

            {/* Charts */}
            <ProfitChart data={dailyData} />

            <div className="lg:grid gap-6 grid-cols-2">
                <MonthlyProfitChart monthlyData={monthlyData} years={years} monthlyYear={2026} />
                <YearlyProfitChart yearlyData={yearlyData} earlyYear={2022} endYear={2026} />
            </div>
            <TopFive />
        </div>
    )
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <DashboardContent />
        </AppLayout>
    );
}

