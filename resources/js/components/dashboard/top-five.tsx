import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Card, CardContent } from '../ui/card';
import { Top5Data } from '@/lib/model';

type Props = {
    top5: Top5Data;
};

export default ({ top5 }: Props) => {
    function formatCurrency(value: number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(value);
    }
    return (
        <>
            <h2 className="text-lg font-semibold">Top 5</h2>
            <Card>
                <CardContent>
                    <Tabs defaultValue="product">
                        <TabsList>
                            <TabsTrigger value="product">Produk</TabsTrigger>
                            <TabsTrigger value="category">Kategori</TabsTrigger>
                            <TabsTrigger value="location">Lokasi</TabsTrigger>
                        </TabsList>
                        <TabsContent value="product">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>No. </TableHead>
                                        <TableHead>Nama</TableHead>
                                        <TableHead className="text-center">
                                            Kuantitas
                                        </TableHead>
                                        <TableHead>Total Penjualan</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {top5.products.length > 0 ? (
                                        top5.products.map((item, index) => (
                                            <TableRow
                                                key={`${item.product_name}-${item.quantity}-${item.total_line_amount}`}
                                            >
                                                <TableCell>
                                                    {index + 1}
                                                </TableCell>
                                                <TableCell>
                                                    {item.product_name}
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    {item.quantity}
                                                </TableCell>
                                                <TableCell>
                                                    {formatCurrency(
                                                        item.total_line_amount,
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell
                                                colSpan={4}
                                                className="text-center text-muted-foreground"
                                            >
                                                Data tidak ditemukan
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </TabsContent>
                        <TabsContent value="category">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>No. </TableHead>
                                        <TableHead>Nama</TableHead>
                                        <TableHead className="text-center">
                                            Kuantitas
                                        </TableHead>
                                        <TableHead>Total Penjualan</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {top5.categories.length > 0 ? (
                                        top5.categories.map((item, index) => (
                                            <TableRow
                                                key={`${item.product_category_name}-${item.quantity}-${item.total_line_amount}`}
                                            >
                                                <TableCell>
                                                    {index + 1}
                                                </TableCell>
                                                <TableCell>
                                                    {item.product_category_name}
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    {item.quantity}
                                                </TableCell>
                                                <TableCell>
                                                    {formatCurrency(
                                                        item.total_line_amount,
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell
                                                colSpan={4}
                                                className="text-center text-muted-foreground"
                                            >
                                                Data tidak ditemukan
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </TabsContent>
                        <TabsContent value="location">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>No.</TableHead>
                                        <TableHead>Lokasi</TableHead>
                                        <TableHead>Total Penjualan</TableHead>
                                    </TableRow>
                                </TableHeader>

                                <TableBody>
                                    {top5.locations.length > 0 ? (
                                        top5.locations.map((item, index) => (
                                            <TableRow
                                                key={`${item.location_name}-${item.net_sales_after_tax}`}
                                            >
                                                <TableCell>
                                                    {index + 1}
                                                </TableCell>

                                                <TableCell>
                                                    {item.location_name}
                                                </TableCell>

                                                <TableCell>
                                                    {formatCurrency(
                                                        item.net_sales_after_tax,
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell
                                                colSpan={3}
                                                className="text-center text-muted-foreground"
                                            >
                                                Data tidak ditemukan
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
        </>
    );
};
