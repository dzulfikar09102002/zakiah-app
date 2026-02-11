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
    const startIndex = (categories.current_page - 1) * categories.per_page
    return (
        <div className="relative w-full overflow-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>No.</TableHead>
                        <TableHead className="w-[800px]">Nama</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead className="text-center">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {categories.data.map((category: Category, index: number) => (
                        <TableRow key={category.id ?? index}>
                            <TableCell>{startIndex + index + 1}.</TableCell>
                            <TableCell className="min-w-[800px]">{category.name}</TableCell>
                            <TableCell><Badge>{category.status}</Badge></TableCell>
                            <TableCell className="space-x-2 text-center">
                                <Button
                                    size="icon"
                                    variant="outline"
                                >
                                    <Pencil className="size-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="destructive"
                                >
                                    <Trash2 className="size-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                    ))}

                    {categories.data.length === 0 && (
                        <TableRow>
                            <TableCell colSpan={6} className="text-center py-8 text-muted-foreground">
                                Tidak ada data Kategori
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
        </div>
    )
}
export default ProductCategoryTable