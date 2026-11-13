import { createContext, useContext } from "react"
import type { SubmitEventHandler } from "react"
import type { Location } from "@/lib/model"

type LocationChooserContextValue = {
    locations: Location[]
    filtered: Location[]
    search: string
    onSearch: SubmitEventHandler<HTMLFormElement>
}

const LocationChooserContext =
    createContext<LocationChooserContextValue | null>(null)

export const useLocationChooser = () => {
    const ctx = useContext(LocationChooserContext)
    if (!ctx) {
        throw new Error(
            "LocationChooser components must be used inside LocationChooserContainer"
        )
    }
    return ctx
}

export default LocationChooserContext
