import { Pencil, Trash2 } from "lucide-react"
import { Button } from "@/components/ui/button"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import type { Category } from "@/lib/model"
import { Badge } from "../ui/badge"

type Props = {
    categories: {
        data: Category[]
        current_page: number
        per_page: number
    }
}

function ProductCategoryTable({ categories }: Props) {
    return (
        <div className="relative w-full overflow-auto">

        </div>
    )
}
export default ProductCategoryTable