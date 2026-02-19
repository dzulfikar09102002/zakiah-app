import { LocationChooserContainer, LocationChooserFilter, LocationChooserList } from "@/components/reports/stocks/remainings/location-chooser"
import TablePagination from "@/components/table-pagination"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import {
    Card,
    CardContent,
    CardHeader
} from "@/components/ui/card"
import {
    Combobox,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList
} from "@/components/ui/combobox"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Separator } from "@/components/ui/separator"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from "@/components/ui/table"
import { useQuery } from "@/hooks/use-query"
import AppLayout from "@/layouts/app-layout"
import {
    Location,
    Pagination,
    ProductStock
} from "@/lib/model"
import { capitalize } from "@/lib/utils"
import stockRemaining from "@/routes/stock-remaining"
import { BreadcrumbItem } from "@/types"
import {
    Form,
    Head,
    router
} from "@inertiajs/react"
import {
    FileDown,
    MapPinPen,
    Search
} from "lucide-react"
import { SubmitEvent, useState } from "react"

const title = "Laporan Stok Sisa"

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: stockRemaining.chooseLocation().url
    }
]

type Props = {
    pagination: Pagination<ProductStock>
    locations: Location[]
    location: Location
    categoryOptions: {
        label: string
        value: string
    }[]
}

export default ({ pagination, categoryOptions, location, locations }: Props) => {
    const query = useQuery()
    const search = query.search || ''
    const product_category_id = query.product_category_id || 'all'
    const startIndex = (pagination.current_page - 1) * pagination.per_page

    const [dSearch, setDSearch] = useState('');

    const locationDialogOnSearch = (e: SubmitEvent<HTMLFormElement>) => {
        e.preventDefault()

        const form = e.currentTarget
        const data = new FormData(form)
        const val = data.get('search')?.toString() || ''
        setDSearch(val)
    }

    return (
        <AppLayout breadcrumbs={[...breadcrumbs, {
            title: capitalize(location.name),
            href: stockRemaining.report(location.id).url
        }]}>
            <Head title={title} />
            <div className="grid lg:flex gap-2">
                <Dialog>
                    <DialogTrigger asChild>
                        <Button><MapPinPen /> Ganti Lokasi</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <LocationChooserContainer locations={locations} search={dSearch} onSearch={locationDialogOnSearch}>
                            <DialogHeader>
                                <DialogTitle>Ganti Lokasi</DialogTitle>
                                <div className="py-4">
                                    <LocationChooserFilter />
                                </div>
                            </DialogHeader>
                            <div className="max-h-[50vh] overflow-y-auto">
                                <LocationChooserList />
                            </div>
                        </LocationChooserContainer>
                    </DialogContent>
                </Dialog>
                <Button variant={'outline'}><FileDown /> Export</Button>
            </div>
            <Separator className="my-4" />
            <Card>
                <CardHeader>
                    <Form method="GET" className="grid lg:flex gap-2">
                        <Combobox
                            items={categoryOptions}
                            name="product_category_id"
                            defaultValue={categoryOptions.find(el => el.value == product_category_id)}
                        >
                            <ComboboxInput placeholder="Pilih Kategori" className={'w-full'} />
                            <ComboboxContent>
                                <ComboboxEmpty>Tidak ditemukan</ComboboxEmpty>
                                <ComboboxList>
                                    {(el) => (
                                        <ComboboxItem key={el.value} value={el}>
                                            {el.label}
                                        </ComboboxItem>
                                    )}
                                </ComboboxList>
                            </ComboboxContent>
                        </Combobox>
                        <Input placeholder="Cari..." name="search" defaultValue={search} />
                        <input type="hidden" name="page" value={1} />
                        <Button variant={"secondary"}><Search /> Cari</Button>
                    </Form>
                </CardHeader>
                <CardContent>
                    <Table className='stripped'>
                        <TableHeader>
                            <TableRow>
                                <TableHead>No.</TableHead>
                                <TableHead>Nama</TableHead>
                                <TableHead>Kategori</TableHead>
                                <TableHead>Stok</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {pagination.data.map((report: ProductStock, index: number) => (
                                <TableRow key={report.id ?? index}>
                                    <TableCell>{startIndex + index + 1}.</TableCell>
                                    <TableCell>{report.product.name}</TableCell>
                                    <TableCell>
                                        <Badge variant={'secondary'}>
                                            {report.product.product_category?.name}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{report.stock}</TableCell>
                                </TableRow>
                            ))}

                            {!pagination.data.length && (
                                <TableRow>
                                    <TableCell colSpan={4} className="text-center py-4 text-muted-foreground">
                                        Data tidak ditemukan
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                    <TablePagination pagination={pagination} />
                </CardContent>
            </Card>
        </AppLayout>
    )
}