import { Card, CardContent } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";

export default function TableSkeleton() {
    return (
        <>
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
        </>
    );
}