import { cn } from "@/lib/utils"

export function MainCard({ className, ...props }: React.ComponentProps<"div">) {
    return (
        <div
            data-slot="card"
            className={cn(
                "text-card-foreground flex flex-col gap-6 rounded-xl py-6 bg-background lg:bg-card p-0 lg:py-6 border-0 lg:border",
                className
            )}
            {...props}
        />
    )
}

export function MainCardHeader({ className, ...props }: React.ComponentProps<"div">) {
    return (
        <div
            data-slot="card-header"
            className={cn("flex flex-col gap-1.5 px-6 p-0 lg:px-6", className)}
            {...props}
        />
    )
}

export function MainCardContent({ className, ...props }: React.ComponentProps<"div">) {
    return (
        <div
            data-slot="card-content"
            className={cn("px-6 p-0 lg:px-6", className)}
            {...props}
        />
    )
}
