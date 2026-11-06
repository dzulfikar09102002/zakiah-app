import { useForm } from '@inertiajs/react';
import { Plus, X } from 'lucide-react';
import type { SubmitEventHandler } from 'react';
import { useEffect, useState } from 'react';
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
    FieldSet,
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
import { cn } from '@/lib/utils';
import employees from '@/routes/employees';
import type {
    AccessRoleOption,
    EmployeeRow,
    LocationOption,
    RoleOption,
} from './types';

export type ModalState = {
    isOpen: boolean;
    dataId: number | undefined;
};

/** Satu baris form: satu toko dan satu role, seperti form karyawan lama. */
type AssignmentRow = {
    location_id: string;
    role_id: string;
};

type Props = {
    modalState: ModalState;
    tableData: EmployeeRow[];
    roleOptions: RoleOption[];
    accessRoleOptions: AccessRoleOption[] | null;
    locations: LocationOption[];
    onModalSuccess: () => void;
    onModalClose: () => void;
};

/** Nilai pilihan "bawaan" di dropdown Hak Akses (Select tidak menerima string kosong). */
const DEFAULT_ACCESS = 'default';

const emptyRow = (): AssignmentRow => ({ location_id: '', role_id: '' });

/**
 * Data kerja karyawan: nama dan penugasan. Orang yang jadi Kasir sekaligus Sales di
 * satu toko cukup diisi dua baris dengan toko yang sama. Email dan password diatur di
 * menu Akun Login.
 */
