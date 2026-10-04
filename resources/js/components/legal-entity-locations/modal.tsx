import { useForm } from '@inertiajs/react';
import { Check, ChevronsUpDown, X } from 'lucide-react';
import type { SubmitEventHandler } from 'react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
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
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Spinner } from '@/components/ui/spinner';
import type { LocationWithLegalEntities } from '@/lib/model';
import { cn } from '@/lib/utils';
import legalEntityLocations from '@/routes/legal-entity-locations';

export type LegalEntityOption = {
    value: number;
    label: string;
};

type Props = {
    location?: LocationWithLegalEntities;
    legalEntityOptions: LegalEntityOption[];
    onClose: () => void;
};

export default ({ location, legalEntityOptions, onClose }: Props) => {
    const [open, setOpen] = useState(false);
    const { processing, post, reset, errors, data, setData, clearErrors } =
        useForm<{ location_id: number; legal_entity_ids: number[] }>({
            location_id: 0,
            legal_entity_ids: [],
        });

    useEffect(() => {
        reset();
        clearErrors();
        setOpen(false);

        if (location) {
            setData((prev) => ({ ...prev, location_id: location.id }));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [location?.id]);

    const assignedIds =
        location?.legal_entity_locations.map((item) => item.legal_entity_id) ??
        [];
    const availableOptions = legalEntityOptions.filter(
        (option) => !assignedIds.includes(option.value),
    );
    const selectedOptions = availableOptions.filter((option) =>
        data.legal_entity_ids.includes(option.value),
    );
    const allSelected =
        !!availableOptions.length &&
        selectedOptions.length === availableOptions.length;

    const setSelected = (ids: number[]) => {
        setData('legal_entity_ids', ids);
        clearErrors();
    };

    const toggle = (id: number) =>
        setSelected(
            data.legal_entity_ids.includes(id)
                ? data.legal_entity_ids.filter((x) => x !== id)
                : [...data.legal_entity_ids, id],
        );

    const error =
        errors.location_id ??
        Object.entries(errors).find(([key]) =>
            key.startsWith('legal_entity_ids'),
        )?.[1];

    const submit: SubmitEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        post(legalEntityLocations.store().url, {
            only: ['pagination'],
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    `${data.legal_entity_ids.length} CV berhasil ditambahkan ke lokasi`,
                );
                onClose();
            },
            onError: () => toast.error('Gagal menambahkan CV'),
        });
    };

    return (
        <Dialog
            open={!!location}
            onOpenChange={() => {
                if (!processing) onClose();
            }}
        >
            <DialogContent asChild className="sm:max-w-lg">
                <form onSubmit={submit}>
                    <DialogCancel />

                    <DialogHeader>
                        <DialogTitle>Tambah CV</DialogTitle>
                        <DialogDescription>{location?.name}</DialogDescription>
                    </DialogHeader>

                    <FieldSet className="grid gap-4">
                        <Field>
                            <FieldLabel>
                                CV<span className="text-destructive">*</span>
                            </FieldLabel>
                            <Popover open={open} onOpenChange={setOpen} modal>
                                <PopoverTrigger asChild>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        role="combobox"
                                        aria-expanded={open}
                                        aria-invalid={!!error}
                                        disabled={!availableOptions.length}
                                        className="h-auto min-h-9 w-full justify-between px-3 py-1.5 font-normal"
                                    >
                                        {selectedOptions.length ? (
                                            <div className="flex flex-wrap gap-1">
                                                {selectedOptions.map(
                                                    (option) => (
                                                        <Badge
                                                            key={option.value}
                                                            variant="secondary"
                                                            className="gap-1 pr-0.5"
                                                        >
                                                            {option.label}
                                                            <span
                                                                role="button"
                                                                tabIndex={-1}
                                                                className="rounded-sm p-0.5 hover:bg-black/10"
                                                                onPointerDown={(
                                                                    e,
                                                                ) => {
                                                                    e.preventDefault();
                                                                    e.stopPropagation();
                                                                    toggle(
                                                                        option.value,
                                                                    );
                                                                }}
                                                            >
                                                                <X />
                                                            </span>
                                                        </Badge>
                                                    ),
                                                )}
                                            </div>
                                        ) : (
                                            <span className="text-muted-foreground">
                                                {availableOptions.length
                                                    ? 'Pilih CV'
                                                    : 'Semua CV aktif sudah terdaftar'}
                                            </span>
                                        )}
                                        <ChevronsUpDown className="shrink-0 opacity-50" />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent
                                    align="start"
                                    className="w-(--radix-popover-trigger-width) p-0"
                                >
                                    <Command>
                                        <CommandInput placeholder="Cari CV..." />
                                        <CommandList>
                                            <CommandEmpty>
                                                CV tidak ditemukan
                                            </CommandEmpty>
                                            <CommandGroup>
                                                <CommandItem
                                                    className="cursor-pointer"
                                                    onSelect={() =>
                                                        setSelected(
                                                            allSelected
                                                                ? []
                                                                : availableOptions.map(
                                                                      (o) =>
                                                                          o.value,
                                                                  ),
                                                        )
                                                    }
                                                >
                                                    <Check
                                                        className={cn(
                                                            allSelected
                                                                ? 'opacity-100'
                                                                : 'opacity-0',
                                                        )}
                                                    />
                                                    Pilih semua
                                                </CommandItem>
                                                {availableOptions.map(
                                                    (option) => (
                                                        <CommandItem
                                                            key={option.value}
                                                            value={option.label}
                                                            className="cursor-pointer"
                                                            onSelect={() =>
                                                                toggle(
                                                                    option.value,
                                                                )
                                                            }
                                                        >
                                                            <Check
                                                                className={cn(
                                                                    data.legal_entity_ids.includes(
                                                                        option.value,
                                                                    )
                                                                        ? 'opacity-100'
                                                                        : 'opacity-0',
                                                                )}
                                                            />
                                                            {option.label}
                                                        </CommandItem>
                                                    ),
                                                )}
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                            <FieldError>{error}</FieldError>
                        </Field>
                    </FieldSet>

                    <DialogFooter>
                        <DialogClose asChild disabled={processing}>
                            <Button variant="outline">Batal</Button>
                        </DialogClose>
                        <Button
                            disabled={
                                processing || !data.legal_entity_ids.length
                            }
                            type="submit"
                        >
                            <Spinner className={processing ? '' : 'hidden'} />
                            Simpan
                            {!!data.legal_entity_ids.length &&
                                ` (${data.legal_entity_ids.length})`}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
};
