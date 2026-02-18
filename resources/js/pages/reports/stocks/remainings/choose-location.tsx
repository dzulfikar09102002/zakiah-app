import { LocationChooserContainer, LocationChooserFilter, LocationChooserList } from "@/components/reports/stocks/remainings/location-chooser"
import { Separator } from "@/components/ui/separator"
import { useQuery } from "@/hooks/use-query"
import AppLayout from "@/layouts/app-layout"
import { Location } from "@/lib/model"
import stockRemaining from "@/routes/stock-remaining"
import { BreadcrumbItem } from "@/types"
import { Head, router } from "@inertiajs/react"
import { SubmitEvent, useState } from "react"

const title = "Laporan Stok Sisa"

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: stockRemaining.chooseLocation().url
    }
]

type Props = {
    locations: Location[]
}

export default ({ locations }: Props) => {
    const query = useQuery()
    const [search, setSearch] = useState(query.search || '');

    const onSearch = (e: SubmitEvent<HTMLFormElement>) => {
        e.preventDefault()

        const form = e.currentTarget
        const data = new FormData(form)
        const val = data.get('search')?.toString() || ''
        setSearch(val)

        router.replace({
            url: stockRemaining.chooseLocation({
                query: {
                    search: val
                }
            }).url,
            preserveState: true,
            preserveScroll: true,
        })
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <LocationChooserContainer locations={locations} onSearch={onSearch} search={search}>
                <LocationChooserFilter />
                <Separator className="my-4" />
                <LocationChooserList />
            </LocationChooserContainer>
        </AppLayout>
    )
}