export default function EmployeeModal({
    modalState,
    tableData,
    roleOptions,
    accessRoleOptions,
    locations,
    onModalSuccess,
    onModalClose,
}: Props) {
    const editing = tableData.find((row) => row.id === modalState.dataId);
    const [showIncomplete, setShowIncomplete] = useState(false);

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
        first_name: '',
        last_name: '',
        access_role_id: DEFAULT_ACCESS,
        rows: [emptyRow()] as AssignmentRow[],
    });

    useEffect(() => {
        if (!modalState.isOpen) {
            return;
        }

        clearErrors();
        setShowIncomplete(false);

        if (editing) {
            setData({
                first_name: editing.first_name,
                last_name: editing.last_name,
                access_role_id: editing.access_role_id
                    ? String(editing.access_role_id)
                    : DEFAULT_ACCESS,
                rows: editing.assignments.flatMap((assignment) =>
                    assignment.roles.map((role) => ({
                        location_id: String(assignment.location_id),
                        role_id: String(role.role_id),
                    })),
                ),
            });
        } else {
            reset();
        }
        // Hanya saat dialog dibuka untuk karyawan lain.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [modalState.isOpen, modalState.dataId]);

    const updateRow = (index: number, row: Partial<AssignmentRow>) =>
        setData(
            'rows',
            data.rows.map((current, i) =>
                i === index ? { ...current, ...row } : current,
            ),
        );

    const isDuplicate = (index: number) => {
        const row = data.rows[index];

        return (
            row.location_id !== '' &&
            row.role_id !== '' &&
            data.rows.some(
                (other, i) =>
                    i < index &&
                    other.location_id === row.location_id &&
                    other.role_id === row.role_id,
            )
        );
    };

    const assignmentError =
        errors['assignments' as keyof typeof errors] ??
        Object.entries(errors).find(([key]) =>
            key.startsWith('assignments.'),
        )?.[1];

    const submit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        const incomplete = data.rows.some(
            (row) => !row.location_id || !row.role_id,
        );

        if (incomplete || data.rows.length === 0) {
            setShowIncomplete(true);
            toast.error('Lengkapi lokasi dan role di setiap baris');

            return;
        }

        if (data.rows.some((_, index) => isDuplicate(index))) {
            toast.error('Ada lokasi dan role yang sama lebih dari sekali');

            return;
        }

        transform((form) => ({
            first_name: form.first_name,
            last_name: form.last_name,
            access_role_id:
                form.access_role_id === DEFAULT_ACCESS
                    ? null
                    : Number(form.access_role_id),
            assignments: form.rows.map((row) => ({
                location_id: Number(row.location_id),
                role_id: Number(row.role_id),
            })),
        }));

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    editing ? 'Karyawan diperbarui' : 'Karyawan ditambahkan',
                );
                onModalSuccess();
                reset();
            },
            onError: () => toast.error('Periksa kembali isian karyawan'),
        };

        if (editing) {
            put(employees.update(editing.id).url, options);
        } else {
            post(employees.store().url, options);
        }
    };

    return (
        <Dialog
            open={modalState.isOpen}
            onOpenChange={(open) => !open && !processing && onModalClose()}
        >
            <DialogContent
                asChild
                className="max-h-[90dvh] overflow-y-auto sm:max-w-2xl"
            >
                <form onSubmit={submit}>
                    <DialogCancel />
                    <DialogHeader>
                        <DialogTitle>
                            {editing ? 'Edit Karyawan' : 'Karyawan Baru'}
                        </DialogTitle>
                        <DialogDescription>
                            Email dan password diatur di menu Akun Login.
                        </DialogDescription>
                    </DialogHeader>

                    {editing?.shares_account && (
                        <p className="rounded-lg bg-amber-500/12 p-3 text-sm text-amber-700 dark:text-amber-400">
                            Akun login karyawan ini juga dipakai karyawan lain
                            (data lama). Perubahan di sini hanya berlaku untuk
                            karyawan ini.
                        </p>
                    )}

                    <FieldSet className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel htmlFor="first_name">
                                Nama Depan
                            </FieldLabel>
                            <Input
                                id="first_name"
                                value={data.first_name}
                                readOnly={processing}
                                onChange={(event) =>
                                    setData('first_name', event.target.value)
                                }
                                className="h-11 text-base md:h-9 md:text-sm"
                            />
                            <FieldError>{errors.first_name}</FieldError>
                        </Field>
                        <Field>
                            <FieldLabel htmlFor="last_name">
                                Nama Belakang
                            </FieldLabel>
                            <Input
                                id="last_name"
                                value={data.last_name}
                                readOnly={processing}
                                placeholder="Opsional"
                                onChange={(event) =>
                                    setData('last_name', event.target.value)
                                }
                                className="h-11 text-base md:h-9 md:text-sm"
                            />
                            <FieldError>{errors.last_name}</FieldError>
                        </Field>

                        {accessRoleOptions && (
                            <Field className="sm:col-span-2">
                                <FieldLabel htmlFor="access_role_id">
                                    Role Backoffice
                                </FieldLabel>
                                <Select
                                    value={data.access_role_id}
                                    onValueChange={(value) =>
                                        setData('access_role_id', value)
                                    }
                                    disabled={processing}
                                >
                                    <SelectTrigger
                                        id="access_role_id"
                                        className="h-11 md:h-9"
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={DEFAULT_ACCESS}>
                                            Bawaan sesuai role utama
                                        </SelectItem>
                                        {accessRoleOptions.map((option) => (
                                            <SelectItem
                                                key={option.value}
                                                value={String(option.value)}
                                            >
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldDescription>
                                    Menentukan menu yang bisa dibuka di
                                    backoffice. Tidak memengaruhi kasir.
                                </FieldDescription>
                                <FieldError>{errors.access_role_id}</FieldError>
                            </Field>
                        )}
                    </FieldSet>

                    <Field className="mt-2">
                        <FieldLabel>Lokasi & Role</FieldLabel>
                        <FieldDescription>
                            Kasir sekaligus Sales di satu toko: isi dua baris
                            dengan lokasi yang sama.
                        </FieldDescription>

                        <div className="overflow-hidden rounded-lg border">
                            <div className="hidden grid-cols-[1fr_1fr_auto] gap-2 border-b bg-muted/30 px-3 py-2 text-sm font-medium text-muted-foreground sm:grid">
                                <span>Lokasi</span>
                                <span>Role</span>
                                <span className="w-9" />
                            </div>

                            {data.rows.length === 0 && (
                                <p className="p-6 text-center text-sm text-muted-foreground">
                                    Belum ada lokasi & role
                                </p>
                            )}

                            {data.rows.map((row, index) => {
                                const duplicate = isDuplicate(index);
                                const missing = (value: string) =>
                                    showIncomplete && value === '';

                                return (
                                    <div
                                        key={index}
                                        className="grid grid-cols-[1fr_auto] gap-2 border-b px-3 py-2 last:border-b-0 sm:grid-cols-[1fr_1fr_auto]"
                                    >
                                        <Select
                                            value={row.location_id}
                                            onValueChange={(value) =>
                                                updateRow(index, {
                                                    location_id: value,
                                                })
                                            }
                                            disabled={processing}
                                        >
                                            <SelectTrigger
                                                aria-label={`Lokasi baris ${index + 1}`}
                                                aria-invalid={
                                                    missing(row.location_id) ||
                                                    duplicate
                                                }
                                                className={cn(
                                                    'h-11 md:h-9',
                                                    (missing(row.location_id) ||
                                                        duplicate) &&
                                                        'border-destructive',
                                                )}
                                            >
                                                <SelectValue placeholder="Pilih Lokasi" />
                                            </SelectTrigger>
                                            <SelectContent className="max-h-60">
                                                {locations.map((location) => (
                                                    <SelectItem
                                                        key={location.id}
                                                        value={String(
                                                            location.id,
                                                        )}
                                                    >
                                                        {location.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>

                                        <Button
                                            type="button"
                                            size="icon"
                                            variant="outline"
                                            className="size-11 text-destructive sm:order-last md:size-9"
                                            aria-label={`Hapus baris ${index + 1}`}
                                            disabled={processing}
                                            onClick={() =>
                                                setData(
                                                    'rows',
                                                    data.rows.filter(
                                                        (_, i) => i !== index,
                                                    ),
                                                )
                                            }
                                        >
                                            <X />
                                        </Button>

                                        <Select
                                            value={row.role_id}
                                            onValueChange={(value) =>
                                                updateRow(index, {
                                                    role_id: value,
                                                })
                                            }
                                            disabled={processing}
                                        >
                                            <SelectTrigger
                                                aria-label={`Role baris ${index + 1}`}
                                                aria-invalid={
                                                    missing(row.role_id) ||
                                                    duplicate
                                                }
                                                className={cn(
                                                    'col-span-1 h-11 md:h-9',
                                                    (missing(row.role_id) ||
                                                        duplicate) &&
                                                        'border-destructive',
                                                )}
                                            >
                                                <SelectValue placeholder="Pilih Role" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {roleOptions.map((role) => (
                                                    <SelectItem
                                                        key={role.value}
                                                        value={String(
                                                            role.value,
                                                        )}
                                                    >
                                                        {role.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>

                                        {duplicate && (
                                            <p className="col-span-full text-xs text-destructive">
                                                Lokasi dan role ini sudah ada di
                                                baris lain.
                                            </p>
                                        )}
                                    </div>
                                );
                            })}
                        </div>

                        <Button
                            type="button"
                            variant="secondary"
                            className="h-11 w-full md:h-9 md:w-auto"
                            disabled={processing}
                            onClick={() =>
                                setData('rows', [...data.rows, emptyRow()])
                            }
                        >
                            <Plus /> Tambah Lokasi & Role
                        </Button>

                        <FieldError>{assignmentError}</FieldError>
                    </Field>

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
                            {editing ? 'Simpan' : 'Tambah'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
