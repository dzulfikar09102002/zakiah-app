import { router } from "@inertiajs/react"
import { ChevronLeft, ChevronRight } from "lucide-react"
import { Button } from "./ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select"

type Props = {
    data: {
        current_page: number
        last_page: number
        per_page: number
        total: number
        prev_page_url: string | null
        next_page_url: string | null
    }
    showing: number
    onPerPageChange?: (val: number) => void
}

export default function TablePagination({ data, showing, onPerPageChange }: Props) {
    const { per_page, total, prev_page_url, next_page_url } = data

    return (
        <div className="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

            <div className="text-sm text-muted-foreground">
                Menampilkan <b>{showing}</b> dari <b>{total}</b> data
            </div>

            <div className="flex items-center gap-2">

                {onPerPageChange && (
                    <div className="flex items-center gap-2">
                        <span className="text-sm text-muted-foreground">Baris:</span>

                        <Select
                            value={String(per_page)}
                            onValueChange={(val) => onPerPageChange(Number(val))}
                        >
                            <SelectTrigger className="h-9 w-[90px]">
                                <SelectValue placeholder="Per page" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem value="10">10</SelectItem>
                                <SelectItem value="25">25</SelectItem>
                                <SelectItem value="50">50</SelectItem>
                                <SelectItem value="100">100</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>)}
                <Button
                    variant="outline"
                    disabled={!prev_page_url}
                    onClick={() => prev_page_url && router.get(prev_page_url, {}, { preserveScroll: true })}
                    className="rounded-md border px-3 py-1 text-sm disabled:opacity-50"
                > <ChevronLeft />
                    Sebelumnya
                </Button>
                <Button
                    variant="outline"
                    disabled={!next_page_url}
                    onClick={() => next_page_url && router.get(next_page_url, {}, { preserveScroll: true })}
                    className="rounded-md border px-3 py-1 text-sm disabled:opacity-50"
                >
                    Selanjutnya
                    <ChevronRight />
                </Button>
            </div>
        </div>
    )
}
