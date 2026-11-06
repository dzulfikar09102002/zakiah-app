import { useForm } from '@inertiajs/react';
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
import menuGroups from '@/routes/menu-groups';
import menuSubmenus from '@/routes/menu-submenus';
import { IconSelect } from './icon-select';
import type { MenuGroupRow, MenuSubmenuRow } from './types';

type Props =
    | {
          kind: 'submenu';
          open: boolean;
          item: MenuSubmenuRow | null;
          routeOptions: string[];
          onClose: () => void;
      }
    | {
          kind: 'group';
          open: boolean;
          item: MenuGroupRow | null;
          routeOptions?: undefined;
          onClose: () => void;
      };

/** Form sub menu (nama, ikon, halaman) dan group (nama, ikon). */
export default function ItemDialog(props: Props) {
    const { kind, open, item, onClose } = props;
    const routes = kind === 'submenu' ? menuSubmenus : menuGroups;
    const label = kind === 'submenu' ? 'Sub Menu' : 'Group';

    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm({
            name: '',
            icon: null as string | null,
            route_name: '',
            is_active: true,
        });

    useEffect(() => {
        if (!open) {
            return;
        }

        clearErrors();

        if (item) {
            setData({
                name: item.name,
                icon: item.icon,
                route_name: 'route_name' in item ? item.route_name : '',
                is_active: item.is_active,
            });
        } else {
            reset();
        }
        // Hanya saat dialog dibuka.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, item?.id]);

    const submit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    `${label} ${item ? 'diperbarui' : 'ditambahkan'}`,
                );
                onClose();
            },
            onError: () => toast.error(`Periksa kembali isian ${label}`),
        };

        if (item) {
            put(routes.update(item.id).url, options);
        } else {
            post(routes.store().url, options);
        }
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(next) => !next && !processing && onClose()}
        >
            <DialogContent asChild>
                <form onSubmit={submit}>
                    <DialogCancel />
                    <DialogHeader>
                        <DialogTitle>
                            {item ? `Edit ${label}` : `${label} Baru`}
                        </DialogTitle>
                        <DialogDescription>
                            Berlaku untuk semua entity.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <Field>
                            <FieldLabel htmlFor="item-name">Nama</FieldLabel>
                            <Input
                                id="item-name"
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
                            <FieldLabel htmlFor="item-icon">Ikon</FieldLabel>
                            <IconSelect
                                id="item-icon"
                                value={data.icon}
                                disabled={processing}
                                onChange={(icon) => setData('icon', icon)}
                            />
                        </Field>
                        {props.kind === 'submenu' && (
                            <Field>
                                <FieldLabel htmlFor="item-route">
                                    Halaman
                                </FieldLabel>
                                <Select
                                    value={data.route_name}
                                    onValueChange={(value) =>
                                        setData('route_name', value)
                                    }
                                    disabled={processing}
                                >
                                    <SelectTrigger
                                        id="item-route"
                                        className="h-11 md:h-9"
                                    >
                                        <SelectValue placeholder="Pilih halaman" />
                                    </SelectTrigger>
                                    <SelectContent className="max-h-72">
                                        {props.routeOptions.map((route) => (
                                            <SelectItem
                                                key={route}
                                                value={route}
                                            >
                                                {route}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldDescription>
                                    Aksi Hak Akses sub menu (Tambah, Ubah, ...)
                                    diatur lewat SQL oleh developer.
                                </FieldDescription>
                                <FieldError>{errors.route_name}</FieldError>
                            </Field>
                        )}
                        <Field orientation="horizontal">
                            <Switch
                                id="item-active"
                                checked={data.is_active}
                                disabled={processing}
                                onCheckedChange={(checked) =>
                                    setData('is_active', checked)
                                }
                            />
                            <FieldLabel htmlFor="item-active">
                                Tampil di sidebar
                            </FieldLabel>
                        </Field>
                    </div>

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
