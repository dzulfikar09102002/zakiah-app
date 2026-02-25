import { Head } from '@inertiajs/react';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Badge } from '@/components/ui/badge';
import RangeDatePicker from '@/components/range-date-picker';

import React, { useState } from "react"
import {
    TrendingUp,
    DollarSign,
    RotateCcw,
    Package,
    BarChart3,
    Calendar,
    MapPin,
    Percent,
    Box,
} from "lucide-react"

import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
} from "recharts"

import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from "@/components/ui/select"
import { Button } from "@/components/ui/button"
import { Input } from '@/components/ui/input';
import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart';

/* ================= DATA ================= */

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

const locations = ["Semua Lokasi", "Jakarta", "Surabaya", "Bandung", "Medan"]
const years = [2022, 2023, 2024, 2025, 2026]

/* ================= UTIL ================= */

const formatCurrency = (value: number) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    })
        .format(value)
        .replace("Rp", "")
        .trim()

/* ================= COMPONENTS ================= */

function StatCard({
    title,
    value,
    icon: Icon,
    suffix,
}: {
    title: string
    value: string | number
    icon: any
    suffix?: string
}) {
    return (
        <Card>
            <CardHeader className="flex-row justify-between items-center">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
                <div className="size-8 bg-secondary rounded-lg flex items-center justify-center">
                    <Icon className="size-4" />
                </div>
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold">
                    {typeof value === "number" ? formatCurrency(value) : value}
                    {suffix}
                </div>
            </CardContent>
        </Card>
    )
}

function ChartCard({ data }: { data: any[] }) {
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

/* ================= MAIN ================= */

function DashboardContent() {
    const [location, setLocation] = useState("Semua Lokasi")
    const [monthlyYear, setMonthlyYear] = useState("2026")

    return (
        <div className="space-y-4">
            <div className="fixed right-4 bottom-4 z-50 bg-background p-2 rounded-lg border">
                {/* Filter */}
                <div className="flex items-center gap-4">
                    {/* <MapPin className="h-4 w-4" /> */}
                    <Select value={location} onValueChange={setLocation}>
                        <SelectTrigger className="w-48">
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
            </div>

            <div className="justify-between items-center lg:flex">
                <h2 className="text-lg font-semibold">Performa Harian</h2>
                <RangeDatePicker />
            </div>
            {/* Stats */}
            <div className="grid gap-4 md:grid-cols-3">
                <StatCard
                    title="Total Penjualan"
                    value={21418500}
                    icon={DollarSign}
                />
                <StatCard
                    title="Total Laba Bersih"
                    value={5777660}
                    icon={TrendingUp}
                />
                <StatCard title="Pengembalian" value={0} icon={RotateCcw} />
            </div>

            <div className="grid gap-4 md:grid-cols-4">
                <StatCard title="Total Stok" value="28.837" icon={Package} />
                <StatCard
                    title="Nominal HPP"
                    value={1624601365}
                    icon={BarChart3}
                />
                <StatCard
                    title="Harga Stok"
                    value={2337998100}
                    icon={DollarSign}
                />
                <StatCard
                    title="Potensi Laba"
                    value={713396735}
                    icon={Percent}
                    suffix=" (0.44%)"
                />
            </div>

            {/* Charts */}
            <ChartCard data={dailyData} />

            <div className="lg:grid gap-6 grid-cols-2">
                <section className="space-y-4 mt-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-semibold">Performa Bulanan</h2>

                        <Select value={monthlyYear} onValueChange={setMonthlyYear}>
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

                    <ChartCard data={monthlyData} />
                </section>

                <YearlySection />
            </div>
        </div>
    )
}

function YearlySection() {
    const [startYear, setStartYear] = useState(2025);
    const [endYear, setEndYear] = useState(2026);
    return (
        <section className="space-y-4 mt-4">
            <div className="lg:flex items-center justify-between">
                <h2 className="text-lg font-semibold">Performa Tahunan</h2>

                <div className="flex items-center gap-2">
                    <Input
                        type='number'
                        value={startYear}
                        onChange={(e) => setStartYear(+e.target.value)}
                    />
                    <span className="text-muted-foreground">-</span>
                    <Input
                        type='number'
                        value={endYear}
                        onChange={(e) => setEndYear(+e.target.value)}
                    />
                </div>
            </div>

            <ChartCard data={yearlyData} />
        </section>
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

