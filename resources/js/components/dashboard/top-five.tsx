import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Card, CardContent } from '../ui/card';

export default () => {
    return (
        <>
            <h2 className="text-lg font-semibold">Top 5</h2>
            <Card>
                <CardContent>
                    <Tabs defaultValue='product'>
                        <TabsList>
                            <TabsTrigger value='product'>Produk</TabsTrigger>
                            <TabsTrigger value='category'>Kategori</TabsTrigger>
                        </TabsList>
                        <TabsContent value='product'>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>#</TableHead>
                                        <TableHead>Nama</TableHead>
                                        <TableHead>Kuantitas</TableHead>
                                        <TableHead>Total Penjualan</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow>
                                        <TableCell>1</TableCell>
                                        <TableCell>Item 1</TableCell>
                                        <TableCell>1</TableCell>
                                        <TableCell>Rp. *****</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>2</TableCell>
                                        <TableCell>Item 1</TableCell>
                                        <TableCell>1</TableCell>
                                        <TableCell>Rp. *****</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>3</TableCell>
                                        <TableCell>Item 1</TableCell>
                                        <TableCell>1</TableCell>
                                        <TableCell>Rp. *****</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>4</TableCell>
                                        <TableCell>Item 1</TableCell>
                                        <TableCell>1</TableCell>
                                        <TableCell>Rp. *****</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>5</TableCell>
                                        <TableCell>Item 1</TableCell>
                                        <TableCell>1</TableCell>
                                        <TableCell>Rp. *****</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </TabsContent>
                        <TabsContent value='category'>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>#</TableHead>
                                        <TableHead>Nama</TableHead>
                                        <TableHead>Kuantitas</TableHead>
                                        <TableHead>Total Penjualan</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow>
                                        <TableCell>1</TableCell>
                                        <TableCell>Item 1</TableCell>
                                        <TableCell>1</TableCell>
                                        <TableCell>Rp. *****</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>2</TableCell>
                                        <TableCell>Item 1</TableCell>
                                        <TableCell>1</TableCell>
                                        <TableCell>Rp. *****</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>3</TableCell>
                                        <TableCell>Item 1</TableCell>
                                        <TableCell>1</TableCell>
                                        <TableCell>Rp. *****</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>4</TableCell>
                                        <TableCell>Item 1</TableCell>
                                        <TableCell>1</TableCell>
                                        <TableCell>Rp. *****</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell>5</TableCell>
                                        <TableCell>Item 1</TableCell>
                                        <TableCell>1</TableCell>
                                        <TableCell>Rp. *****</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
        </>
    )
}