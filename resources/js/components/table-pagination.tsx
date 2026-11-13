import { router } from "@inertiajs/react"
import {
    ChevronLeft,
    ChevronRight
} from "lucide-react"
import type { Pagination } from "@/lib/model"
import { Button } from "./ui/button"
import {
    Combobox,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList
} from "./ui/combobox"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from "./ui/select"
import { Skeleton } from "./ui/skeleton"

type Props<T> = {
    /** undefined = data tabel masih dimuat (prop deferred); paginasi tetap tampil. */
    pagination?: Pagination<T>
}

/**
 * Total baris terakhir per halaman + filter (tanpa page/per_page), agar saat pindah halaman atau
 * ganti per page paginasi tetap tampil penuh selama data berikutnya dimuat.
 */
const knownTotals = new Map<string, number>()

function filterKey(): string {
    const params = new URLSearchParams(window.location.search)
    params.delete('page')
    params.delete('per_page')
    params.sort()

    return `${window.location.pathname}?${params.toString()}`
}

function urlNumber(name: string): number | undefined {
    const value = Number(new URLSearchParams(window.location.search).get(name))

    return Number.isFinite(value) && value > 0 ? value : undefined
}

export default function TablePagination<T>({ pagination }: Props<T>) {
    const key = filterKey()

    if (pagination) {
        knownTotals.set(key, pagination.total)
    }

    const perPage = pagination?.per_page ?? urlNumber('per_page') ?? 10
    const currentPage = pagination?.current_page ?? urlNumber('page') ?? 1
    const total = pagination?.total ?? knownTotals.get(key)
    const lastPage = pagination?.last_page ?? (total !== undefined ? Math.max(1, Math.ceil(total / perPage)) : undefined)

    // Dengan data: URL dari server (sama seperti sebelumnya). Saat dimuat: URL halaman saat ini.
    const baseUrl = pagination?.first_page_url ?? `${window.location.pathname}${window.location.search}`
    const visit = (params: Record<string, string | number>) =>
        router.get(baseUrl, params, { preserveScroll: true })

    const goToPage = (direction: -1 | 1) => {
        if (pagination) {
            const url = direction < 0 ? pagination.prev_page_url : pagination.next_page_url

            if (url) {
                router.get(url, {}, { preserveScroll: true })
            }

            return
        }

        visit({ page: currentPage + direction })
    }

    const canPrev = pagination ? Boolean(pagination.prev_page_url) : currentPage > 1 && lastPage !== undefined
    const canNext = pagination ? Boolean(pagination.next_page_url) : lastPage !== undefined && currentPage < lastPage

    // Fungsi untuk handle perpindahan halaman via Select
    const handlePageChange = (page: string | null) => {
        if (page) {
            visit({ page })
        }
    }

    return (
        <div className="lg:flex justify-between mt-4">

            <div className="text-sm flex items-center gap-2 mb-2">
                <span>Menampilkan</span>
                <Select
                    value={String(perPage)}
                    onValueChange={(val) => visit({ per_page: val, page: 1 })}
                >
                    <SelectTrigger className="h-9 w-20">
                        <SelectValue placeholder="Per page" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="10">10</SelectItem>
                        <SelectItem value="25">25</SelectItem>
                        <SelectItem value="50">50</SelectItem>
                        <SelectItem value="100">100</SelectItem>
                    </SelectContent>
                </Select>
                <span className="flex items-center gap-1">
                    dari{' '}
                    {total !== undefined ? (
                        <b>{total}</b>
                    ) : (
                        <Skeleton className="inline-block h-4 w-10 align-middle" aria-label="Memuat jumlah data" />
                    )}{' '}
                    baris data
                </span>
            </div>

            <div className="flex items-center gap-2 text-sm">
                <div>Halaman</div>
                <div>
                    <Button
                        size={"icon"}
                        variant="outline"
                        disabled={!canPrev}
                        aria-label="Halaman sebelumnya"
                        onClick={() => goToPage(-1)}
                    >
                        <ChevronLeft />
                    </Button>
                </div>

                {/* --- Select Page Baru Disisipkan di Sini --- */}
                <Combobox
                    key={`${currentPage}-${lastPage ?? 'x'}`}
                    items={Array.from({ length: lastPage ?? currentPage }, (_, i) => (i + 1).toString())}
                    defaultValue={String(currentPage)}
                    onValueChange={(handlePageChange)}
                >
                    <ComboboxInput placeholder="Pilih Halaman" />
                    <ComboboxContent>
                        <ComboboxEmpty>No items found.</ComboboxEmpty>
                        <ComboboxList>
                            {page => (
                                <ComboboxItem key={page} value={String(page)}>
                                    {page}
                                </ComboboxItem>
                            )}
                        </ComboboxList>
                    </ComboboxContent>
                </Combobox>
                {/* ------------------------------------------ */}

                <div>
                    <Button
                        size={"icon"}
                        variant="outline"
                        disabled={!canNext}
                        aria-label="Halaman berikutnya"
                        onClick={() => goToPage(1)}
                    >
                        <ChevronRight />
                    </Button>
                </div>
            </div>
        </div>
    )
}
