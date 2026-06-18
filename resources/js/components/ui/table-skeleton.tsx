import { Card, CardContent } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";

export default function TableSkeleton() {
    return (
        <Card>
            <CardContent className="space-y-4 pt-6">
                {/* Filter Section */}
                <div className="flex flex-col gap-2 lg:flex-row lg:justify-between">
                    <Skeleton className="h-10 w-40" />

                    <div className="flex flex-wrap gap-2">
                        <Skeleton className="h-10 w-64" />
                        <Skeleton className="h-10 w-44" />
                        <Skeleton className="h-10 w-56" />
                        <Skeleton className="h-10 w-28" />
                    </div>
                </div>

                {/* Table Header */}
                <div className="border rounded-md">
                    <div className="border-b p-4">
                        <div className="grid grid-cols-6 gap-4">
                            {Array.from({ length: 6 }).map((_, i) => (
                                <Skeleton key={i} className="h-4 w-full" />
                            ))}
                        </div>
                    </div>

                    {/* Table Rows */}
                    <div className="space-y-4 p-4">
                        {Array.from({ length: 10 }).map((_, row) => (
                            <div
                                key={row}
                                className="grid grid-cols-6 gap-4"
                            >
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-full" />
                            </div>
                        ))}
                    </div>
                </div>

                {/* Pagination */}
                <div className="flex justify-between items-center">
                    <Skeleton className="h-8 w-32" />

                    <div className="flex gap-2">
                        <Skeleton className="h-8 w-8" />
                        <Skeleton className="h-8 w-8" />
                        <Skeleton className="h-8 w-8" />
                        <Skeleton className="h-8 w-8" />
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}