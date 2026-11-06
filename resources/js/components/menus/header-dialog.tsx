import { useForm } from '@inertiajs/react';
import { ArrowDown, ArrowUp, Plus, X } from 'lucide-react';
import type { SubmitEventHandler } from 'react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogCancel,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Field,
    FieldDescription,
    FieldError,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import menuHeaders from '@/routes/menu-headers';
import { IconSelect } from './icon-select';
import type { MenuGroupRow, MenuHeaderRow, MenuSubmenuRow } from './types';

/** Select tidak menerima string kosong sebagai nilai. */
const NONE = 'none';

type DetailRow = { submenu: string; group: string };

type Props = {
    open: boolean;
    header: MenuHeaderRow | null;
    submenus: MenuSubmenuRow[];
    groups: MenuGroupRow[];
    routeOptions: string[];
    onClose: () => void;
};

/**
 * Header menu beserta isinya: sub menu, opsional di dalam group. Urutan baris = urutan
 * tampil; group tampil di posisi sub menu pertamanya.
 */
export default function HeaderDialog({
    open,
    header,
    submenus,
    groups,
    routeOptions,
    onClose,
}: Props) {
    const {
        data,
        setData,
        post,
        put,
        transform,
        processing,
        errors,
        reset,
        clearErrors,
    } = useForm({
        name: '',
        section: 'main' as 'main' | 'setting',
        icon: null as string | null,
        route_name: NONE,
        seq: 10,
        is_active: true,
        rows: [] as DetailRow[],
    });

    useEffect(() => {
        if (!open) {
            return;
        }

        clearErrors();

        if (header) {
            setData({
                name: header.name,
                section: header.section,
                icon: header.icon,
                route_name: header.route_name ?? NONE,
                seq: header.seq,
                is_active: header.is_active,
                rows: [...header.details]
                    .sort((a, b) => a.seq - b.seq)
                    .map((detail) => ({
                        submenu: String(detail.menu_submenu_id),
                        group: detail.menu_group_id
                            ? String(detail.menu_group_id)
                            : NONE,
                    })),
            });
        } else {
            reset();
        }
        // Hanya saat dialog dibuka.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, header?.id]);

    const direct = data.route_name !== NONE;

    const updateRow = (index: number, row: Partial<DetailRow>) =>
        setData(
            'rows',
            data.rows.map((current, i) =>
                i === index ? { ...current, ...row } : current,
            ),
        );

    const move = (index: number, offset: number) => {
        const rows = [...data.rows];
        const [row] = rows.splice(index, 1);
        rows.splice(index + offset, 0, row);
        setData('rows', rows);
    };

    const submit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        if (!direct && data.rows.some((row) => row.submenu === '')) {
            toast.error('Pilih sub menu di setiap baris');

            return;
        }

        transform((form) => ({
            name: form.name,
            section: form.section,
            icon: form.icon,
            route_name: form.route_name === NONE ? null : form.route_name,
            seq: Number(form.seq),
            is_active: form.is_active,
            details: direct
                ? []
                : form.rows.map((row, index) => ({
                      menu_submenu_id: Number(row.submenu),
                      menu_group_id:
                          row.group === NONE ? null : Number(row.group),
                      seq: (index + 1) * 10,
                  })),
        }));

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    header ? 'Header diperbarui' : 'Header ditambahkan',
                );
                onClose();
            },
            onError: () => toast.error('Periksa kembali isian header'),
        };

        if (header) {
            put(menuHeaders.update(header.id).url, options);
        } else {
            post(menuHeaders.store().url, options);
        }
    };

    const detailError = Object.entries(errors).find(([key]) =>
        key.startsWith('details'),
    )?.[1];

    return (
        <Dialog
            open={open}
            onOpenChange={(next) => !next && !processing && onClose()}
        >
            <DialogContent
                asChild
                className="max-h-[92dvh] overflow-y-auto sm:max-w-2xl"
            >
                <form onSubmit={submit}>
                    <DialogCancel />
                    <DialogHeader>
                        <DialogTitle>
                            {header ? 'Edit Header Menu' : 'Header Menu Baru'}
                        </DialogTitle>
                        <DialogDescription>
                            Berlaku untuk semua entity.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel htmlFor="header-name">Nama</FieldLabel>
                            <Input
                                id="header-name"
                                value={data.name}
                                readOnly={processing}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                className="h-11 text-base md:h-9 md:text-sm"
                            />
                            <FieldError>{errors.name}</FieldError>
                        </Field>
                        <Field>
                            <FieldLabel htmlFor="header-icon">Ikon</FieldLabel>
                            <IconSelect
                                id="header-icon"
                                value={data.icon}
                                disabled={processing}
                                onChange={(icon) => setData('icon', icon)}
                            />
                        </Field>
                        <Field>
                            <FieldLabel htmlFor="header-section">
                                Bagian Sidebar
                            </FieldLabel>
                            <Select
                                value={data.section}
                                onValueChange={(value) =>
                                    setData(
                                        'section',
                                        value as 'main' | 'setting',
                                    )
                                }
                                disabled={processing}
                            >
                                <SelectTrigger
                                    id="header-section"
                                    className="h-11 md:h-9"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="main">
                                        Menu utama
                                    </SelectItem>
                                    <SelectItem value="setting">
                                        Pengaturan (bawah)
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </Field>
                        <Field>
                            <FieldLabel htmlFor="header-seq">Urutan</FieldLabel>
                            <Input
                                id="header-seq"
                                type="number"
                                inputMode="numeric"
                                min={0}
                                value={data.seq}
                                readOnly={processing}
                                onChange={(event) =>
                                    setData('seq', Number(event.target.value))
                                }
                                className="h-11 text-base md:h-9 md:text-sm"
                            />
                            <FieldError>{errors.seq}</FieldError>
                        </Field>
                        <Field className="sm:col-span-2">
                            <FieldLabel htmlFor="header-route">
                                Langsung Membuka Halaman
                            </FieldLabel>
                            <Select
                                value={data.route_name}
                                onValueChange={(value) =>
                                    setData('route_name', value)
                                }
                                disabled={processing}
                            >
                                <SelectTrigger
                                    id="header-route"
                                    className="h-11 md:h-9"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent className="max-h-72">
                                    <SelectItem value={NONE}>
                                        Tidak, berisi sub menu
                                    </SelectItem>
                                    {routeOptions.map((route) => (
                                        <SelectItem key={route} value={route}>
                                            {route}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <FieldDescription>
                                Isi bila header ini cukup satu link, seperti
                                Dashboard.
                            </FieldDescription>
                            <FieldError>{errors.route_name}</FieldError>
                        </Field>
                        <Field orientation="horizontal">
                            <Switch
                                id="header-active"
                                checked={data.is_active}
                                disabled={processing}
                                onCheckedChange={(checked) =>
                                    setData('is_active', checked)
                                }
                            />
                            <FieldLabel htmlFor="header-active">
                                Tampil di sidebar
                            </FieldLabel>
                        </Field>
                    </div>

                    {!direct && (
                        <Field className="mt-2">
                            <FieldLabel>Sub Menu</FieldLabel>
                            <FieldDescription>
                                Urutan baris = urutan di sidebar. Sub menu
                                dengan group yang sama dikumpulkan di bawah
                                group itu.
                            </FieldDescription>

                            <div className="overflow-hidden rounded-lg border">
                                {data.rows.length === 0 && (
                                    <p className="p-6 text-center text-sm text-muted-foreground">
                                        Belum ada sub menu
                                    </p>
                                )}
                                {data.rows.map((row, index) => (
                                    <div
                                        key={index}
                                        className="grid grid-cols-[1fr_auto] gap-2 border-b px-3 py-2 last:border-b-0 sm:grid-cols-[1fr_1fr_auto]"
                                    >
                                        <Select
                                            value={row.submenu}
                                            onValueChange={(value) =>
                                                updateRow(index, {
                                                    submenu: value,
                                                })
                                            }
                                            disabled={processing}
                                        >
                                            <SelectTrigger
                                                aria-label={`Sub menu baris ${index + 1}`}
                                                className="h-11 md:h-9"
                                            >
                                                <SelectValue placeholder="Pilih sub menu" />
                                            </SelectTrigger>
                                            <SelectContent className="max-h-72">
                                                {submenus.map((submenu) => (
                                                    <SelectItem
                                                        key={submenu.id}
                                                        value={String(
                                                            submenu.id,
                                                        )}
                                                    >
                                                        {submenu.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>

                                        <div className="flex gap-1 sm:order-last">
                                            <Button
                                                type="button"
                                                size="icon"
                                                variant="ghost"
                                                className="size-11 md:size-9"
                                                aria-label="Naikkan"
                                                disabled={
                                                    processing || index === 0
                                                }
                                                onClick={() => move(index, -1)}
                                            >
                                                <ArrowUp />
                                            </Button>
                                            <Button
                                                type="button"
                                                size="icon"
                                                variant="ghost"
                                                className="size-11 md:size-9"
                                                aria-label="Turunkan"
                                                disabled={
                                                    processing ||
                                                    index ===
                                                        data.rows.length - 1
                                                }
                                                onClick={() => move(index, 1)}
                                            >
                                                <ArrowDown />
                                            </Button>
                                            <Button
                                                type="button"
                                                size="icon"
                                                variant="outline"
                                                className="size-11 text-destructive md:size-9"
                                                aria-label={`Hapus baris ${index + 1}`}
                                                disabled={processing}
                                                onClick={() =>
                                                    setData(
                                                        'rows',
                                                        data.rows.filter(
                                                            (_, i) =>
                                                                i !== index,
                                                        ),
                                                    )
                                                }
                                            >
                                                <X />
                                            </Button>
                                        </div>

                                        <Select
                                            value={row.group}
                                            onValueChange={(value) =>
                                                updateRow(index, {
                                                    group: value,
                                                })
                                            }
                                            disabled={processing}
                                        >
                                            <SelectTrigger
                                                aria-label={`Group baris ${index + 1}`}
                                                className="h-11 md:h-9"
                                            >
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent className="max-h-72">
                                                <SelectItem value={NONE}>
                                                    Tanpa group
                                                </SelectItem>
                                                {groups.map((group) => (
                                                    <SelectItem
                                                        key={group.id}
                                                        value={String(group.id)}
                                                    >
                                                        {group.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                ))}
                            </div>

                            <Button
                                type="button"
                                variant="secondary"
                                className="h-11 w-full md:h-9 md:w-auto"
                                disabled={processing}
                                onClick={() =>
                                    setData('rows', [
                                        ...data.rows,
                                        { submenu: '', group: NONE },
                                    ])
                                }
                            >
                                <Plus /> Tambah Sub Menu
                            </Button>
                            <FieldError>{detailError}</FieldError>
                        </Field>
                    )}

                    <DialogFooter className="mt-4">
                        <DialogClose asChild disabled={processing}>
                            <Button
                                type="button"
                                variant="outline"
                                className="h-11 md:h-9"
                            >
                                Batal
                            </Button>
                        </DialogClose>
                        <Button
                            type="submit"
                            disabled={processing}
                            className="h-11 md:h-9"
                        >
                            {processing && <Spinner />}
                            Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
