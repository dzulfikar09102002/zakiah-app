import { Form, Head, router } from '@inertiajs/react';
import { ChevronRight, Plus, Search, Trash2 } from 'lucide-react';
import { Fragment, useState } from 'react';
import { toast } from 'sonner';
import Modal from '@/components/legal-entity-locations/modal';
import type { LegalEntityOption } from '@/components/legal-entity-locations/modal';
import TablePagination from '@/components/table-pagination';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useQuery } from '@/hooks/use-query';
import AppLayout from '@/layouts/app-layout';
import type {
    LegalEntityLocation,
    LocationWithLegalEntities,
    Option,
    Pagination,
} from '@/lib/model';
import { cn } from '@/lib/utils';
import legalEntityLocations from '@/routes/legal-entity-locations';
import type { BreadcrumbItem } from '@/types';

const title = 'CV per Toko';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: legalEntityLocations.index().url,
    },
];

const kindLabels: Record<string, string> = {
    main_office: 'Office',
    outlet: 'Outlet',
    warehouse: 'Gudang',
};

type Props = {
    pagination: Pagination<LocationWithLegalEntities>;
    legalEntityOptions: LegalEntityOption[];
    legalTypes: Option[];
};

type DetailProps = {
    location: LocationWithLegalEntities;
    legalTypes: Option[];
    onRemove: (item: LegalEntityLocation) => void;
};

