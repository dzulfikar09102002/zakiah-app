// import { Button } from "@/components/ui/button"
// import { Input } from "@/components/ui/input"
// import { Item, ItemActions, ItemContent, ItemDescription, ItemTitle } from "@/components/ui/item"
// import { Separator } from "@/components/ui/separator"
// import { Location } from "@/lib/model"
// import { capitalize } from "@/lib/utils"
// import stockRemaining from "@/routes/stock-remaining"
// import { Link } from "@inertiajs/react"
// import { Search } from "lucide-react"
// import { SubmitEventHandler } from "react"

// type Props = {
//     onSearch: SubmitEventHandler<HTMLFormElement>
//     search: string
//     locations: Location[]
// }

// export default ({ locations, onSearch, search }: Props) => {
//     const term = search.toLowerCase()
//     const isSimilar = (key: string | undefined) => key?.toLowerCase().includes(term)

//     const filtered = locations.filter(({ name, full_address, city, postal_code }) =>
//         isSimilar(name)
//         || isSimilar(full_address)
//         || isSimilar(city)
//         || isSimilar(postal_code)
//     )
//     return (
//         <div className="@container">
//             <form onSubmit={onSearch} className="grid @lg:flex gap-2">
//                 <Input
//                     name="search"
//                     defaultValue={search}
//                     placeholder="Cari lokasi..."
//                 />
//                 <Button type="submit" variant="secondary">
//                     <Search /> Cari
//                 </Button>
//             </form>

//             <Separator className="my-4" />

//             <div className="grid @lg:grid-cols-3 gap-4">
//                 {filtered.map(location => (
//                     <Item key={location.id} variant="outline">
//                         <ItemContent>
//                             <ItemTitle>{location.name}</ItemTitle>
//                             <ItemDescription>
//                                 {[location.full_address?.replace(/\s*\([^)]*\)/g, ''), location.city, location.postal_code]
//                                     .filter(Boolean)
//                                     .join(', ') || capitalize(location.name)}
//                             </ItemDescription>
//                         </ItemContent>
//                         <ItemActions>
//                             <Button size="sm" asChild>
//                                 <Link href={stockRemaining.report(location.id)}>
//                                     Pilih
//                                 </Link>
//                             </Button>
//                         </ItemActions>
//                     </Item>
//                 ))}
//             </div>
//             {!filtered.length && (
//                 <div className="text-center text-muted-foreground py-8">
//                     Lokasi tidak ditemukan
//                 </div>
//             )}
//         </div>
//     )
// }

import { ReactNode } from "react"
import { Location } from "@/lib/model"
import LocationChooserContext, { useLocationChooser } from "@/context/location-chooser-context"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Search } from "lucide-react"
import {
    Item,
    ItemActions,
    ItemContent,
    ItemDescription,
    ItemTitle,
} from "@/components/ui/item"
import { Link } from "@inertiajs/react"
import { Separator } from "@/components/ui/separator"
import stockRemaining from "@/routes/stock-remaining"
import { capitalize } from "@/lib/utils"

type Props = {
    locations: Location[]
    search: string
    onSearch: React.FormEventHandler<HTMLFormElement>
    children: ReactNode
}

export const LocationChooserContainer = ({
    locations,
    search,
    onSearch,
    children,
}: Props) => {
    const term = search.toLowerCase()
    const isSimilar = (key?: string) =>
        key?.toLowerCase().includes(term)

    const filtered = locations.filter(
        ({ name, full_address, city, postal_code }) =>
            isSimilar(name) ||
            isSimilar(full_address) ||
            isSimilar(city) ||
            isSimilar(postal_code)
    )

    return (
        <div className="@container">
            <LocationChooserContext.Provider
                value={{ locations, filtered, search, onSearch }}
            >
                {children}
            </LocationChooserContext.Provider>
        </div>
    )
}


export const LocationChooserFilter = () => {
    const { search, onSearch } = useLocationChooser()

    return (
        <form onSubmit={onSearch} className="grid @md:flex gap-2">
            <Input
                name="search"
                defaultValue={search}
                placeholder="Cari lokasi..."
            />
            <Button type="submit" variant="secondary">
                <Search /> Cari
            </Button>
        </form>
    )
}

export const LocationChooserList = () => {
    const { filtered } = useLocationChooser()

    return (
        <>
            <div className="grid @lg:grid-cols-3 gap-2 @lg:gap-4">
                {filtered.map((location) => (
                    <Item key={location.id} variant="outline">
                        <ItemContent>
                            <ItemTitle>{location.name}</ItemTitle>
                            <ItemDescription>
                                {[
                                    location.full_address?.replace(
                                        /\s*\([^)]*\)/g,
                                        ""
                                    ),
                                    location.city,
                                    location.postal_code,
                                ]
                                    .filter(Boolean)
                                    .join(", ") ||
                                    capitalize(location.name)}
                            </ItemDescription>
                        </ItemContent>
                        <ItemActions>
                            <Button size="sm" asChild>
                                <Link
                                    href={stockRemaining.report(
                                        location.id
                                    )}
                                >
                                    Pilih
                                </Link>
                            </Button>
                        </ItemActions>
                    </Item>
                ))}
            </div>

            {!filtered.length && (
                <div className="text-center text-muted-foreground py-8">
                    Lokasi tidak ditemukan
                </div>
            )}
        </>
    )
}


