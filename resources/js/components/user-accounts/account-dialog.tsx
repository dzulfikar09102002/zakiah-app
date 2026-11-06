import { Link, useForm } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import type { SubmitEventHandler } from 'react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import type {
    EmployeeOption,
    UserAccountRow,
} from '@/components/employees/types';
import { Button } from '@/components/ui/button';
import {
    Combobox,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList,
} from '@/components/ui/combobox';
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
import { Spinner } from '@/components/ui/spinner';
import employees from '@/routes/employees';
import userAccounts from '@/routes/user-accounts';

export type AccountDialogState =
    | { mode: 'closed' }
    | { mode: 'create' }
    | { mode: 'edit'; account: UserAccountRow };

type Props = {
    state: AccountDialogState;
    employeeOptions: EmployeeOption[];
    onClose: () => void;
};

/**
 * Buat akun: pilih karyawan yang belum punya akun, lalu isi email dan password.
 * Edit akun: ganti email dan/atau password.
 */
export default function AccountDialog({
    state,
    employeeOptions,
    onClose,
}: Props) {
    const [showPassword, setShowPassword] = useState(false);
    const editing = state.mode === 'edit' ? state.account : null;

    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm({
            employee_id: '',
            email: '',
            password: '',
            password_confirmation: '',
        });

    useEffect(() => {
        if (state.mode === 'closed') {
            return;
        }

        clearErrors();
        setShowPassword(false);
        reset();

        if (editing) {
            setData('email', editing.email ?? '');
        }
        // Hanya saat dialog dibuka.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [state]);

    const submit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(editing ? 'Akun diperbarui' : 'Akun dibuat');
                reset();
                onClose();
            },
            onError: () => toast.error('Periksa kembali isian akun'),
        };

        if (editing) {
            put(userAccounts.update(editing.id).url, options);
        } else {
            post(userAccounts.store().url, options);
        }
    };

    const selectedEmployee =
        employeeOptions.find(
            (option) => String(option.value) === data.employee_id,
        ) ?? null;

    const passwordInput = (
        key: 'password' | 'password_confirmation',
        label: string,
    ) => (
        <Field>
            <FieldLabel htmlFor={key}>{label}</FieldLabel>
            <div className="relative">
                <Input
                    id={key}
                    type={showPassword ? 'text' : 'password'}
                    autoComplete="new-password"
                    value={data[key]}
                    readOnly={processing}
                    placeholder={
                        editing ? 'Kosongkan bila tidak diganti' : undefined
                    }
                    onChange={(event) => setData(key, event.target.value)}
                    className="h-11 pr-11 text-base md:h-9 md:text-sm"
                />
                <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    aria-label={
                        showPassword ? 'Sembunyikan password' : 'Lihat password'
                    }
                    className="absolute top-1/2 right-1 flex size-9 -translate-y-1/2 items-center justify-center text-muted-foreground"
                >
                    {showPassword ? (
                        <EyeOff className="size-4" />
                    ) : (
                        <Eye className="size-4" />
                    )}
                </button>
            </div>
            <FieldError>{errors[key]}</FieldError>
        </Field>
    );

    return (
        <Dialog
            open={state.mode !== 'closed'}
            onOpenChange={(open) => !open && !processing && onClose()}
        >
            <DialogContent asChild className="max-h-[90dvh] overflow-y-auto">
                <form onSubmit={submit}>
                    <DialogCancel />
                    <DialogHeader>
                        <DialogTitle>
                            {editing ? 'Edit Akun Login' : 'Akun Login Baru'}
                        </DialogTitle>
                        <DialogDescription className="break-words">
                            {editing
                                ? `Dipakai oleh ${editing.employees.map((employee) => employee.name).join(', ')}`
                                : 'Untuk masuk ke kasir (NewZakicaPOS) dan backoffice.'}
                        </DialogDescription>
                    </DialogHeader>

                    <FieldSet>
                        {!editing && (
                            <Field>
                                <FieldLabel>Karyawan</FieldLabel>
                                <Combobox
                                    items={employeeOptions}
                                    value={selectedEmployee}
                                    onValueChange={(
                                        option: EmployeeOption | null,
                                    ) =>
                                        setData(
                                            'employee_id',
                                            option ? String(option.value) : '',
                                        )
                                    }
                                >
                                    <ComboboxInput
                                        placeholder="Cari nama karyawan"
                                        className="h-11 w-full md:h-9"
                                    />
                                    <ComboboxContent>
                                        <ComboboxEmpty>
                                            Karyawan tidak ditemukan
                                        </ComboboxEmpty>
                                        <ComboboxList>
                                            {(option: EmployeeOption) => (
                                                <ComboboxItem
                                                    key={option.value}
                                                    value={option}
                                                >
                                                    {option.label}
                                                </ComboboxItem>
                                            )}
                                        </ComboboxList>
                                    </ComboboxContent>
                                </Combobox>
                                <FieldDescription>
                                    Karyawan tidak ditemukan?{' '}
                                    <Link
                                        href={employees.index().url}
                                        className="font-medium text-primary underline-offset-4 hover:underline"
                                    >
                                        Tambah Karyawan
                                    </Link>
                                </FieldDescription>
                                <FieldError>{errors.employee_id}</FieldError>
                            </Field>
                        )}

                        <Field>
                            <FieldLabel htmlFor="email">Email</FieldLabel>
                            <Input
                                id="email"
                                type="email"
                                autoComplete="off"
                                value={data.email}
                                readOnly={processing}
                                onChange={(event) =>
                                    setData('email', event.target.value)
                                }
                                className="h-11 text-base md:h-9 md:text-sm"
                            />
                            <FieldError>{errors.email}</FieldError>
                        </Field>

                        {passwordInput('password', 'Password')}
                        {passwordInput(
                            'password_confirmation',
                            'Konfirmasi Password',
                        )}
                    </FieldSet>

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
                            {editing ? 'Simpan' : 'Buat Akun'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