const Detail = ({ location, legalTypes, onRemove }: DetailProps) => {
    const legalTypeLabel = (value: string) =>
        legalTypes.find((type) => type.value === value)?.label ?? value;

    if (!location.legal_entity_locations.length) {
        return (
            <div className="py-4 text-center text-sm text-muted-foreground">
                Belum ada CV di lokasi ini. Klik tombol Tambah untuk
                menambahkan.
            </div>
        );
    }

    return (
        <Table>
            <TableHeader>
                <TableRow className="hover:bg-transparent">
                    <TableHead className="w-12">No.</TableHead>
                    <TableHead>Nama CV</TableHead>
                    <TableHead>Inisial</TableHead>
                    <TableHead>Jenis</TableHead>
                    <TableHead>NPWP</TableHead>
                    <TableHead>PKP</TableHead>
                    <TableHead className="text-center">Aksi</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {location.legal_entity_locations.map((item, index) => (
                    <TableRow key={item.id}>
                        <TableCell>{index + 1}.</TableCell>
                        <TableCell>{item.legal_entity.name}</TableCell>
                        <TableCell>{item.legal_entity.initial}</TableCell>
                        <TableCell>
                            {legalTypeLabel(item.legal_entity.legal_type)}
                        </TableCell>
                        <TableCell>{item.legal_entity.npwp ?? '-'}</TableCell>
                        <TableCell>
                            <Badge
                                variant={
                                    item.legal_entity.is_pkp
                                        ? 'default'
                                        : 'outline'
                                }
                            >
                                {item.legal_entity.is_pkp ? 'PKP' : 'Non PKP'}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <div className="flex justify-center gap-2">
                                <Tooltip>
                                    <TooltipTrigger asChild>
                                        <Button
                                            size="icon"
                                            variant="destructive"
                                            onClick={() => onRemove(item)}
                                        >
                                            <Trash2 />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>Hapus</TooltipContent>
                                </Tooltip>
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
};

export default ({ pagination, legalEntityOptions, legalTypes }: Props) => {
    const [selectedLocation, setSelectedLocation] =
        useState<LocationWithLegalEntities>();
    const [removing, setRemoving] = useState<LegalEntityLocation>();
    const [processing, setProcessing] = useState(false);
    const [expanded, setExpanded] = useState<number[]>([]);

    const search = useQuery().search || '';
    const startIndex = (pagination.current_page - 1) * pagination.per_page;

    const toggle = (id: number) =>
        setExpanded((prev) =>
            prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id],
        );

    const expand = (id: number) =>
        setExpanded((prev) => (prev.includes(id) ? prev : [...prev, id]));

    const remove = () => {
        if (!removing) return;

        router.delete(legalEntityLocations.destroy(removing.id).url, {
            only: ['pagination'],
            preserveState: true,
            preserveScroll: true,
            onBefore: () => setProcessing(true),
            onSuccess: () => toast.success('CV berhasil dihapus dari lokasi'),
            onError: () => toast.error('Gagal menghapus CV dari lokasi'),
            onFinish: () => {
                setProcessing(false);
                setRemoving(undefined);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <Modal
                location={selectedLocation}
                legalEntityOptions={legalEntityOptions}
                onClose={() => setSelectedLocation(undefined)}
            />

            <AlertDialog
                open={!!removing}
                onOpenChange={() => processing || setRemoving(undefined)}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Hapus CV dari Lokasi
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            Hapus {removing?.legal_entity.name} dari lokasi ini?
                            Data CV-nya sendiri tidak ikut terhapus.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel disabled={processing}>
                            Batal
                        </AlertDialogCancel>
                        <Button
                            variant="destructive"
                            disabled={processing}
                            onClick={remove}
                        >
                            <Spinner className={processing ? '' : 'hidden'} />
                            Ya
                        </Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <Card className="border-0 bg-background p-0 lg:border lg:bg-card lg:py-6">
                <CardHeader className="p-0 lg:px-6">
                    <Form method="GET">
                        <div className="grid gap-2 lg:flex">
                            <input type="hidden" name="page" value={1} />
                            <Input
                                defaultValue={search}
                                name="search"
                                placeholder="Cari lokasi atau CV..."
                            />
                            <Button variant={'secondary'}>
                                <Search /> Cari
                            </Button>
                        </div>
                    </Form>
                </CardHeader>
                <CardContent className="border-t p-0 lg:border-0 lg:px-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-10" />
                                <TableHead>No.</TableHead>
                                <TableHead>Lokasi</TableHead>
                                <TableHead>Jenis</TableHead>
                                <TableHead>Jumlah CV</TableHead>
                                <TableHead className="text-center">
                                    Aksi
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {pagination.data.map((location, index) => {
                                const isOpen = expanded.includes(location.id);

                                return (
                                    <Fragment key={location.id}>
                                        <TableRow
                                            className="cursor-pointer"
                                            onClick={() => toggle(location.id)}
                                        >
                                            <TableCell>
                                                <ChevronRight
                                                    className={cn(
                                                        'size-4 transition-transform',
                                                        isOpen && 'rotate-90',
                                                    )}
                                                />
                                            </TableCell>
                                            <TableCell>
                                                {startIndex + index + 1}.
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-medium">
                                                    {location.name}
                                                </div>
                                                {location.city && (
                                                    <div className="text-xs text-muted-foreground">
                                                        {location.city}
                                                    </div>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {kindLabels[location.kind] ??
                                                    location.kind}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="secondary">
                                                    {
                                                        location
                                                            .legal_entity_locations
                                                            .length
                                                    }{' '}
                                                    CV
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <Button
                                                    size="sm"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        expand(location.id);
                                                        setSelectedLocation(
                                                            location,
                                                        );
                                                    }}
                                                >
                                                    <Plus />
                                                </Button>
                                            </TableCell>
                                        </TableRow>

                                        {isOpen && (
                                            <TableRow className="bg-muted/40 hover:bg-muted/40">
                                                <TableCell
                                                    colSpan={6}
                                                    className="p-0"
                                                >
                                                    <div className="py-2 pr-4 pl-12">
                                                        <Detail
                                                            location={location}
                                                            legalTypes={
                                                                legalTypes
                                                            }
                                                            onRemove={
                                                                setRemoving
                                                            }
                                                        />
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </Fragment>
                                );
                            })}

                            {!pagination.data.length && (
                                <TableRow>
                                    <TableCell
                                        colSpan={6}
                                        className="py-4 text-center text-muted-foreground"
                                    >
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
    );
};
