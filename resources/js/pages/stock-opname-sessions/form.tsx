import { Head, Link, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { ArrowLeft, Save, Search } from 'lucide-react';
import type { FormEvent } from 'react';
import { useMemo, useState } from 'react';
import { toast } from 'sonner';
import DatePicker from '@/components/date-picker';
import LocationDropdown from '@/components/location-dropdown';
import SearchableSelect from '@/components/searchable-select';
import type {
    EmployeeOption,
    OpnameSession,
} from '@/components/stock-opname-session/types';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Field,
    FieldDescription,
    FieldError,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import productOpnameSessions from '@/routes/product-opname-sessions';
import type { Option } from '@/types';

type Props = {
    session: OpnameSession | null;
    locationOptions: Option[];
    employeeOptions: EmployeeOption[];
    currentEmployeeId: number | null;
};

export default function SessionForm({
    session,
    locationOptions,
    employeeOptions,
    currentEmployeeId,
}: Props) {
    const editing = session !== null;
    const title = editing ? `Ubah Sesi ${session.code}` : 'Sesi SO Baru';

    const { data, setData, post, put, processing, errors } = useForm({
        location_id: (session?.location_id ?? '') as number | '',
        opname_date: session?.opname_date ?? format(new Date(), 'yyyy-MM-dd'),
        pic_employee_id: (session?.pic_employee_id ?? '') as number | '',
        employee_ids: session?.employees?.map((item) => item.id) ?? [],
        start_now: false,
        note: session?.note ?? '',
    });

    const [keyword, setKeyword] = useState('');

    const shownEmployees = useMemo(() => {
        const search = keyword.trim().toLowerCase();

        return employeeOptions.filter((employee) =>
            employee.label.toLowerCase().includes(search),
        );
    }, [employeeOptions, keyword]);

    const changeLocation = (id: number) => {
        if (!id || id === data.location_id) {
            return;
        }

        setData((form) => {
            // PJ awal adalah akun yang sedang login, supaya tidak salah pilih orang lain.
            const pic =
                form.pic_employee_id === '' && currentEmployeeId !== null
                    ? currentEmployeeId
                    : form.pic_employee_id;

            return {
                ...form,
                location_id: id,
                pic_employee_id: pic,
                employee_ids:
                    pic !== '' && !form.employee_ids.includes(pic)
                        ? [...form.employee_ids, pic]
                        : form.employee_ids,
            };
        });
    };

    const toggleEmployee = (id: number, checked: boolean) =>
        setData(
            'employee_ids',
            checked
                ? [...data.employee_ids, id]
                : data.employee_ids.filter((item) => item !== id),
        );

    const changePic = (value: string) => {
        const id = Number(value);

        // PJ otomatis ikut dalam daftar karyawan sesi.
        setData((form) => ({
            ...form,
            pic_employee_id: id,
            employee_ids: form.employee_ids.includes(id)
                ? form.employee_ids
                : [...form.employee_ids, id],
        }));
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        const options = {
            onSuccess: () =>
                toast.success(
                    data.start_now ? 'Sesi SO dimulai' : 'Sesi SO disimpan',
                ),
            onError: (formErrors: Record<string, string>) =>
                toast.error(
                    formErrors.session ??
                        formErrors.employee ??
                        'Periksa kembali data sesi',
                ),
        };

        if (editing) {
            put(productOpnameSessions.update(session.id).url, options);
        } else {
            post(productOpnameSessions.store().url, options);
        }
    };

    return (
        <AppLayout
            breadcrumbs={[
                {
                    title: 'Stok Opname',
                    href: productOpnameSessions.index().url,
                },
                { title, href: '#' },
            ]}
        >
            <Head title={title} />

            <form onSubmit={submit} className="space-y-4">
                <Card>
                    <CardHeader>
                        <CardTitle className="break-all">{title}</CardTitle>
                        <CardDescription>
                            Tentukan toko, tanggal, PJ, dan karyawan yang boleh
                            masuk ke sesi ini.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <Field>
                            <FieldLabel>Toko</FieldLabel>
                            <div className="[&_button]:h-11 md:[&_button]:h-9 lg:[&_button]:w-full">
                                <LocationDropdown
                                    full
                                    options={locationOptions.map((option) => ({
                                        id: Number(option.value),
                                        name: option.label,
                                    }))}
                                    disabled={processing}
                                    defaultId={data.location_id || undefined}
                                    handleIdChange={changeLocation}
                                />
                            </div>
                            <FieldError>{errors.location_id}</FieldError>
                        </Field>

                        <Field>
                            <FieldLabel>Tanggal SO</FieldLabel>
                            <div className="[&_button]:h-11 md:[&_button]:h-9">
                                <DatePicker
                                    value={data.opname_date}
                                    disabled={processing}
                                    onChange={(value) =>
                                        setData('opname_date', value)
                                    }
                                />
                            </div>
                            <FieldError>{errors.opname_date}</FieldError>
                        </Field>

                        <Field>
                            <FieldLabel>PJ SO</FieldLabel>
                            <SearchableSelect
                                className="h-11 md:h-9"
                                value={
                                    data.pic_employee_id === ''
                                        ? ''
                                        : String(data.pic_employee_id)
                                }
                                disabled={processing || !data.location_id}
                                placeholder={
                                    data.location_id
                                        ? 'Pilih PJ SO'
                                        : 'Pilih toko terlebih dahulu'
                                }
                                searchPlaceholder="Cari karyawan..."
                                emptyText="Karyawan tidak ditemukan"
                                options={employeeOptions.map((employee) => ({
                                    value: String(employee.value),
                                    label: employee.label,
                                }))}
                                onChange={changePic}
                            />
                            <FieldError>{errors.pic_employee_id}</FieldError>
                        </Field>

                        <Field className="lg:col-span-2">
                            <FieldLabel htmlFor="note">Catatan</FieldLabel>
                            <Textarea
                                id="note"
                                placeholder="Catatan (opsional)"
                                disabled={processing}
                                value={data.note}
                                onChange={(event) =>
                                    setData('note', event.target.value)
                                }
                            />
                            <FieldError>{errors.note}</FieldError>
                        </Field>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Karyawan</CardTitle>
                        <CardDescription>
                            {data.employee_ids.length} dipilih dari role Office
                            dan CEO. Hanya karyawan ini dan PJ yang bisa masuk
                            ke sesi.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-3">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={keyword}
                                onChange={(event) =>
                                    setKeyword(event.target.value)
                                }
                                placeholder="Cari karyawan"
                                aria-label="Cari karyawan"
                                className="h-11 pl-9 text-base md:h-9 md:text-sm"
                            />
                        </div>

                        <ul className="max-h-96 divide-y overflow-y-auto rounded-lg border">
                            {shownEmployees.map((employee) => {
                                const inputId = `employee-${employee.value}`;

                                return (
                                    <li key={employee.value}>
                                        <label
                                            htmlFor={inputId}
                                            className="flex min-h-11 cursor-pointer items-center gap-3 px-3 py-2"
                                        >
                                            <Checkbox
                                                id={inputId}
                                                disabled={
                                                    processing ||
                                                    employee.value ===
                                                        data.pic_employee_id
                                                }
                                                checked={data.employee_ids.includes(
                                                    employee.value,
                                                )}
                                                onCheckedChange={(checked) =>
                                                    toggleEmployee(
                                                        employee.value,
                                                        checked === true,
                                                    )
                                                }
                                            />
                                            <span className="min-w-0 text-sm break-words">
                                                {employee.label}
                                                {employee.value ===
                                                    data.pic_employee_id &&
                                                    ' (PJ)'}
                                            </span>
                                        </label>
                                    </li>
                                );
                            })}
                            {!shownEmployees.length && (
                                <li className="p-4 text-center text-sm text-muted-foreground">
                                    {employeeOptions.length
                                        ? 'Karyawan tidak ditemukan'
                                        : 'Belum ada karyawan ber-role Office atau CEO'}
                                </li>
                            )}
                        </ul>
                        <FieldError>
                            {errors.employee_ids ??
                                (errors as Record<string, string>)[
                                    'employee_ids.0'
                                ]}
                        </FieldError>

                        <Field
                            orientation="horizontal"
                            className="rounded-lg border p-3"
                        >
                            <Checkbox
                                id="start_now"
                                checked={data.start_now}
                                disabled={processing}
                                onCheckedChange={(checked) =>
                                    setData('start_now', checked === true)
                                }
                            />
                            <div>
                                <FieldLabel htmlFor="start_now">
                                    Langsung mulai sesi
                                </FieldLabel>
                                <FieldDescription>
                                    Bila tidak dicentang, sesi berstatus
                                    Dijadwalkan sampai PJ memulainya.
                                </FieldDescription>
                            </div>
                        </Field>
                    </CardContent>
                </Card>

                <div className="flex flex-col-reverse gap-2 md:flex-row md:justify-end">
                    <Button
                        type="button"
                        variant="outline"
                        className="h-11 md:h-9"
                        asChild
                    >
                        <Link
                            href={
                                editing
                                    ? productOpnameSessions.show(session.id).url
                                    : productOpnameSessions.index().url
                            }
                        >
                            <ArrowLeft /> Kembali
                        </Link>
                    </Button>
                    <Button
                        type="submit"
                        className="h-11 md:h-9"
                        disabled={processing}
                    >
                        {processing ? <Spinner /> : <Save />}
                        Simpan
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
}
