import { useForm } from '@inertiajs/react';
import type { SubmitEventHandler } from 'react';
import { useEffect, useMemo } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import { FieldError } from '@/components/ui/field';
import { Spinner } from '@/components/ui/spinner';
import accessRoles from '@/routes/access-roles';
import type { AccessRoleRow, MatrixHeader } from './types';

type Row = MatrixHeader['rows'][number];

type Props = {
    role: AccessRoleRow | null;
    matrix: MatrixHeader[];
    onClose: () => void;
};

/**
 * Hak Akses satu role: matriks centang (baris = sub menu, isi = aksi).
 * "Lihat" adalah syarat aksi lain: mencentang aksi apa pun ikut mencentang Lihat,
 * dan melepas Lihat melepas semua aksi di baris itu.
 */
export default function PermissionDialog({ role, matrix, onClose }: Props) {
    const { data, setData, put, processing, errors, clearErrors } = useForm({
        action_ids: [] as number[],
    });

    useEffect(() => {
        if (role) {
            clearErrors();
            setData('action_ids', role.action_ids);
        }
        // Hanya saat role yang dibuka berganti.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [role?.id]);

    const selected = useMemo(() => new Set(data.action_ids), [data.action_ids]);
    const allIds = useMemo(
        () =>
            matrix.flatMap((header) =>
                header.rows.flatMap((row) =>
                    row.actions.map((action) => action.id),
                ),
            ),
        [matrix],
    );

    const apply = (add: number[], remove: number[] = []) => {
        const next = new Set(selected);
        remove.forEach((id) => next.delete(id));
        add.forEach((id) => next.add(id));
        setData('action_ids', [...next]);
    };

    const rowIds = (row: Row) => row.actions.map((action) => action.id);

    const toggleAction = (row: Row, actionId: number, checked: boolean) => {
        const view = row.actions.find((action) => action.code === 'view');
        const isView = view?.id === actionId;

        if (checked) {
            apply(view && !isView ? [actionId, view.id] : [actionId]);
        } else {
            apply([], isView ? rowIds(row) : [actionId]);
        }
    };

    const state = (ids: number[]): boolean | 'indeterminate' =>
        ids.length > 0 && ids.every((id) => selected.has(id))
            ? true
            : ids.some((id) => selected.has(id))
              ? 'indeterminate'
              : false;

    const submit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        if (!role) {
            return;
        }

        put(accessRoles.permissions(role.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Hak Akses disimpan');
                onClose();
            },
            onError: () => toast.error('Hak Akses gagal disimpan'),
        });
    };

    return (
        <Dialog
            open={role !== null}
            onOpenChange={(open) => !open && !processing && onClose()}
        >
            <DialogContent
                asChild
                className="max-h-[92dvh] overflow-y-auto sm:max-w-3xl"
            >
                <form onSubmit={submit}>
                    <DialogCancel />
                    <DialogHeader>
                        <DialogTitle className="break-words">
                            Hak Akses: {role?.name}
                        </DialogTitle>
                        <DialogDescription>
                            {role?.default_for
                                ? `Dipakai otomatis oleh karyawan dengan role utama ${role.default_for}. `
                                : ''}
                            Centang menu dan aksi yang boleh dibuka di
                            backoffice.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <p className="text-sm text-muted-foreground">
                            {data.action_ids.length} dari {allIds.length} aksi
                            dicentang
                        </p>
                        <div className="flex gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                className="h-11 md:h-8"
                                disabled={processing}
                                onClick={() => setData('action_ids', allIds)}
                            >
                                Pilih Semua
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                className="h-11 md:h-8"
                                disabled={processing}
                                onClick={() => setData('action_ids', [])}
                            >
                                Kosongkan
                            </Button>
                        </div>
                    </div>
                    <FieldError>{errors.action_ids}</FieldError>

                    <div className="space-y-3">
                        {matrix.map((header) => {
                            const headerIds = header.rows.flatMap(rowIds);

                            return (
                                <section
                                    key={header.id}
                                    className="overflow-hidden rounded-lg border"
                                >
                                    <label className="flex min-h-11 cursor-pointer items-center gap-3 bg-muted/40 px-3 py-2">
                                        <Checkbox
                                            checked={state(headerIds)}
                                            disabled={processing}
                                            onCheckedChange={(checked) =>
                                                checked === true
                                                    ? apply(headerIds)
                                                    : apply([], headerIds)
                                            }
                                        />
                                        <span className="font-semibold">
                                            {header.name}
                                        </span>
                                    </label>

                                    <ul className="divide-y">
                                        {header.rows.map((row) => (
                                            <li
                                                key={row.submenu_id}
                                                className="flex flex-col gap-2 px-3 py-2 md:flex-row md:items-center"
                                            >
                                                <label className="flex min-h-11 cursor-pointer items-center gap-3 md:w-56 md:shrink-0">
                                                    <Checkbox
                                                        checked={state(
                                                            rowIds(row),
                                                        )}
                                                        disabled={processing}
                                                        onCheckedChange={(
                                                            checked,
                                                        ) =>
                                                            checked === true
                                                                ? apply(
                                                                      rowIds(
                                                                          row,
                                                                      ),
                                                                  )
                                                                : apply(
                                                                      [],
                                                                      rowIds(
                                                                          row,
                                                                      ),
                                                                  )
                                                        }
                                                    />
                                                    <span className="text-sm">
                                                        {row.group && (
                                                            <span className="text-muted-foreground">
                                                                {row.group}{' '}
                                                                ›{' '}
                                                            </span>
                                                        )}
                                                        {row.name}
                                                    </span>
                                                </label>

                                                <div className="flex flex-wrap gap-x-4 gap-y-1 pl-7 md:pl-0">
                                                    {row.actions.map(
                                                        (action) => (
                                                            <label
                                                                key={action.id}
                                                                className="flex min-h-11 cursor-pointer items-center gap-2 text-sm md:min-h-8"
                                                            >
                                                                <Checkbox
                                                                    checked={selected.has(
                                                                        action.id,
                                                                    )}
                                                                    disabled={
                                                                        processing
                                                                    }
                                                                    onCheckedChange={(
                                                                        checked,
                                                                    ) =>
                                                                        toggleAction(
                                                                            row,
                                                                            action.id,
                                                                            checked ===
                                                                                true,
                                                                        )
                                                                    }
                                                                />
                                                                {action.name}
                                                            </label>
                                                        ),
                                                    )}
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                </section>
                            );
                        })}
                    </div>

                    <p className="mt-2 text-xs text-muted-foreground">
                        Pemilik Usaha selalu bisa membuka semua menu.
                    </p>

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
                            Simpan Hak Akses
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
